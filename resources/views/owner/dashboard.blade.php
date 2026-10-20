<x-layouts.login title="Dashboard Owner">
    <div class="min-h-screen bg-stone-50 lg:flex">
        <aside class="border-b border-stone-200 bg-white lg:fixed lg:inset-y-0 lg:w-72 lg:border-b-0 lg:border-r">
            <div class="flex items-center gap-3 px-5 py-5 lg:px-7 lg:py-7">
                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-orange-500 text-lg font-black text-white shadow-lg shadow-orange-500/20">KB</div>
                <div>
                    <p class="font-black text-stone-900">Kantin Baraya</p>
                    <p class="text-xs font-medium text-stone-500">Panel Owner</p>
                </div>
            </div>

            <nav aria-label="Navigasi Owner" class="hide-scrollbar flex gap-2 overflow-x-auto px-4 pb-4 lg:block lg:space-y-1 lg:overflow-visible lg:px-4">
                <a href="{{ route('owner.dashboard') }}" aria-current="page" class="flex shrink-0 items-center gap-3 rounded-xl bg-orange-50 px-4 py-3 text-sm font-bold text-orange-700 lg:w-full">
                    <span aria-hidden="true">⌂</span> Dashboard
                </a>
                <a href="{{ route('owner.menu-categories.index') }}" class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700 lg:w-full">
                    <span aria-hidden="true">▦</span> Kategori Menu
                </a>
                <a href="{{ route('owner.menus.index') }}" class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700 lg:w-full">
                    <span aria-hidden="true">☷</span> Menu
                </a>
                <a href="{{ route('owner.ingredients.index') }}" class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700 lg:w-full">
                    <span aria-hidden="true">▤</span> Bahan Baku
                </a>
                <a href="{{ route('owner.recipes.index') }}" class="flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700 lg:w-full">
                    <span aria-hidden="true">▧</span> Resep Menu
                </a>
                <span aria-disabled="true" title="Modul POS belum tersedia" class="flex shrink-0 cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-stone-400 lg:w-full"><span aria-hidden="true">▣</span> POS</span>
                <span aria-disabled="true" title="Modul pre-order belum tersedia" class="flex shrink-0 cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-stone-400 lg:w-full"><span aria-hidden="true">◷</span> Pre-order</span>
                <span aria-disabled="true" title="Modul produksi belum tersedia" class="flex shrink-0 cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-stone-400 lg:w-full"><span aria-hidden="true">⚙</span> Produksi</span>
                <span aria-disabled="true" title="Modul loyalty belum tersedia" class="flex shrink-0 cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-stone-400 lg:w-full"><span aria-hidden="true">☆</span> Loyalty</span>
                <span aria-disabled="true" title="Modul laporan belum tersedia" class="flex shrink-0 cursor-not-allowed items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-stone-400 lg:w-full"><span aria-hidden="true">▥</span> Laporan</span>
            </nav>

            <div class="hidden border-t border-stone-100 p-4 lg:absolute lg:inset-x-0 lg:bottom-0 lg:block">
                <div class="flex items-center gap-3 rounded-xl bg-stone-50 p-3">
                    <div class="grid h-10 w-10 place-items-center rounded-full bg-orange-100 font-bold text-orange-700">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-stone-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-stone-500">Owner</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1 lg:ml-72">
            <header class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-200 bg-white px-5 py-4 sm:px-8 lg:px-10">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.16em] text-orange-600">Ringkasan operasional</p>
                    <h1 class="mt-1 text-2xl font-black text-stone-900 sm:text-3xl">Dashboard Owner</h1>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-bold text-stone-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700">Keluar</button>
                </form>
            </header>

            <main class="px-5 py-7 sm:px-8 lg:px-10 lg:py-9">
                <section class="mb-7 rounded-3xl bg-gradient-to-br from-orange-500 via-orange-500 to-amber-400 p-6 text-white shadow-lg shadow-orange-500/10 sm:p-8">
                    <p class="text-sm font-semibold text-white/80">Selamat datang kembali</p>
                    <h2 class="mt-2 text-2xl font-black sm:text-3xl">Halo, {{ auth()->user()->name }}.</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/90">Pantau ringkasan kantin dan buka pengelolaan menu dari satu tempat.</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('owner.menus.index') }}" class="rounded-xl bg-white px-4 py-3 text-sm font-bold text-orange-700 shadow transition hover:bg-orange-50">Kelola menu <span aria-hidden="true">→</span></a>
                        <a href="{{ route('owner.menu-categories.index') }}" class="rounded-xl border border-white/40 bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">Kategori Menu</a>
                    </div>
                </section>

                <section aria-labelledby="summaryTitle">
                    <div class="mb-4">
                        <h2 id="summaryTitle" class="text-lg font-extrabold text-stone-900">Ringkasan hari ini</h2>
                        <p class="mt-1 text-sm text-stone-500">Metrik ditampilkan berdasarkan data yang tersedia di aplikasi.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
                        <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-semibold text-stone-500">Penjualan hari ini</p>
                            <p class="mt-4 text-xl font-black text-stone-500">Belum tersedia</p>
                            <p class="mt-1 text-xs text-stone-400">Data penjualan belum tersedia.</p>
                        </article>
                        <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-semibold text-stone-500">Pesanan aktif</p>
                            <p class="mt-4 text-xl font-black text-stone-500">Belum tersedia</p>
                            <p class="mt-1 text-xs text-stone-400">Data pesanan belum tersedia.</p>
                        </article>
                        <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                            <p class="text-sm font-semibold text-stone-500">Jumlah menu aktif</p>
                            <p class="mt-4 text-3xl font-black text-orange-600">{{ number_format($activeMenuCount, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-stone-400">Menu dengan status aktif.</p>
                        </article>
                        <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-semibold text-stone-500">Stok bahan baku rendah</p>
                            <p class="mt-4 text-xl font-black text-stone-500">Belum tersedia</p>
                            <p class="mt-1 text-xs text-stone-400">Data bahan baku belum tersedia.</p>
                        </article>
                    </div>
                </section>

                <section class="mt-7 rounded-2xl border border-dashed border-stone-300 bg-white p-5 sm:p-6">
                    <h2 class="font-extrabold text-stone-900">Modul operasional</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-500">Navigasi yang belum aktif menunggu implementasi modul dan sumber datanya. Dashboard ini tidak menampilkan angka perkiraan untuk penjualan, pesanan, atau stok bahan baku.</p>
                </section>
            </main>
        </div>
    </div>
</x-layouts.login>
