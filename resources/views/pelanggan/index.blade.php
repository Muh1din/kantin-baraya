<x-layouts.home>
    <section id="menu" class="space-y-6">

        {{-- HEADER MENU --}}
        <div class="space-y-2">
            <span class="text-xs font-bold tracking-widest text-primary">
                KANTIN BARAYA
            </span>

            <h1 class="text-2xl font-extrabold sm:text-3xl">
                Daftar Makanan & Minuman
            </h1>

            <p class="text-sm leading-6 text-gray-500">
                Temukan makanan favorit dan minuman segar untuk menemani harimu.
            </p>
        </div>

        {{-- BANNER MENU --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-[#FF8A3D] p-6 text-white sm:p-8">
            <div class="relative z-10 max-w-xl">
                <span class="rounded-full bg-white/20 px-3 py-1 text-[10px] font-semibold tracking-widest">
                    FRESHLY MADE, JUST FOR YOU
                </span>

                <h2 class="mt-4 text-2xl font-extrabold leading-tight sm:text-3xl">
                    Lapar? Yuk, pilih
                    <span class="text-[#FFE0A3]">
                        menu favoritmu!
                    </span>
                </h2>

                <p class="mt-3 max-w-md text-sm leading-6 text-white/90">
                    Aneka makanan lezat dan minuman menyegarkan siap menemani aktivitasmu.
                </p>

                <a href="#daftar-menu"
                   class="mt-5 inline-flex rounded-xl bg-white px-5 py-3 text-xs font-bold text-primary transition hover:bg-orange-50">
                    Lihat Daftar Menu ↓
                </a>
            </div>

            <div class="absolute bottom-0 right-5 text-7xl opacity-25 sm:right-10 sm:text-9xl">
                🍜
            </div>
        </div>

        {{-- PENCARIAN DAN FILTER --}}
        <div id="daftar-menu" class="space-y-4">

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        ⌕
                    </span>

                    <input
                        id="menuSearch"
                        type="search"
                        placeholder="Cari makanan atau minuman..."
                        class="w-full rounded-2xl border border-gray-200 bg-white py-3 pl-10 pr-4 text-sm outline-none transition focus:border-primary"
                    >
                </div>

                <select
                    id="sortMenu"
                    class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:border-primary"
                >
                    <option value="featured">Menu rekomendasi</option>
                    <option value="low">Harga terendah</option>
                    <option value="high">Harga tertinggi</option>
                    <option value="name">Nama A–Z</option>
                </select>
            </div>

            {{-- KATEGORI --}}
            <div class="space-y-3">
                <h2 class="font-bold">Kategori menu</h2>

                <div id="categories"
                     class="flex gap-2 overflow-x-auto pb-2">
                    <button type="button" data-category="all"
                            class="chip active whitespace-nowrap">
                        Semua Menu
                    </button>

                    <button type="button" data-category="makanan"
                            class="chip whitespace-nowrap">
                        🍛 Makanan
                    </button>

                    <button type="button" data-category="minuman"
                            class="chip whitespace-nowrap">
                        🥤 Minuman
                    </button>

                    <button type="button" data-category="snack"
                            class="chip whitespace-nowrap">
                        🍟 Snack
                    </button>
                </div>
            </div>

            {{-- INFORMASI HASIL --}}
            <div class="flex items-center justify-between gap-3">
                <p id="resultCount" class="text-xs text-gray-500">
                    Daftar menu
                </p>

                <button
                    id="resetFilters"
                    type="button"
                    class="text-xs font-bold text-primary hover:underline"
                >
                    Reset filter
                </button>
            </div>

            {{-- DAFTAR PRODUK --}}
            <div id="menuGrid"
                 class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                {{-- Kartu menu dirender oleh JavaScript --}}
            </div>

            {{-- JIKA MENU KOSONG --}}
            <div id="noMenu"
                 class="hidden rounded-2xl border border-dashed bg-white p-10 text-center">
                <div class="text-4xl">🍽️</div>

                <h3 class="mt-3 font-bold">
                    Menu tidak ditemukan
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Coba kata kunci lain atau ubah kategori pilihanmu.
                </p>
            </div>

        </div>

    </section>
    <script>
    window.menuItems = {{ Illuminate\Support\Js::from(
        $menus->map(function ($menu) {
            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'category' => $menu->category,
                'price' => $menu->price,
                'description' => $menu->description ?? '',
                'image' => $menu->image
                    ? asset('storage/' . $menu->image)
                    : '',
                    'stock' => $menu->stock,
'is_available' => (bool) $menu->is_available,
            ];
        })->values()
    ) }};
</script>
</x-layouts.home>
