<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Order;
use DomainException;
use Illuminate\Support\Collection;

class RecipeInventoryService
{
    public function __construct(private readonly IngredientStockService $stockService)
    {
    }

    /** Deplete an order's immutable recipe snapshot. Call inside the order transaction. */
    public function consumeForOrder(Order $order, int $userId): void
    {
        $requirements = $this->requirementsFor($order);
        if ($requirements->isEmpty()) {
            return;
        }

        $keyPrefix = "order:{$order->id}:ingredient:";
        $existing = IngredientStockMovement::query()
            ->where('order_id', $order->id)
            ->where('movement_type', IngredientStockMovement::TYPE_STOCK_OUT)
            ->where('transaction_key', 'like', $keyPrefix . '%:consume')
            ->get()
            ->keyBy('ingredient_id');

        if ($existing->isNotEmpty()) {
            $this->assertExistingMovementsMatch($existing, $requirements, -1);

            return;
        }

        $ingredients = Ingredient::query()
            ->whereIn('id', $requirements->keys())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($requirements as $ingredientId => $requirement) {
            $ingredient = $ingredients->get($ingredientId);
            if (! $ingredient) {
                throw new DomainException("Bahan {$requirement['name']} pada snapshot pesanan tidak ditemukan.");
            }

            if ($this->toMilli($ingredient->current_stock) < $requirement['quantity_milli']) {
                throw new DomainException(sprintf(
                    'Stok bahan %s tidak mencukupi. Dibutuhkan %s %s, tersedia %s %s.',
                    $requirement['name'],
                    $this->fromMilli($requirement['quantity_milli']),
                    $requirement['unit'],
                    $ingredient->current_stock,
                    $requirement['unit'],
                ));
            }
        }

        foreach ($requirements as $ingredientId => $requirement) {
            $this->stockService->recordMovement(
                ingredientId: (int) $ingredientId,
                movementType: IngredientStockMovement::TYPE_STOCK_OUT,
                quantityChange: '-' . $this->fromMilli($requirement['quantity_milli']),
                transactionKey: $keyPrefix . $ingredientId . ':consume',
                userId: $userId,
                notes: "Pemakaian resep pesanan {$order->order_code} saat mulai diproses.",
                orderId: $order->id,
            );
        }
    }

    /** Restore only stock movements previously consumed for this order. */
    public function restoreForCancelledOrder(Order $order, int $userId): void
    {
        $keyPrefix = "order:{$order->id}:ingredient:";
        $consumptions = IngredientStockMovement::query()
            ->where('order_id', $order->id)
            ->where('movement_type', IngredientStockMovement::TYPE_STOCK_OUT)
            ->where('transaction_key', 'like', $keyPrefix . '%:consume')
            ->orderBy('ingredient_id')
            ->get();

        if ($consumptions->isEmpty()) {
            return;
        }

        $restorations = IngredientStockMovement::query()
            ->where('order_id', $order->id)
            ->where('movement_type', IngredientStockMovement::TYPE_STOCK_IN)
            ->where('transaction_key', 'like', $keyPrefix . '%:restore')
            ->get()
            ->keyBy('ingredient_id');

        if ($restorations->isNotEmpty()) {
            if ($restorations->count() !== $consumptions->count()) {
                throw new DomainException('Riwayat pengembalian stok pesanan tidak lengkap. Periksa riwayat stok sebelum melanjutkan.');
            }

            foreach ($consumptions as $consumption) {
                $restoration = $restorations->get($consumption->ingredient_id);
                if (! $restoration || $this->toMilli($restoration->quantity_change) !== -$this->toMilli($consumption->quantity_change)) {
                    throw new DomainException('Jumlah pengembalian stok tidak sesuai pemakaian pesanan. Periksa riwayat stok sebelum melanjutkan.');
                }
            }

            return;
        }

        foreach ($consumptions as $movement) {
            $this->stockService->recordMovement(
                ingredientId: $movement->ingredient_id,
                movementType: IngredientStockMovement::TYPE_STOCK_IN,
                quantityChange: ltrim((string) $movement->quantity_change, '-'),
                transactionKey: $keyPrefix . $movement->ingredient_id . ':restore',
                userId: $userId,
                notes: "Pengembalian bahan karena pesanan {$order->order_code} dibatalkan.",
                orderId: $order->id,
            );
        }
    }

    /** @return Collection<int, array{name: string, unit: string, quantity_milli: int}> */
    private function requirementsFor(Order $order): Collection
    {
        $requirements = [];
        $items = $order->items()
            ->with('ingredientSnapshots')
            ->get();

        if ($items->contains(fn ($item) => $item->recipe_snapshotted_at === null)) {
            throw new DomainException("Pesanan {$order->order_code} belum memiliki snapshot resep. Periksa pesanan lama bersama Owner sebelum mulai diproses.");
        }

        $snapshots = $items->flatMap(fn ($item) => $item->ingredientSnapshots);

        foreach ($snapshots as $snapshot) {
            $ingredientId = (int) $snapshot->ingredient_id;
            $requirements[$ingredientId] ??= [
                'name' => $snapshot->ingredient_name,
                'unit' => $snapshot->unit_abbreviation,
                'quantity_milli' => 0,
            ];
            $requirements[$ingredientId]['quantity_milli'] += $this->toMilli($snapshot->required_quantity);
        }

        ksort($requirements);

        return collect($requirements);
    }

    private function assertExistingMovementsMatch(Collection $movements, Collection $requirements, int $direction): void
    {
        if ($movements->count() !== $requirements->count()) {
            throw new DomainException('Riwayat pemakaian stok pesanan tidak lengkap. Periksa riwayat stok sebelum melanjutkan.');
        }

        foreach ($requirements as $ingredientId => $requirement) {
            $movement = $movements->get($ingredientId);
            if (! $movement || $this->toMilli($movement->quantity_change) !== $direction * $requirement['quantity_milli']) {
                throw new DomainException('Riwayat pemakaian stok tidak sesuai snapshot resep pesanan. Periksa riwayat stok sebelum melanjutkan.');
            }
        }
    }

    private function toMilli(string|int|float $quantity): int
    {
        return (int) round((float) $quantity * 1000);
    }

    private function fromMilli(int $quantity): string
    {
        return intdiv($quantity, 1000) . '.' . str_pad((string) ($quantity % 1000), 3, '0', STR_PAD_LEFT);
    }
}
