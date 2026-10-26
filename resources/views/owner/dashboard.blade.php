<x-layouts.owner title="Dashboard">
    <div class="space-y-8">
        {{-- Hero Banner Welcome (Solid Orange) --}}
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
                    <h2 id="summaryTitle" class="text-xl font-black text-stone-900">Ringkasan Hari Ini</h2>
                    <p class="mt-0.5 text-sm text-stone-500">Metrik terkini berdasarkan data operasional kantin.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Penjualan Hari Ini --}}
                <article
                    class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition-all hover:border-stone-300">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">Penjualan Hari Ini</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-stone-100 text-stone-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-xl font-extrabold text-stone-400">Belum tersedia</p>
                    <p class="mt-1 text-xs text-stone-400">Menunggu modul transaksi.</p>
                </article>

                {{-- Pesanan Aktif --}}
                <article
                    class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition-all hover:border-stone-300">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">Pesanan Aktif</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-stone-100 text-stone-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-xl font-extrabold text-stone-400">Belum tersedia</p>
                    <p class="mt-1 text-xs text-stone-400">Menunggu modul pesanan.</p>
                </article>

                {{-- Jumlah Menu Aktif --}}
                <article
                    class="rounded-2xl border border-orange-200 bg-white p-5 shadow-sm transition-all hover:border-orange-300">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-orange-600">Menu Aktif</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-black text-stone-900">
                        {{ number_format($activeMenuCount, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs font-medium text-emerald-600">Tersedia untuk dipesan</p>
                </article>

                {{-- Stok Bahan Baku Rendah --}}
                <article
                    class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition-all hover:border-stone-300">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">Stok Rendah</p>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-stone-100 text-stone-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-xl font-extrabold text-stone-400">Belum tersedia</p>
                    <p class="mt-1 text-xs text-stone-400">Menunggu integrasi stok.</p>
                </article>
            </div>
        </section>

        {{-- Navigasi Cepat / Modul Operasional --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-stone-900">Akses Cepat Modul</h2>
            <p class="mt-1 text-sm text-stone-500">Pilih modul di bawah ini untuk memulai pengoperasian aplikasi.</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('owner.ingredients.index') }}"
                    class="group flex items-start gap-4 rounded-xl border border-stone-200 p-4 transition-all hover:border-orange-300 hover:bg-orange-50/50">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-600 transition-colors group-hover:bg-orange-500 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 group-hover:text-orange-600">Bahan Baku</h3>
                        <p class="mt-0.5 text-xs text-stone-500">Stok & penyesuaian persediaan</p>
                    </div>
                </a>

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
                        <h3 class="text-sm font-bold text-stone-900 group-hover:text-orange-600">Resep Menu</h3>
                        <p class="mt-0.5 text-xs text-stone-500">Atur takaran tiap porsi</p>
                    </div>
                </a>

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
                        <h3 class="text-sm font-bold text-stone-900 group-hover:text-orange-600">Kategori Menu</h3>
                        <p class="mt-0.5 text-xs text-stone-500">Pengelompokan makanan/minuman</p>
                    </div>
                </a>

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
                        <h3 class="text-sm font-bold text-stone-900 group-hover:text-orange-600">Daftar Menu</h3>
                        <p class="mt-0.5 text-xs text-stone-500">Ubah harga & ketersediaan</p>
                    </div>
                </a>
            </div>
        </section>
    </div>
</x-layouts.owner>
