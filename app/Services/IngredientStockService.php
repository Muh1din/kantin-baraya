<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class IngredientStockService
{
    public function recordOpeningBalance(
        int $ingredientId,
        string $quantity,
        string $transactionKey,
        ?int $userId = null,
        ?string $notes = null,
        ?CarbonInterface $occurredAt = null,
    ): IngredientStockMovement {
        return $this->recordMovement(
            ingredientId: $ingredientId,
            movementType: IngredientStockMovement::TYPE_OPENING_BALANCE,
            quantityChange: $quantity,
            transactionKey: $transactionKey,
            userId: $userId,
            notes: $notes,
            occurredAt: $occurredAt,
        );
    }

    /**
     * Create an ingredient with a zero balance, then record its opening balance
     * and movement atomically. The ingredient attributes must not set stock.
     */
    public function createWithOpeningBalance(
        array $attributes,
        string $openingQuantity,
        string $transactionKey,
        ?int $userId = null,
        ?string $notes = null,
        ?CarbonInterface $occurredAt = null,
    ): Ingredient {
        return DB::transaction(function () use ($attributes, $openingQuantity, $transactionKey, $userId, $notes, $occurredAt): Ingredient {
            unset($attributes['current_stock']);
            $ingredient = Ingredient::create($attributes);

            $this->recordMovement(
                ingredientId: $ingredient->id,
                movementType: IngredientStockMovement::TYPE_OPENING_BALANCE,
                quantityChange: $openingQuantity,
                transactionKey: $transactionKey,
                userId: $userId,
                notes: $notes,
                occurredAt: $occurredAt,
            );

            return $ingredient->refresh();
        });
    }

    public function recordMovement(
        int $ingredientId,
        string $movementType,
        string $quantityChange,
        string $transactionKey,
        ?int $userId = null,
        ?string $notes = null,
        ?CarbonInterface $occurredAt = null,
    ): IngredientStockMovement {
        $change = $this->toMinorUnits($quantityChange);
        $this->validateMovement($movementType, $change, $transactionKey);

        return DB::transaction(function () use ($ingredientId, $movementType, $quantityChange, $change, $transactionKey, $userId, $notes, $occurredAt): IngredientStockMovement {
            $ingredient = Ingredient::query()->lockForUpdate()->findOrFail($ingredientId);

            $existing = IngredientStockMovement::query()
                ->where('transaction_key', $transactionKey)
                ->first();

            if ($existing) {
                $sameMovement = $existing->ingredient_id === $ingredient->id
                    && $existing->movement_type === $movementType
                    && $this->toMinorUnits($existing->quantity_change) === $change;

                if (! $sameMovement) {
                    throw new LogicException('Transaction key sudah digunakan untuk pergerakan stok lain.');
                }

                return $existing;
            }

            if ($movementType === IngredientStockMovement::TYPE_OPENING_BALANCE) {
                $hasOpeningBalance = $ingredient->stockMovements()
                    ->where('movement_type', IngredientStockMovement::TYPE_OPENING_BALANCE)
                    ->exists();

                if ($hasOpeningBalance || $this->toMinorUnits($ingredient->current_stock) !== 0) {
                    throw new LogicException('Saldo awal bahan baku hanya dapat dicatat satu kali.');
                }
            }

            $stockBefore = $this->toMinorUnits($ingredient->current_stock);
            $stockAfter = $stockBefore + $change;

            if ($stockAfter < 0) {
                throw new DomainException('Pergerakan stok akan membuat saldo bahan baku negatif.');
            }

            $ingredient->current_stock = $this->fromMinorUnits($stockAfter);
            $ingredient->save();

            return $ingredient->stockMovements()->create([
                'user_id' => $userId,
                'movement_type' => $movementType,
                'quantity_change' => $quantityChange,
                'stock_after' => $this->fromMinorUnits($stockAfter),
                'transaction_key' => $transactionKey,
                'notes' => $notes,
                'occurred_at' => $occurredAt ?? now(),
            ]);
        });
    }

    private function validateMovement(string $movementType, int $change, string $transactionKey): void
    {
        $allowedTypes = [
            IngredientStockMovement::TYPE_OPENING_BALANCE,
            IngredientStockMovement::TYPE_STOCK_IN,
            IngredientStockMovement::TYPE_STOCK_OUT,
            IngredientStockMovement::TYPE_ADJUSTMENT,
        ];

        if (! in_array($movementType, $allowedTypes, true)) {
            throw new InvalidArgumentException('Jenis pergerakan stok tidak dikenal.');
        }

        if (strlen($transactionKey) === 0 || strlen($transactionKey) > 120) {
            throw new InvalidArgumentException('Transaction key wajib diisi dan maksimal 120 karakter.');
        }

        if ($movementType === IngredientStockMovement::TYPE_OPENING_BALANCE && $change < 0) {
            throw new InvalidArgumentException('Saldo awal tidak boleh negatif.');
        }

        if ($movementType === IngredientStockMovement::TYPE_STOCK_IN && $change <= 0) {
            throw new InvalidArgumentException('Stok masuk harus bernilai positif.');
        }

        if ($movementType === IngredientStockMovement::TYPE_STOCK_OUT && $change >= 0) {
            throw new InvalidArgumentException('Stok keluar harus bernilai negatif.');
        }

        if ($movementType !== IngredientStockMovement::TYPE_OPENING_BALANCE && $change === 0) {
            throw new InvalidArgumentException('Perubahan stok harus lebih besar dari nol atau kurang dari nol.');
        }
    }

    /** Convert a DECIMAL(18, 3) string to an exact integer representation. */
    private function toMinorUnits(string|int $quantity): int
    {
        $quantity = (string) $quantity;

        if (! preg_match('/^-?\d{1,15}(?:\.\d{1,3})?$/D', $quantity)) {
            throw new InvalidArgumentException('Kuantitas harus berupa angka desimal dengan maksimal 15 digit dan 3 angka pecahan.');
        }

        $negative = str_starts_with($quantity, '-');
        $unsigned = $negative ? substr($quantity, 1) : $quantity;
        [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $minorUnits = ((int) $whole * 1000) + (int) str_pad($fraction, 3, '0');

        return $negative ? -$minorUnits : $minorUnits;
    }

    private function fromMinorUnits(int $quantity): string
    {
        $negative = $quantity < 0;
        $absolute = abs($quantity);
        $whole = intdiv($absolute, 1000);
        $fraction = $absolute % 1000;

        return ($negative ? '-' : '') . $whole . ($fraction === 0 ? '' : '.' . rtrim(str_pad((string) $fraction, 3, '0', STR_PAD_LEFT), '0'));
    }
}
