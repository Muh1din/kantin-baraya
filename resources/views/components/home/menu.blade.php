<section id="menu" class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black tracking-[.2em] text-orange-500">
                    TEMUKAN FAVORITMU
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                    Menu Makanan & Minuman
                </h2>

                <p class="mt-2 text-sm text-stone-500">
                    Pilih menu yang bikin kamu makin semangat.
                </p>
            </div>
        </div>

        <!-- SEARCH + SORT -->
        <div class="mb-5 flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg text-stone-400">⌕</span>

                <input id="menuSearch" type="search" autocomplete="off"
                    placeholder="Cari nasi goreng, es teh, bakso..."
                    class="w-full rounded-2xl border border-stone-200 bg-white py-3.5 pl-11 pr-4 text-sm outline-none transition placeholder:text-stone-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100" />
            </div>

            <select id="sortMenu"
                class="rounded-2xl border border-stone-200 bg-white px-4 py-3.5 text-sm outline-none focus:border-orange-400">
                <option value="featured">Rekomendasi</option>
                <option value="low">Harga terendah</option>
                <option value="high">Harga tertinggi</option>
                <option value="name">Nama A–Z</option>
            </select>
        </div>

        <!-- CATEGORY FILTER -->
        @php
            $categoryLabels = [
                'makanan' => '🍛 Makanan',
                'lauk' => '🍗 Lauk',
                'mie' => '🍜 Mi & Bakso',
                'snack' => '🍟 Camilan',
                'minuman' => '🥤 Minuman',
                'hangat' => '☕ Minuman Hangat',
                'dessert' => '🍰 Dessert',
            ];
            $categories = $menus->pluck('category')->unique()->values();
        @endphp

        <div id="categories" class="hide-scrollbar mb-6 flex gap-2 overflow-x-auto pb-2">
            <button class="category-btn active rounded-full border border-orange-200 px-5 py-2.5 text-xs font-bold"
                data-category="all">
                🍽️ Semua
            </button>
            @foreach ($categories as $category)
                <button class="category-btn rounded-full border border-stone-200 bg-white px-5 py-2.5 text-xs font-bold"
                    data-category="{{ $category }}">
                    {{ $categoryLabels[$category] ?? \Illuminate\Support\Str::headline($category) }}
                </button>
            @endforeach
        </div>

        <!-- RESULTS INFO -->
        <div class="mb-4 flex items-center justify-between gap-3">
            <p id="resultCount" class="text-xs font-medium text-stone-500">
                Menampilkan {{ $menus->count() }} menu
            </p>

            <button id="resetFilters" class="text-xs font-bold text-orange-600 hover:underline">
                Reset filter
            </button>
        </div>

        <!-- PRODUCT GRID -->
        <div id="menuGrid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4 xl:grid-cols-5">
            @forelse ($menus as $menu)
                <article class="menu-card overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm"
                    data-name="{{ strtolower($menu->name) }}"
                    data-description="{{ strtolower($menu->description ?? '') }}"
                    data-category="{{ $menu->category }}"
                    data-price="{{ $menu->price }}">
                    <div class="aspect-[4/3] bg-orange-50">
                        @if ($menu->image)
                            <img src="{{ \Illuminate\Support\Str::startsWith($menu->image, ['http://', 'https://']) ? $menu->image : asset('storage/' . ltrim($menu->image, '/')) }}"
                                alt="{{ $menu->name }}" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="grid h-full place-items-center text-5xl" aria-hidden="true">🍽️</div>
                        @endif
                    </div>
                    <div class="p-3 sm:p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-orange-600">{{ $categoryLabels[$menu->category] ?? \Illuminate\Support\Str::headline($menu->category) }}</p>
                        <h3 class="mt-1 font-extrabold text-stone-900">{{ $menu->name }}</h3>
                        @if ($menu->description)
                            <p class="mt-1 line-clamp-2 text-xs text-stone-500">{{ $menu->description }}</p>
                        @endif
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <span class="text-sm font-black text-orange-600">Rp{{ number_format($menu->price, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-stone-400">Stok {{ $menu->stock }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center text-sm text-stone-500">
                    Belum ada menu yang tersedia saat ini.
                </p>
            @endforelse
        </div>

        <!-- EMPTY STATE -->
        <div id="noMenu"
            class="hidden rounded-3xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <div class="text-5xl">🔎</div>
            <h3 class="mt-4 text-lg font-extrabold">Menu tidak ditemukan</h3>
            <p class="mt-2 text-sm text-stone-500">
                Coba kata kunci atau kategori lainnya.
            </p>
            <button id="emptyReset" class="mt-5 rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white">
                Tampilkan semua menu
            </button>
        </div>
    </div>
</section>

<script>
    (() => {
        const grid = document.getElementById('menuGrid');
        const cards = [...grid.querySelectorAll('.menu-card')];
        const search = document.getElementById('menuSearch');
        const sort = document.getElementById('sortMenu');
        const count = document.getElementById('resultCount');
        const empty = document.getElementById('noMenu');
        let category = 'all';

        function updateMenu() {
            const query = search.value.trim().toLocaleLowerCase('id');
            const visible = cards.filter(card => {
                const matchesQuery = `${card.dataset.name} ${card.dataset.description}`.includes(query);
                const matchesCategory = category === 'all' || card.dataset.category === category;
                card.hidden = !(matchesQuery && matchesCategory);
                return !card.hidden;
            });

            visible.sort((a, b) => {
                if (sort.value === 'low') return Number(a.dataset.price) - Number(b.dataset.price);
                if (sort.value === 'high') return Number(b.dataset.price) - Number(a.dataset.price);
                if (sort.value === 'name') return a.dataset.name.localeCompare(b.dataset.name, 'id');
                return 0;
            }).forEach(card => grid.appendChild(card));

            count.textContent = `Menampilkan ${visible.length} menu`;
            empty.classList.toggle('hidden', visible.length > 0);
        }

        document.getElementById('categories').addEventListener('click', event => {
            const button = event.target.closest('.category-btn');
            if (!button) return;
            category = button.dataset.category;
            document.querySelectorAll('.category-btn').forEach(item => {
                item.classList.toggle('active', item === button);
                item.classList.toggle('border-orange-200', item === button);
                item.classList.toggle('text-orange-700', item === button);
                item.classList.toggle('border-stone-200', item !== button);
            });
            updateMenu();
        });

        search.addEventListener('input', updateMenu);
        sort.addEventListener('change', updateMenu);
        document.getElementById('resetFilters').addEventListener('click', () => {
            search.value = '';
            sort.value = 'featured';
            document.querySelector('[data-category="all"]').click();
        });
        document.getElementById('emptyReset').addEventListener('click', () => {
            document.getElementById('resetFilters').click();
        });
        updateMenu();
    })();
</script>
