<x-layouts.owner title="Voucher Diskon">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Voucher Diskon</h1>
            <p class="mt-1 text-sm text-stone-500">Buat dan atur voucher yang dapat digunakan pada pesanan online.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Buat Voucher</h2>
            <p class="mt-1 text-sm text-stone-500">Voucher dapat berupa potongan persentase atau potongan nominal tetap.</p>
            @include('owner.vouchers._form', ['voucher' => null])
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Voucher</h2>
                <span class="text-sm text-stone-500">{{ $vouchers->total() }} voucher</span>
            </div>

            @if ($vouchers->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada voucher diskon.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Kode / Nama</th>
                                <th scope="col" class="px-5 py-3.5">Diskon</th>
                                <th scope="col" class="px-5 py-3.5">Minimum</th>
                                <th scope="col" class="px-5 py-3.5">Masa Berlaku</th>
                                <th scope="col" class="px-5 py-3.5">Penggunaan</th>
                                <th scope="col" class="px-5 py-3.5">Status</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($vouchers as $voucher)
                                @php
                                    $today = now()->toDateString();
                                    $voucherStatus = ! $voucher->is_active
                                        ? 'Nonaktif'
                                        : ($voucher->starts_at && $voucher->starts_at->toDateString() > $today
                                            ? 'Belum berlaku'
                                            : ($voucher->ends_at && $voucher->ends_at->toDateString() < $today
                                                ? 'Kedaluwarsa'
                                                : ($voucher->usage_limit !== null && $voucher->orders_count >= $voucher->usage_limit
                                                    ? 'Kuota habis'
                                                    : 'Aktif')));
                                    $voucherStatusClass = $voucherStatus === 'Aktif'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-stone-100 text-stone-700';
                                @endphp
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-black tracking-wide text-orange-700">{{ $voucher->code }}</p>
                                        <p class="mt-1 text-xs text-stone-500">{{ $voucher->name }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-800">
                                        @if ($voucher->discount_type === 'percentage')
                                            {{ rtrim(rtrim(number_format((float) $voucher->discount_value, 2, ',', '.'), '0'), ',') }}%
                                        @else
                                            Rp{{ number_format((float) $voucher->discount_value, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">Rp{{ number_format((float) $voucher->minimum_order, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-stone-600">
                                        {{ $voucher->starts_at?->format('d/m/Y') ?? '—' }} – {{ $voucher->ends_at?->format('d/m/Y') ?? 'Tanpa batas' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        {{ number_format($voucher->orders_count, 0, ',', '.') }}
                                        / {{ $voucher->usage_limit ? number_format($voucher->usage_limit, 0, ',', '.') : '∞' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $voucherStatusClass }}">
                                            {{ $voucherStatus }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('owner.vouchers.edit', $voucher) }}" class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Edit</a>
                                            <form action="{{ route('owner.vouchers.destroy', $voucher) }}" method="POST"
                                                onsubmit="return confirm('Hapus voucher ini? Riwayat transaksi yang menggunakan voucher tetap tersimpan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($vouchers->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $vouchers->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.owner>
