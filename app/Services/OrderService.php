<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function create(array $items, int $cashierId, ?string $idempotencyKey = null, ?int $cashReceived = null, string $customerName = ''): Order
    {
        if ($idempotencyKey !== null && ($idempotencyKey === '' || strlen($idempotencyKey) > 100)) {
            throw new InvalidArgumentException('Kunci transaksi tidak valid.');
        }

        if ($items === []) {
            throw new InvalidArgumentException('Transaksi harus memiliki minimal satu item.');
        }

        if (empty($customerName)) {
            throw new InvalidArgumentException('Nama pelanggan wajib diisi.');
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

        return DB::transaction(function () use ($items, $cashierId, $idempotencyKey, $cashReceived, $customerName, $normalizedInput): Order {
            $cashier = User::query()->with('role')->lockForUpdate()->findOrFail($cashierId);
            if (! in_array($cashier->role?->name, ['kasir', 'cashier'], true)) {
                throw new InvalidArgumentException('Transaksi hanya dapat dibuat oleh pengguna dengan role kasir.');
            }

            if ($idempotencyKey !== null) {
                $existingOrder = Order::query()->where('tracking_token', $idempotencyKey)->first();
                if ($existingOrder) {
                    return $existingOrder->load(['items', 'payment']);
                }
            }

            $normalizedItems = [];
            foreach ($items as $item) {
                $menuId = filter_var($item['menu_id'] ?? null, FILTER_VALIDATE_INT);
                $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                if ($menuId === false || $menuId < 1 || $quantity === false || $quantity < 1 || isset($normalizedItems[$menuId])) {
                    throw new InvalidArgumentException('Menu dan jumlah item harus valid.');
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
                throw new InvalidArgumentException('Satu atau lebih menu tidak ditemukan atau tidak tersedia.');
            }

            $subtotal = 0;
            $itemsToCreate = [];
            foreach ($normalizedItems as $menuId => $quantity) {
                $menu = $menus->get($menuId);
                $unitPrice = (int) $menu->price;
                $lineSubtotal = $unitPrice * $quantity;
                $subtotal += $lineSubtotal;
                $itemsToCreate[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $order = Order::create([
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . Str::random(4),
                'tracking_token' => $idempotencyKey ?? Str::random(32),
                'customer_name' => $customerName,
                'order_channel' => 'pos',
                'order_type' => 'dine_in',
                'order_status' => 'completed',
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'created_by' => $cashierId,
            ]);

            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            if ($cashReceived !== null) {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'amount' => $subtotal,
                    'paid_at' => now(),
                ]);
            }

            return $order->load(['items', 'payment']);
        });
    }
}
