<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(): View
    {
        return view('owner.vouchers.index', [
            'vouchers' => Voucher::query()->withCount('orders')->latest()->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        Voucher::query()->create($validated);

        return redirect()
            ->route('owner.vouchers.index')
            ->with('status', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('owner.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($this->validatedData($request, $voucher));

        return redirect()
            ->route('owner.vouchers.index')
            ->with('status', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->delete();

        return redirect()
            ->route('owner.vouchers.index')
            ->with('status', 'Voucher berhasil dihapus. Riwayat transaksi tetap tersimpan.');
    }

    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail) use ($voucher): void {
                    $normalizedCode = Str::upper(trim((string) $value));
                    $duplicate = Voucher::withTrashed()
                        ->whereRaw('UPPER(code) = ?', [$normalizedCode])
                        ->when($voucher, fn ($query) => $query->whereKeyNot($voucher->id))
                        ->exists();

                    if ($duplicate) {
                        $fail('Kode voucher sudah digunakan.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:120'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'minimum_order' => ['required', 'numeric', 'min:0', 'max:999999999999999.99'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validated['discount_type'] === 'percentage' && (float) $validated['discount_value'] > 100) {
            throw ValidationException::withMessages([
                'discount_value' => 'Diskon persentase tidak boleh lebih dari 100%.',
            ]);
        }

        $validated['code'] = Str::upper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
