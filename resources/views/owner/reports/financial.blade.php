<x-layouts.owner title="Laporan Keuangan">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Laporan Keuangan</h1>
            <p class="mt-1 text-sm text-stone-500">Ringkasan pendapatan dari pembayaran yang berhasil pada periode yang
                dipilih.</p>
        </header>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('owner.reports.financial') }}" method="GET"
                class="grid gap-4 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end">
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
                <button type="submit"
                    class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">
                    Tampilkan Laporan
                </button>
                <a href="{{ route('owner.reports.financial') }}"
                    class="rounded-xl border border-stone-300 px-5 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50">
                    Bulan Ini
                </a>
            </form>
        </section>

        <section class="grid gap-4 sm:grid-cols-2">
            <article class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Total Pendapatan</p>
                <p class="mt-3 text-3xl font-black text-stone-900">
                    Rp{{ number_format((float) $totalRevenue, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-stone-500">
                    {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }} –
                    {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}</p>
            </article>
            <article class="rounded-2xl border border-orange-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-orange-700">Total Transaksi Berhasil</p>
                <p class="mt-3 text-3xl font-black text-stone-900">{{ number_format($transactionCount, 0, ',', '.') }}
                </p>
                <p class="mt-2 text-sm text-stone-500">Setiap pesanan dihitung satu kali.</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Rincian Pembayaran berdasarkan Metode</h2>
                <p class="mt-1 text-sm text-stone-500">Hanya pembayaran berstatus berhasil yang dihitung.</p>
            </div>

            @if ($paymentBreakdown->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Tidak ada pembayaran berhasil pada periode ini.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Metode Pembayaran</th>
                                <th scope="col" class="px-5 py-3.5">Jumlah Transaksi</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($paymentBreakdown as $row)
                                <tr class="hover:bg-stone-50/50">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">
                                        {{ strtoupper($row->payment_method) }}</td>
                                    <td class="px-5 py-4">{{ number_format($row->transaction_count, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-900 sm:px-6">
                                        Rp{{ number_format((float) $row->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-stone-200 bg-stone-50 font-bold text-stone-900">
                            <tr>
                                <td class="px-5 py-4 sm:px-6">Total</td>
                                <td class="px-5 py-4">{{ number_format($transactionCount, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                    Rp{{ number_format((float) $totalRevenue, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.owner>
