<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

class SaleService
{
    public function create(array $items, int $cashierId, ?string $idempotencyKey = null, ?int $cashReceived = null): Sale
    {
        if ($idempotencyKey !== null && ($idempotencyKey === '' || strlen($idempotencyKey) > 100)) {
            throw new InvalidArgumentException('Kunci transaksi tidak valid.');
        }

        if ($items === []) {
            throw new InvalidArgumentException('Transaksi harus memiliki minimal satu item.');
        }

        $normalizedInput = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException('Format item transaksi tidak valid.');
            }
            $menuId = filter_var($item['menu_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
            if ($menuId === false || $menuId < 1 || $quantity === false || $quantity < 1 || isset($normalizedInput[$menuId])) {
                throw new InvalidArgumentException('Menu dan jumlah item harus valid dan tidak boleh duplikat.');
            }
            $normalizedInput[$menuId] = $quantity;
        }
        ksort($normalizedInput);
        $requestHash = hash('sha256', json_encode([$cashierId, $normalizedInput, $cashReceived], JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($items, $cashierId, $idempotencyKey, $cashReceived, $normalizedInput, $requestHash): Sale {
            $cashier = User::query()->with('role')->lockForUpdate()->findOrFail($cashierId);
            if ($cashier->role?->name !== 'kasir') {
                throw new InvalidArgumentException('Transaksi hanya dapat dibuat oleh pengguna dengan role kasir.');
            }

            if ($idempotencyKey !== null) {
                $existingSale = Sale::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existingSale) {
                    if (! hash_equals((string) $existingSale->idempotency_hash, $requestHash)) {
                        throw new LogicException('Kunci checkout sudah digunakan untuk transaksi yang berbeda.');
                    }
                    return $existingSale->load(['cashier', 'items.menu']);
                }
            }

            $normalizedItems = [];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    throw new InvalidArgumentException('Format item transaksi tidak valid.');
                }

                $menuId = filter_var($item['menu_id'] ?? null, FILTER_VALIDATE_INT);
                $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                if ($menuId === false || $menuId < 1 || $quantity === false || $quantity < 1) {
                    throw new InvalidArgumentException('Menu dan jumlah item harus berupa bilangan bulat positif.');
                }

                if (isset($normalizedItems[$menuId])) {
                    throw new InvalidArgumentException('Menu yang sama hanya boleh muncul satu kali dalam detail transaksi.');
                }

                $normalizedItems[$menuId] = $quantity;
            }

            $menus = Menu::query()
                ->whereIn('id', array_keys($normalizedItems))
                ->where('is_available', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($menus->count() !== count($normalizedItems)) {
                throw new InvalidArgumentException('Satu atau lebih menu tidak ditemukan atau sedang tidak tersedia.');
            }

            $lines = [];
            $subtotal = 0;
            foreach ($normalizedItems as $menuId => $quantity) {
                /** @var Menu $menu */
                $menu = $menus->get($menuId);
                $unitPrice = (int) $menu->price;

                if ($unitPrice < 1) {
                    throw new InvalidArgumentException('Harga menu harus lebih besar dari nol.');
                }

                if ($unitPrice > intdiv(PHP_INT_MAX, $quantity)) {
                    throw new InvalidArgumentException('Nilai subtotal item melebihi batas angka yang didukung.');
                }

                $lineSubtotal = $unitPrice * $quantity;
                if ($subtotal > PHP_INT_MAX - $lineSubtotal) {
                    throw new InvalidArgumentException('Total transaksi melebihi batas angka yang didukung.');
                }

                $subtotal += $lineSubtotal;
                $lines[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_subtotal' => $lineSubtotal,
                ];
            }

            if ($cashReceived !== null && ($cashReceived < $subtotal || $cashReceived < 0)) {
                throw new InvalidArgumentException('Uang yang diterima harus sama dengan atau lebih besar dari total.');
            }

            $sale = new Sale();
            $paidInCash = $cashReceived !== null;
            $sale->forceFill([
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(12)),
                'cashier_id' => $cashier->id,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => $paidInCash ? Sale::STATUS_COMPLETED : Sale::STATUS_PENDING,
                'payment_status' => $paidInCash ? Sale::PAYMENT_PAID : Sale::PAYMENT_UNPAID,
                'payment_method' => $paidInCash ? Sale::METHOD_CASH : null,
                'cash_received' => $cashReceived,
                'change_due' => $cashReceived === null ? null : $cashReceived - $subtotal,
                'idempotency_key' => $idempotencyKey,
                'idempotency_hash' => $idempotencyKey === null ? null : $requestHash,
            ])->save();

            foreach ($lines as $line) {
                $saleItem = new SaleItem();
                $saleItem->forceFill($line);
                $sale->items()->save($saleItem);
            }

            return $sale->load(['cashier', 'items.menu']);
        });
    }
}
