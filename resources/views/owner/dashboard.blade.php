<x-layouts.owner title="Dashboard Owner">
    <main class="min-h-[calc(100vh-5rem)] bg-stone-50 px-5 py-7 sm:px-8 lg:px-6 lg:py-9">
        <div class="w-full">
            <section class="mb-7 rounded-3xl bg-gradient-to-br from-orange-500 via-orange-500 to-amber-400 p-6 text-white shadow-lg shadow-orange-500/10 sm:p-8">
                <p class="text-sm font-semibold text-white/80">Selamat datang kembali</p>
                <h2 class="mt-2 text-2xl font-black sm:text-3xl">Halo, {{ auth()->user()->name }}.</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/90">Pantau ringkasan kantin dan buka pengelolaan menu dari satu tempat.</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('owner.menus.index') }}" class="rounded-xl bg-white px-4 py-3 text-sm font-bold text-orange-700 shadow transition hover:bg-orange-50">
                        Kelola menu <span aria-hidden="true">→</span>
                    </a>
                    <a href="{{ route('owner.menu-categories.index') }}" class="rounded-xl border border-white/40 bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20">
                        Kategori Menu
                    </a>
                </div>
            </section>

            <section aria-labelledby="summaryTitle" class="mb-7">
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

            <section class="rounded-2xl border border-dashed border-stone-300 bg-white p-5 sm:p-6">
                <h2 class="font-extrabold text-stone-900">Modul operasional</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Navigasi yang belum aktif menunggu implementasi modul dan sumber datanya. Dashboard ini tidak menampilkan angka perkiraan untuk penjualan, pesanan, atau stok bahan baku.</p>
            </section>
        </div>
    </main>
</x-layouts.owner>
