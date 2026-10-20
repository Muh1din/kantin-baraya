<x-layouts.login title="Riwayat Transaksi Kasir">
    <main class="min-h-screen bg-orange-50/60 px-4 py-8 sm:px-6">
        <div class="mx-auto max-w-7xl">
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-gradient-to-br from-orange-500 via-orange-500 to-amber-400 p-6 text-white shadow-lg">
                <div><p class="text-sm font-semibold text-white/80">Kantin Baraya</p><h1 class="mt-1 text-3xl font-black">Riwayat Transaksi</h1><p class="mt-1 text-sm">Transaksi yang dibuat oleh akun kasir ini.</p></div>
                <div class="flex flex-wrap items-center gap-3">@include('cashier.pos._navigation')<form action="{{ route('logout') }}" method="POST">@csrf<button class="rounded-xl bg-white/20 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/30">Keluar</button></form></div>
            </header>

            <section class="mt-6 rounded-3xl border border-orange-100 bg-white p-5 shadow-sm sm:p-6">
                <form method="GET" action="{{ route('kasir.pos.history') }}" class="grid gap-3 sm:grid-cols-[1fr_220px_auto_auto] sm:items-end">
                    <label class="block text-sm font-semibold text-stone-700">Nomor invoice
                        <input type="search" name="invoice" value="{{ $filters['invoice'] ?? '' }}" placeholder="Cari nomor invoice..." class="mt-1.5 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-normal">
                    </label>
                    <label class="block text-sm font-semibold text-stone-700">Tanggal transaksi
                        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="mt-1.5 w-full rounded-xl border border-stone-200 px-4 py-2.5 font-normal">
                    </label>
                    <button class="rounded-xl bg-orange-500 px-5 py-2.5 font-bold text-white hover:bg-orange-600">Terapkan</button>
                    <a href="{{ route('kasir.pos.history') }}" class="rounded-xl border border-stone-200 px-5 py-2.5 text-center font-bold text-stone-700 hover:bg-stone-50">Reset</a>
                </form>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b border-stone-200 text-xs uppercase tracking-wide text-stone-500"><tr><th class="px-3 py-3">Invoice</th><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3">Total</th><th class="px-3 py-3">Status transaksi</th><th class="px-3 py-3">Status pembayaran</th><th class="px-3 py-3 text-right">Detail</th></tr></thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse ($sales as $sale)
                                <tr>
                                    <td class="px-3 py-4 font-bold text-stone-800">{{ $sale->invoice_number }}</td>
                                    <td class="px-3 py-4 text-stone-600">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-4 font-semibold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                    <td class="px-3 py-4 capitalize text-stone-600">{{ str_replace('_', ' ', $sale->status) }}</td>
                                    <td class="px-3 py-4 capitalize text-stone-600">{{ str_replace('_', ' ', $sale->payment_status) }}</td>
                                    <td class="px-3 py-4 text-right"><a href="{{ route('kasir.pos.receipt', $sale) }}" class="inline-flex rounded-lg border border-orange-200 px-3 py-2 font-bold text-orange-700 hover:bg-orange-50">Buka nota</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-10 text-center text-stone-500">Tidak ada transaksi yang cocok dengan pencarian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($sales->hasPages())<div class="mt-5">{{ $sales->links() }}</div>@endif
            </section>
        </div>
    </main>
</x-layouts.login>
