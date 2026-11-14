<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    /** Apply the best active percentage voucher whose minimum order is met. */
    public function applyAutomatic(int|float|string $subtotal): array
    {
        $subtotal = (float) $subtotal;
        $today = now()->toDateString();
        $voucher = Voucher::query()
            ->where('is_active', true)
            ->where('discount_type', 'percentage')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today))
            ->where('minimum_order', '<=', $subtotal)
            ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereRaw('(select count(*) from orders where orders.voucher_id = vouchers.id) < vouchers.usage_limit'))
            ->orderByDesc('discount_value')
            ->orderBy('minimum_order')
            ->lockForUpdate()
            ->first();

        if (! $voucher) {
            return ['voucher' => null, 'discount_amount' => 0.0, 'total_amount' => $subtotal];
        }

        $discountAmount = min($subtotal, round($subtotal * (float) $voucher->discount_value / 100, 2));

        return [
            'voucher' => $voucher,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $subtotal - $discountAmount),
        ];
    }

    /**
     * Validate an optional voucher and calculate its discount for an order subtotal.
     * Must be called inside the order-creation transaction.
     *
     * @return array{voucher: ?Voucher, discount_amount: float, total_amount: float}
     */
    public function apply(?string $code, int|float|string $subtotal): array
    {
        $subtotal = (float) $subtotal;
        if (blank($code)) {
            return [
                'voucher' => null,
                'discount_amount' => 0.0,
                'total_amount' => $subtotal,
            ];
        }

        $voucher = Voucher::query()
            ->where('code', Str::upper(trim($code)))
            ->lockForUpdate()
            ->first();

        if (! $voucher || ! $voucher->is_active) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Kode voucher tidak ditemukan atau sedang tidak aktif.',
            ]);
        }

        $today = now()->toDateString();
        if (
            ($voucher->starts_at && $voucher->starts_at->toDateString() > $today)
            || ($voucher->ends_at && $voucher->ends_at->toDateString() < $today)
        ) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher belum berlaku atau masa berlakunya sudah berakhir.',
            ]);
        }

        if ($subtotal < (float) $voucher->minimum_order) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Subtotal belum memenuhi minimum pembelian voucher.',
            ]);
        }

        if ($voucher->usage_limit !== null && $voucher->orders()->count() >= $voucher->usage_limit) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Kuota penggunaan voucher sudah habis.',
            ]);
        }

        $discountAmount = $voucher->discount_type === 'percentage'
            ? round($subtotal * (float) $voucher->discount_value / 100, 2)
            : (float) $voucher->discount_value;
        $discountAmount = min($subtotal, $discountAmount);

        return [
            'voucher' => $voucher,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $subtotal - $discountAmount),
        ];
    }
}
