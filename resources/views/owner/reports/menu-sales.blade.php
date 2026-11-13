<x-layouts.owner title="Laporan Penjualan Menu">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Laporan Penjualan Menu</h1>
            <p class="mt-1 text-sm text-stone-500">Jumlah menu dan pendapatan dari pesanan dengan pembayaran berhasil.</p>
        </header>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('owner.reports.menu-sales') }}" method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end">
                <div>
                    <label for="start_date" class="mb-2 block text-sm font-semibold text-stone-700">Dari tanggal</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $startDate }}" required
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="end_date" class="mb-2 block text-sm font-semibold text-stone-700">Sampai tanggal</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $endDate }}" required
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Tampilkan Laporan</button>
                <a href="{{ route('owner.reports.menu-sales') }}" class="rounded-xl border border-stone-300 px-5 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Bulan Ini</a>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-extrabold text-stone-800">Penjualan per Menu</h2>
                    <p class="mt-1 text-sm text-stone-500">Periode {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </div>
                <span class="text-sm text-stone-500">{{ number_format($menuSales->total(), 0, ',', '.') }} menu</span>
            </div>

            @if ($menuSales->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Tidak ada penjualan menu dengan pembayaran berhasil pada periode ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Nama Menu</th>
                                <th scope="col" class="px-5 py-3.5">Total Jumlah Terjual</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($menuSales as $row)
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $row->menu_name }}</td>
                                    <td class="px-5 py-4">{{ number_format($row->quantity_sold, 0, ',', '.') }} porsi</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-900 sm:px-6">Rp{{ number_format((float) $row->revenue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($menuSales->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $menuSales->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.owner>
