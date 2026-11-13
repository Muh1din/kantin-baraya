<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuestOrderController extends Controller
{
    public function store(Request $request, VoucherService $voucherService)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'order_type' => ['required', 'in:bungkus,makan_di_tempat'],
            'payment_method' => ['required', 'in:tunai,qris'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $order = DB::transaction(function () use ($validated, $voucherService) {
            $menus = collect();

            foreach ($validated['items'] as $item) {
                $menu = Menu::query()
                    ->whereKey($item['id'])
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $menu ||
                    ! $menu->is_available ||
                    $menu->stock < $item['quantity']
                ) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Ada menu yang tidak tersedia atau stoknya tidak mencukupi. Silakan periksa keranjang.',
                        ],
                    ]);
                }

                $menus->push([
                    'menu' => $menu,
                    'quantity' => $item['quantity'],
                ]);
            }

            $subtotal = $menus->sum(
                fn ($item) => $item['menu']->price * $item['quantity']
            );
            $voucherApplication = $voucherService->apply($validated['voucher_code'] ?? null, $subtotal);
            $discountAmount = $voucherApplication['discount_amount'];
            $totalAmount = $voucherApplication['total_amount'];

            $order = Order::create([
                'order_code' => 'ORD-' . Str::upper(Str::random(10)),
                'tracking_token' => Str::random(48),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'voucher_id' => $voucherApplication['voucher']?->id,
                'order_channel' => 'online',
                'order_type' => $validated['order_type'],
                'order_status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
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

            $order->payment()->create([
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'amount' => $totalAmount,
            ]);

            return $order;
        });

        $order->load('voucher');

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'order_code' => $order->order_code,
            'tracking_token' => $order->tracking_token,
            'discount_amount' => $order->discount_amount,
            'voucher_code' => $order->voucher?->code,
            'total_amount' => $order->total_amount,
            'payment_method' => $validated['payment_method'],
        ], 201);
    }

    public function uploadPaymentProof(Request $request, string $token)
    {
        $validated = $request->validate([
            'payment_proof' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $order = Order::where('tracking_token', $token)->firstOrFail();

        $payment = $order->payment()
            ->where('payment_method', 'qris')
            ->firstOrFail();

        if ($payment->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Pesanan ini tidak menerima bukti pembayaran baru.',
            ], 422);
        }

        $path = $validated['payment_proof']->store(
            'payment-proofs',
            'public'
        );

        $payment->update([
            'proof_image' => $path,
        ]);

        return response()->json([
            'message' => 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi.',
            'order_code' => $order->order_code,
        ]);
    }
}