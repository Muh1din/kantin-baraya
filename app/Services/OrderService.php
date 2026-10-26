<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function create(array $items, int $cashierId, ?string $idempotencyKey = null, ?int $cashReceived = null, string $customerName = '', string $paymentMethod = 'cash', bool $payAfterMeal = false, string $orderType = 'makan_di_tempat'): Order
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

        if (! in_array($paymentMethod, ['cash', 'qris'], true)) {
            throw new InvalidArgumentException('Metode pembayaran tidak didukung.');
        }
        if (! in_array($orderType, ['makan_di_tempat', 'bungkus'], true) || ($payAfterMeal && $orderType !== 'makan_di_tempat')) {
            throw new InvalidArgumentException('Pembayaran setelah makan hanya tersedia untuk pesanan makan di tempat.');
        }
        if (! $payAfterMeal && $paymentMethod === 'cash' && $cashReceived === null) {
            throw new InvalidArgumentException('Masukkan jumlah uang tunai yang diterima.');
        }
        if ($cashReceived !== null && $cashReceived < 0) {
            throw new InvalidArgumentException('Jumlah uang diterima tidak valid.');
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

        return DB::transaction(function () use ($cashierId, $idempotencyKey, $cashReceived, $customerName, $paymentMethod, $payAfterMeal, $orderType, $normalizedInput): Order {
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

            $menus = Menu::query()
                ->whereIn('id', array_keys($normalizedInput))
                ->where('is_available', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($menus->count() !== count($normalizedInput)) {
                throw new InvalidArgumentException('Satu atau lebih menu tidak ditemukan atau tidak tersedia.');
            }

            $subtotal = 0;
            $itemsToCreate = [];
            foreach ($normalizedInput as $menuId => $quantity) {
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

            if ($paymentMethod === 'cash' && ! $payAfterMeal && $cashReceived < $subtotal) {
                throw new InvalidArgumentException('Uang diterima harus minimal sebesar total transaksi.');
            }

            $order = Order::create([
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . Str::random(4),
                'tracking_token' => $idempotencyKey ?? Str::random(32),
                'customer_name' => $customerName,
                'order_channel' => 'pos',
                'order_type' => $orderType,
                'order_status' => ($payAfterMeal || $paymentMethod === 'cash') ? 'confirmed' : 'pending',
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'created_by' => $cashierId,
            ]);

            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $paymentMethod,
                    'payment_status' => ($paymentMethod === 'cash' && ! $payAfterMeal) ? 'paid' : 'pending',
                    'amount' => $subtotal,
                    'cash_received' => ($paymentMethod === 'cash' && ! $payAfterMeal) ? $cashReceived : null,
                    'change_due' => ($paymentMethod === 'cash' && ! $payAfterMeal) ? $cashReceived - $subtotal : null,
                    'paid_at' => ($paymentMethod === 'cash' && ! $payAfterMeal) ? now() : null,
                ]);

            if ($order->order_status === 'confirmed') {
                $order->statusHistories()->create([
                    'old_status' => null,
                    'new_status' => 'confirmed',
                    'changed_by' => $cashierId,
                    'notes' => $payAfterMeal ? 'POS: pesanan diteruskan ke dapur, pembayaran setelah makan.' : 'POS: pesanan diteruskan ke dapur.',
                ]);
            }

            return $order->load(['items', 'payment']);
        });
    }
}
