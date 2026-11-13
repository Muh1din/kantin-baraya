<?php

namespace App\Services;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointTransaction;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Reward;
use App\Models\User;
use App\Notifications\CustomerActivityNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RewardRedemptionService
{
    public function redeem(User $user, int $rewardId): Order
    {
        return DB::transaction(function () use ($user, $rewardId): Order {
            $customer = User::query()->lockForUpdate()->findOrFail($user->id);
            $reward = Reward::query()
                ->where('is_active', true)
                ->lockForUpdate()
                ->findOrFail($rewardId);
            $menu = Menu::query()
                ->whereKey($reward->menu_id)
                ->where('is_available', true)
                ->lockForUpdate()
                ->first();

            if (! $menu || $menu->stock < 1) {
                throw ValidationException::withMessages([
                    'reward' => 'Menu reward sedang tidak tersedia atau stoknya habis.',
                ]);
            }

            $account = LoyaltyAccount::query()
                ->where('user_id', $customer->id)
                ->lockForUpdate()
                ->first();

            if (! $account || $account->saldo_poin < $reward->points_required) {
                throw ValidationException::withMessages([
                    'reward' => 'Poin Anda belum cukup untuk menukar reward ini.',
                ]);
            }

            $account->decrement('saldo_poin', $reward->points_required);
            $menu->decrement('stock', 1);

            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'order_code' => 'RWD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'tracking_token' => Str::random(48),
                'customer_name' => $customer->name,
                'order_channel' => 'loyalty',
                'order_type' => 'bungkus',
                'order_status' => 'confirmed',
                'subtotal' => $menu->price,
                'discount_amount' => $menu->price,
                'total_amount' => 0,
                'notes' => 'Reward ditukar: ' . $menu->name,
            ]);

            $order->items()->create([
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'quantity' => 1,
                'unit_price' => $menu->price,
                'subtotal' => $menu->price,
                'notes' => 'Menu reward gratis.',
            ]);

            Payment::query()->create([
                'order_id' => $order->id,
                'payment_method' => 'loyalty',
                'payment_status' => 'paid',
                'amount' => 0,
                'paid_at' => now(),
            ]);

            $order->statusHistories()->create([
                'old_status' => null,
                'new_status' => 'confirmed',
                'changed_by' => $customer->id,
                'notes' => 'Pesanan reward dikonfirmasi saat penukaran poin.',
            ]);

            LoyaltyPointTransaction::query()->create([
                'loyalty_account_id' => $account->id,
                'order_id' => $order->id,
                'reward_id' => $reward->id,
                'type' => 'redeemed',
                'points' => $reward->points_required,
                'description' => 'Penukaran ' . $menu->name . ' dengan kode pesanan ' . $order->order_code,
            ]);

            $customer->notify(new CustomerActivityNotification(
                'Poin digunakan',
                "{$reward->points_required} poin ditukar dengan menu {$menu->name}.",
                ['kind' => 'loyalty_redeemed', 'order_id' => $order->id, 'order_code' => $order->order_code, 'points' => $reward->points_required],
            ));

            return $order;
        });
    }
}
