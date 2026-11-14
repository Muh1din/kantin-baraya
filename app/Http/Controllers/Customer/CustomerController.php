<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Services\VoucherService;
use App\Services\OperationalSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('customer.dashboard', [
            'categories' => MenuCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'popularMenus' => Menu::query()
                ->with('menuCategory')
                ->where('is_available', true)
                ->where('stock', '>', 0)
                ->orderByDesc('id')
                ->limit(2)
                ->get(),

            'recentOrders' => $user->orders()
                ->latest()
                ->limit(5)
                ->get(),

            'totalOrders' => $user->orders()->count(),

            'activeOrderCount' => $user->orders()
                ->whereIn('order_status', [
                    'pending',
                    'confirmed',
                    'processing',
                    'ready',
                ])
                ->count(),

            'completedOrderCount' => $user->orders()
                ->where('order_status', 'completed')
                ->count(),
        ]);
    }


    public function orders(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items', 'payment'])
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }


    public function storeOrder(Request $request, VoucherService $voucherService)
    {
        if (OperationalSchedule::isClosedToday()) {
            throw ValidationException::withMessages(['order' => 'Kantin sedang libur hari ini dan belum menerima pesanan.']);
        }

        $validated = $request->validate([
            'order_type' => ['required', 'in:bungkus'],
            'payment_method' => ['required', 'in:qris'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1800'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $order = DB::transaction(function () use ($validated, $request, $voucherService) {
            $menus = collect();
            foreach ($validated['items'] as $item) {
                $menu = Menu::query()->whereKey($item['id'])->lockForUpdate()->first();
                if (! $menu || ! $menu->is_available || $menu->stock < $item['quantity']) {
                    throw ValidationException::withMessages(['items' => ['Ada menu yang tidak tersedia atau stoknya tidak mencukupi. Silakan periksa keranjang.']]);
                }
                $menus->push(['menu' => $menu, 'quantity' => $item['quantity']]);
            }

            $subtotal = $menus->sum(fn($item) => $item['menu']->price * $item['quantity']);
            $voucherApplication = blank($validated['voucher_code'] ?? null)
                ? $voucherService->applyAutomatic($subtotal)
                : $voucherService->apply($validated['voucher_code'], $subtotal);
            $discountAmount = $voucherApplication['discount_amount'];
            $totalAmount = $voucherApplication['total_amount'];
            $notes = trim(implode("\n", array_filter([
                'Jadwal preorder: ' . $validated['pickup_date'] . ' ' . $validated['pickup_time'],
                $validated['notes'] ?? null,
            ])));
            $order = Order::create([
                'order_code' => 'ORD-' . Str::upper(Str::random(10)),
                'tracking_token' => Str::random(48),
                'customer_id' => $request->user()->id,
                'voucher_id' => $voucherApplication['voucher']?->id,
                'customer_name' => $request->user()->name,
                'order_channel' => 'online',
                'order_type' => 'bungkus',
                'order_status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $notes,
            ]);

            foreach ($menus as $item) {
                $menu = $item['menu'];
                $quantity = $item['quantity'];
                $order->items()->create([
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'quantity' => $quantity,
                    'unit_price' => $menu->price,
                    'subtotal' => $menu->price * $quantity,
                ]);
            }
            $order->payment()->create(['payment_method' => 'qris', 'payment_status' => 'pending', 'amount' => $totalAmount]);
            return $order;
        });

        $order->load('voucher');

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'order_code' => $order->order_code,
            'tracking_token' => $order->tracking_token,
            'discount_amount' => $order->discount_amount,
            'voucher_code' => $order->voucher?->code,
            'voucher_name' => $order->voucher?->name,
            'total_amount' => $order->total_amount,
        ], 201);
    }

    public function uploadPaymentProof(Request $request, string $token)
    {
        $validated = $request->validate(['payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $order = $request->user()->orders()->where('tracking_token', $token)->firstOrFail();
        $payment = $order->payment()->where('payment_method', 'qris')->firstOrFail();
        if ($payment->payment_status !== 'pending' || $order->order_status !== 'pending') {
            return response()->json(['message' => 'Pesanan ini tidak menerima bukti pembayaran baru.'], 422);
        }

        $path = $validated['payment_proof']->store('payment-proofs', 'public');
        $payment->update(['proof_image' => $path]);

        return response()->json([
            'message' => 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi kasir.',
            'order_code' => $order->order_code,
        ]);
    }
}
