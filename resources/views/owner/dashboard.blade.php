<x-layouts.owner title="Dashboard">

    <div class="space-y-8">

        {{-- Hero Banner Welcome --}}
        <section class="rounded-3xl bg-orange-500 p-6 text-white shadow-sm sm:p-8">
            <div class="max-w-2xl">

                <h1 class="mt-3 text-2xl font-black text-white sm:text-3xl lg:text-4xl">
                    Halo, {{ auth()->user()->name }}
                </h1>

                <p class="mt-2 text-sm leading-relaxed text-orange-100 sm:text-base">
                    Pantau ringkasan operasional Kantin Baraya dan kelola seluruh kebutuhan menu dari satu tempat.
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-3">

                    <a href="{{ route('owner.menus.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-orange-600 transition-all hover:bg-orange-50 active:scale-95">

                        Kelola Menu

                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>

                    </a>

                    <a href="{{ route('owner.menu-categories.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/40 bg-orange-600 px-4 py-2.5 text-sm font-bold text-white transition-all hover:bg-orange-700 active:scale-95">

                        Kategori Menu

                    </a>

                </div>
            </div>
        </section>


        {{-- Section Ringkasan Hari Ini --}}
        <section aria-labelledby="summaryTitle" class="space-y-4">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="summaryTitle" class="text-xl font-black text-stone-900">
                        Ringkasan Hari Ini
                    </h2>

                    <p class="mt-0.5 text-sm text-stone-500">
                        Metrik terkini berdasarkan data operasional kantin.
                    </p>
                </div>
            </div>


            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Penjualan Hari Ini --}}
                <article
                    class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition-all hover:border-stone-300">

                    <div class="flex items-center justify-between">

                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">
                            Penjualan Hari Ini
                        </p>

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>

                        </div>
                    </div>

                    <p class="mt-4 text-xl font-extrabold text-stone-900">
                        Rp {{ number_format($todaySales, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-stone-400">
                        Total pembayaran berhasil hari ini.
                    </p>

                </article>


                {{-- Pesanan Aktif --}}
                <article
                    class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition-all hover:border-stone-300">

                    <div class="flex items-center justify-between">

                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">
                            Pesanan Aktif
                        </p>

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>

                        </div>
                    </div>

                    <p class="mt-4 text-3xl font-black text-stone-900">
                        {{ number_format($activeOrderCount, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-stone-400">
                        Pesanan yang sedang diproses.
                    </p>

                </article>


                {{-- Menu Aktif --}}
                <article
                    class="rounded-2xl border border-orange-200 bg-white p-5 shadow-sm transition-all hover:border-orange-300">

                    <div class="flex items-center justify-between">

                        <p class="text-xs font-bold uppercase tracking-wider text-orange-600">
                            Menu Aktif
                        </p>

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 text-orange-600">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>

                        </div>
                    </div>

                    <p class="mt-3 text-3xl font-black text-stone-900">
                        {{ number_format($activeMenuCount, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs font-medium text-emerald-600">
                        Tersedia untuk dipesan.
                    </p>

                </article>


                {{-- Transaksi Hari Ini --}}
                <article
                    class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition-all hover:border-stone-300">

                    <div class="flex items-center justify-between">

                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">
                            Transaksi Hari Ini
                        </p>

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-purple-600">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h6m-6 4h6" />
                            </svg>

                        </div>
                    </div>

                    <p class="mt-4 text-3xl font-black text-stone-900">
                        {{ number_format($todayTransactionCount, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-stone-400">
                        Transaksi dengan pembayaran berhasil.
                    </p>

                </article>

            </div>
        </section>


        {{-- Aktivitas Pesanan Terbaru --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-200 px-5 py-5 sm:px-6">
                <h2 class="text-lg font-extrabold text-stone-900">
                    Aktivitas Pesanan Terbaru
                </h2>
                <p class="mt-1 text-sm text-stone-500">
                    Lima pesanan terakhir yang tercatat.
                </p>
            </div>

            @php
                $statusLabels = [
                    'pending' => 'Menunggu Konfirmasi',
                    'confirmed' => 'Dikonfirmasi',
                    'rejected' => 'Ditolak',
                    'in_progress' => 'Sedang Dimasak',
                    'completed' => 'Selesai',
                ];

                $statusColors = [
                    'pending' => 'bg-amber-100 text-amber-800',
                    'confirmed' => 'bg-blue-100 text-blue-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'in_progress' => 'bg-orange-100 text-orange-800',
                    'completed' => 'bg-green-100 text-green-800',
                ];
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-4 font-bold">Kode Pesanan</th>
                            <th class="px-5 py-4 font-bold">Nama Pelanggan</th>
                            <th class="px-5 py-4 font-bold">Total Pesanan</th>
                            <th class="px-5 py-4 font-bold">Status</th>
                            <th class="px-5 py-4 font-bold">Waktu Pesanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($recentOrders as $order)
                            <tr class="transition hover:bg-stone-50">
                                <td class="whitespace-nowrap px-5 py-4 font-bold text-stone-800">
                                    {{ $order->order_code }}
                                </td>
                                <td class="px-5 py-4 text-stone-600">
                                    {{ $order->customer_name }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-800">
                                    Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusColors[$order->order_status] ?? 'bg-stone-100 text-stone-700' }}">
                                        {{ $statusLabels[$order->order_status] ?? ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-stone-500">
                                    {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <p class="font-semibold text-stone-700">Belum ada pesanan.</p>
                                    <p class="mt-1 text-sm text-stone-500">
                                        Pesanan terbaru akan muncul di sini setelah pelanggan melakukan pemesanan.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>


        {{-- Navigasi Cepat / Modul Operasional --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-extrabold text-stone-900">
                Akses Cepat Modul
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Pilih modul di bawah ini untuk memulai pengoperasian aplikasi.
            </p>


            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">


                {{-- Resep Menu --}}
                <a href="{{ route('owner.recipes.index') }}"
                    class="group flex items-start gap-4 rounded-xl border border-stone-200 p-4 transition-all hover:border-orange-300 hover:bg-orange-50/50">

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-600 transition-colors group-hover:bg-orange-500 group-hover:text-white">

                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>

                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-stone-900 group-hover:text-orange-600">
                            Resep Menu
                        </h3>

                        <p class="mt-0.5 text-xs text-stone-500">
                            Atur takaran tiap porsi
                        </p>
                    </div>

                </a>


                {{-- Kategori Menu --}}
                <a href="{{ route('owner.menu-categories.index') }}"
                    class="group flex items-start gap-4 rounded-xl border border-stone-200 p-4 transition-all hover:border-orange-300 hover:bg-orange-50/50">

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-600 transition-colors group-hover:bg-orange-500 group-hover:text-white">

                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>

                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-stone-900 group-hover:text-orange-600">
                            Kategori Menu
                        </h3>

                        <p class="mt-0.5 text-xs text-stone-500">
                            Pengelompokan makanan/minuman
                        </p>
                    </div>

                </a>


                {{-- Daftar Menu --}}
                <a href="{{ route('owner.menus.index') }}"
                    class="group flex items-start gap-4 rounded-xl border border-stone-200 p-4 transition-all hover:border-orange-300 hover:bg-orange-50/50">

                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-600 transition-colors group-hover:bg-orange-500 group-hover:text-white">

                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-stone-900 group-hover:text-orange-600">
                            Daftar Menu
                        </h3>

                        <p class="mt-0.5 text-xs text-stone-500">
                            Ubah harga & ketersediaan
                        </p>
                    </div>

                </a>

            </div>
        </section>

    </div>

</x-layouts.owner>