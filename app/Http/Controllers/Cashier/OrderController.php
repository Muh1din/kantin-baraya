<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        $query = Order::query()
            ->with(['items', 'payment'])
            ->where('order_channel', 'online');

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($builder) use ($search) {
                $builder->where('order_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        $orders = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statuses = Order::query()
            ->where('order_channel', 'online')
            ->select('order_status')
            ->distinct()
            ->orderBy('order_status')
            ->pluck('order_status');

        return view('cashier.orders.index', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => $filters,
        ]);
    }

    public function show(int $order): View
    {
        $order = Order::query()
            ->where('order_channel', 'online')
            ->with([
                'items',
                'payment',
                'statusHistories.changedBy',
            ])
            ->findOrFail($order);

        return view('cashier.orders.show', [
            'order' => $order,
        ]);
    }

    public function confirm(int $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $order = Order::query()
                ->where('order_channel', 'online')
                ->lockForUpdate()
                ->findOrFail($order);

            if ($order->order_status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Hanya pesanan yang menunggu yang dapat dikonfirmasi.',
                ]);
            }

            if ($order->payment()->where('payment_method', 'qris')->where('payment_status', 'pending')->exists()) {
                throw ValidationException::withMessages([
                    'payment' => 'Verifikasi pembayaran QRIS terlebih dahulu sebelum mengirim pesanan ke dapur.',
                ]);
            }

            $items = $order->items()->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan tidak memiliki rincian menu.',
                ]);
            }

            // Lock menu records in a consistent order.
            $menuIds = $items->pluck('menu_id')
                ->unique()
                ->sort()
                ->values();

            $menus = Menu::query()
                ->whereIn('id', $menuIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $menu = $menus->get($item->menu_id);

                if (
                    ! $menu ||
                    ! $menu->is_available ||
                    $menu->stock < $item->quantity
                ) {
                    throw ValidationException::withMessages([
                        'order' => "Stok menu {$item->menu_name} tidak mencukupi atau sudah tidak tersedia.",
                    ]);
                }
            }

            foreach ($items as $item) {
                $menus->get($item->menu_id)->decrement(
                    'stock',
                    $item->quantity
                );
            }

            $order->update([
                'order_status' => 'confirmed',
            ]);

            $order->statusHistories()->create([
                'old_status' => 'pending',
                'new_status' => 'confirmed',
                'changed_by' => Auth::id(),
                'notes' => 'Pesanan dikonfirmasi oleh kasir.',
            ]);
        });

        return redirect()
            ->route('kasir.orders.show', $order)
            ->with('success', 'Pesanan berhasil dikonfirmasi dan stok menu telah diperbarui.');
    }

    public function reject(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order = Order::query()
                ->where('order_channel', 'online')
                ->lockForUpdate()
                ->findOrFail($order);

            if ($order->order_status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Hanya pesanan yang menunggu yang dapat ditolak.',
                ]);
            }

            $order->update([
                'order_status' => 'rejected',
            ]);

            $order->statusHistories()->create([
                'old_status' => 'pending',
                'new_status' => 'rejected',
                'changed_by' => Auth::id(),
                'notes' => $validated['reason'],
            ]);
        });

        return redirect()
            ->route('kasir.orders.show', $order)
            ->with('success', 'Pesanan berhasil ditolak.');
    }

    public function verifyPayment(Request $request, int $order, int $payment): RedirectResponse
    {
        DB::transaction(function () use ($request, $order, $payment) {
            $orderRecord = Order::query()
                ->where('order_channel', 'online')
                ->lockForUpdate()
                ->findOrFail($order);

            if (in_array($orderRecord->order_status, [
                'rejected',
                'cancelled',
            ], true)) {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran pesanan yang ditolak atau dibatalkan tidak dapat diverifikasi.',
                ]);
            }

            $paymentRecord = $orderRecord->payment()
                ->lockForUpdate()
                ->findOrFail($payment);

            if ($paymentRecord->payment_status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran ini sudah diproses sebelumnya.',
                ]);
            }

            if (! in_array($paymentRecord->payment_method, [
                'qris',
                'tunai',
            ], true)) {
                throw ValidationException::withMessages([
                    'payment' => 'Metode pembayaran ini tidak didukung.',
                ]);
            }

            if (
                $paymentRecord->payment_method === 'qris'
                && ! $paymentRecord->proof_image
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'Pelanggan belum mengunggah bukti pembayaran QRIS.',
                ]);
            }

            $cashDetails = [];
            if ($paymentRecord->payment_method === 'tunai') {
                $cashReceived = $request->validate([
                    'cash_received' => ['required', 'integer', 'min:' . (int) $paymentRecord->amount],
                ])['cash_received'];
                $cashDetails = [
                    'cash_received' => $cashReceived,
                    'change_due' => $cashReceived - (int) $paymentRecord->amount,
                ];
            }

            $paymentRecord->update($cashDetails + [
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
        });

        return redirect()
            ->route('kasir.orders.show', $order)
            ->with('success', 'Pembayaran berhasil diverifikasi.');
    }
}
