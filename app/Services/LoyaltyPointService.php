<?php

namespace App\Services;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointTransaction;
use App\Models\Order;
use App\Models\User;
use App\Notifications\CustomerActivityNotification;

class LoyaltyPointService
{
    public const RUPIAH_PER_POINT = 1000;

    /** Award points for a paid, registered-customer order. Call inside its payment transaction. */
    public function awardForPaidOrder(Order $order): int
    {
        if (! $order->customer_id || (float) $order->total_amount <= 0) {
            return 0;
        }

        $customer = User::query()
            ->with('role')
            ->lockForUpdate()
            ->find($order->customer_id);

        if (! $customer || ! in_array($customer->role?->name, ['customer', 'pelanggan'], true)) {
            return 0;
        }

        $points = intdiv((int) round((float) $order->total_amount), self::RUPIAH_PER_POINT);
        if ($points < 1) {
            return 0;
        }

        if (LoyaltyPointTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', 'earned')
            ->exists()) {
            return 0;
        }

        $account = LoyaltyAccount::query()
            ->where('user_id', $customer->id)
            ->lockForUpdate()
            ->first();

        if (! $account) {
            $account = LoyaltyAccount::query()->create([
                'user_id' => $customer->id,
                'saldo_poin' => 0,
            ]);
        }

        $account->increment('saldo_poin', $points);
        LoyaltyPointTransaction::query()->create([
            'loyalty_account_id' => $account->id,
            'order_id' => $order->id,
            'type' => 'earned',
            'points' => $points,
            'description' => 'Poin dari pembayaran pesanan ' . $order->order_code,
        ]);

        $customer->notify(new CustomerActivityNotification(
            'Poin bertambah',
            "Anda mendapatkan {$points} poin dari pembayaran pesanan {$order->order_code}.",
            ['kind' => 'loyalty_earned', 'order_id' => $order->id, 'points' => $points],
        ));

        return $points;
    }
}
