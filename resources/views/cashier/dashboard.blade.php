<x-layouts.cashier title="Dashboard Kasir">

    <div class="space-y-6">

        {{-- PAGE HEADER --}}
        <div>
            <p class="mt-1 text-sm text-stone-500">
                Selamat datang, {{ auth()->user()->name }}.
                Berikut ringkasan aktivitas Kantin Baraya hari ini.
            </p>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

            {{-- TOTAL PESANAN --}}
            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm font-medium text-stone-500">
                    Total Pesanan
                </p>

                <h2 class="mt-3 text-3xl font-black text-stone-900">
                    {{ $totalOrders ?? 0 }}
                </h2>

                <p class="mt-2 text-xs text-stone-400">
                    Pesanan hari ini
                </p>
            </div>

            {{-- PESANAN MENUNGGU --}}
            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm font-medium text-stone-500">
                    Menunggu Verifikasi
                </p>

                <h2 class="mt-3 text-3xl font-black text-orange-600">
                    {{ $pendingOrders ?? 0 }}
                </h2>

                <p class="mt-2 text-xs text-stone-400">
                    Menunggu pemeriksaan pembayaran
                </p>
            </div>

            {{-- PESANAN BERJALAN --}}
            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm font-medium text-stone-500">
                    Pesanan Berjalan
                </p>

                <h2 class="mt-3 text-3xl font-black text-blue-600">
                    {{ $processingOrders ?? 0 }}
                </h2>

                <p class="mt-2 text-xs text-stone-400">
                    Sedang diproses
                </p>
            </div>

            {{-- TOTAL PENJUALAN --}}
            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm font-medium text-stone-500">
                    Penjualan Hari Ini
                </p>

                <h2 class="mt-3 text-2xl font-black text-green-600">
                    Rp{{ number_format($todaySales ?? 0, 0, ',', '.') }}
                </h2>

                <p class="mt-2 text-xs text-stone-400">
                    Total transaksi hari ini
                </p>
            </div>

        </div>

        {{-- RECENT ORDERS --}}
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white">

            <div class="flex items-center justify-between border-b border-stone-100 p-5">

                <div>
                    <h2 class="font-bold text-stone-900">
                        Pesanan Terbaru
                    </h2>

                    <p class="mt-1 text-xs text-stone-500">
                        Daftar pesanan customer terbaru.
                    </p>
                </div>

                <a href="{{ route('kasir.orders.index') }}"
                    class="text-sm font-bold text-orange-600 transition hover:text-orange-700">
                    Lihat Semua
                </a>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="bg-stone-50 text-xs uppercase text-stone-500">
                        <tr>
                            <th class="px-5 py-4">Kode Pesanan</th>
                            <th class="px-5 py-4">Pelanggan</th>
                            <th class="px-5 py-4">Total</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-stone-100">

                        @forelse ($recentOrders ?? [] as $order)
                            <tr class="transition hover:bg-stone-50">

                                <td class="px-5 py-4 font-bold text-stone-800">
                                    {{ $order->order_code }}
                                </td>

                                <td class="px-5 py-4 text-stone-600">
                                    {{ $order->customer_name }}
                                </td>

                                <td class="px-5 py-4 font-semibold text-stone-800">
                                    Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-stone-500">
                                    Belum ada pesanan hari ini.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-layouts.cashier>
