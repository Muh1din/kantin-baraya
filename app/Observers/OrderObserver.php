<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\CustomerActivityNotification;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status') || ! $order->customer) {
            return;
        }

        $labels = [
            'pending' => 'Menunggu konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'in_progress' => 'Sedang disiapkan',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
        $status = $labels[$order->order_status] ?? ucfirst(str_replace('_', ' ', $order->order_status));

        $order->customer->notify(new CustomerActivityNotification(
            'Status pesanan berubah',
            "Pesanan {$order->order_code} kini berstatus: {$status}.",
            ['kind' => 'order_status', 'order_id' => $order->id, 'order_code' => $order->order_code],
        ));
    }
}
