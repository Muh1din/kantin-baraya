<x-layouts.home>
    <x-home.hero />
    <x-home.info-strip />

    <section id="menu" class="px-4 pb-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">

            {{-- HEADER --}}
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

                <div class="rounded-xl bg-orange-50 px-4 py-2 text-xs font-bold text-orange-700">
                    Katalog Kantin Baraya
                </div>
            </div>

            @if ($menus->isEmpty())
                {{-- EMPTY STATE --}}
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-12 text-center">
                    <p class="text-lg font-bold text-stone-800">
                        Belum ada menu tersedia
                    </p>

                    <p class="mt-2 text-sm text-stone-500">
                        Silakan cek kembali nanti.
                    </p>
                </div>
            @else
                {{-- SEARCH & SORT --}}
                <div class="mb-5 flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg text-stone-400">
                            ⌕
                        </span>

                        <input id="menuSearch" type="search" autocomplete="off"
                            placeholder="Cari nasi goreng, es teh, bakso..."
                            class="w-full rounded-2xl border border-stone-200 bg-white py-3.5 pl-11 pr-4 text-sm outline-none transition placeholder:text-stone-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>

                    <select id="sortMenu"
                        class="rounded-2xl border border-stone-200 bg-white px-4 py-3.5 text-sm outline-none focus:border-orange-400">
                        <option value="featured">Rekomendasi</option>
                        <option value="low">Harga terendah</option>
                        <option value="high">Harga tertinggi</option>
                        <option value="name">Nama A–Z</option>
                    </select>
                </div>

                {{-- CATEGORY FILTER --}}
                <div id="categories" class="hide-scrollbar flex gap-2 overflow-x-auto">

                    {{-- SEMUA KATEGORI --}}
                    <button type="button" aria-pressed="true"
                        class="category-btn active shrink-0 rounded-full border border-orange-200 px-5 py-2.5 text-xs font-bold"
                        data-category="all">
                        Semua
                    </button>

                    {{-- KATEGORI DARI DATABASE --}}
                    @foreach ($categories as $category)
                        <button type="button" aria-pressed="false"
                            class="category-btn shrink-0 rounded-full border border-stone-200 bg-white px-5 py-2.5 text-xs font-bold"
                            data-category="{{ \Illuminate\Support\Str::slug($category->name) }}">
                            {{ $category->name }}
                        </button>
                    @endforeach

                </div>

                {{-- EMPTY SEARCH RESULT --}}
                <div id="noMenu"
                    class="hidden rounded-3xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                    <div class="text-5xl">🔎</div>

                    <h3 class="mt-4 text-lg font-extrabold">
                        Menu tidak ditemukan
                    </h3>

                    <p class="mt-2 text-sm text-stone-500">
                        Coba kata kunci atau kategori lainnya.
                    </p>
                </div>

                {{-- SERVER-RENDERED MENU GRID --}}
                <div
                    class="mt-6 grid grid-cols-2 items-stretch gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach ($menus as $menu)
                        @php
                            $cart = session('cart', []);
                            $inCart = $cart[$menu->id]['quantity'] ?? 0;
                            $category = $menu->menuCategory->name ?? ($menu->category ?? 'Umum');
                            $image = $menu->image ? asset('storage/' . $menu->image) : null;
                        @endphp

                        <article
                            class="product-card flex h-[410px] min-w-0 flex-col overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg sm:rounded-3xl"
                            data-product-id="{{ $menu->id }}"
                            data-category="{{ \Illuminate\Support\Str::slug($category) }}"
                            data-menu-name="{{ $menu->name }}" data-price="{{ $menu->price }}"
                            data-image="{{ $image }}" data-stock="{{ $menu->stock }}">

                            {{-- THUMBNAIL --}}
                            <button type="button" data-action="detail" data-id="{{ $menu->id }}"
                                class="relative block h-44 w-full shrink-0 overflow-hidden bg-stone-100 text-left">
                                @if ($image)
                                    <img src="{{ $image }}" alt="{{ $menu->name }}" loading="lazy"
                                        class="h-full w-full object-cover transition duration-300 hover:scale-105"
                                        onerror="this.onerror=null;this.style.display='none';this.parentElement.classList.add('grid','place-items-center');this.insertAdjacentHTML('beforeend','<span class="text-5xl">🍽️</span>')">
                                @else
                                    <div class="grid h-full w-full place-items-center bg-stone-100">
                                        <span class="text-5xl">🍽️</span>
                                    </div>
                                @endif

                                {{-- BADGE --}}
                                @if (!$menu->is_available || $menu->stock <= 0)
                                    <span
                                        class="absolute left-2 top-2 rounded-lg bg-stone-900/80 px-2 py-1 text-[9px] font-black text-white shadow backdrop-blur-sm">
                                        Stok Habis
                                    </span>
                                @elseif (!empty($menu->badge))
                                    <span
                                        class="absolute left-2 top-2 rounded-lg bg-orange-500 px-2 py-1 text-[9px] font-black text-white shadow">
                                        {{ $menu->badge }}
                                    </span>
                                @endif

                            </button>

                            {{-- CONTENT BODY --}}
                            <div class="flex min-h-0 flex-1 flex-col p-3">
                                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-stone-400">
                                    {{ $category }}
                                </p>

                                <button type="button" data-action="detail" data-id="{{ $menu->id }}"
                                    class="mt-1 line-clamp-2 min-h-10 w-full text-left text-sm font-extrabold leading-5 text-stone-800 hover:text-orange-600">
                                    {{ $menu->name }}
                                </button>

                                <p class="mt-1 line-clamp-2 min-h-8 text-[10px] leading-4 text-stone-500 sm:text-xs">
                                    {{ $menu->description ?? 'Hidangan lezat dan segar khas Kantin Baraya.' }}
                                </p>

                                {{-- PRICE --}}
                                <div class="mt-2 border-t border-stone-100 pt-3">
                                    <span class="block text-sm font-black text-orange-600 sm:text-base">
                                        Rp{{ number_format($menu->price, 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- ADD TO CART --}}
                                <div class="mt-5 pb-1">
                                    @if ($menu->is_available && $menu->stock > 0)
                                        <button type="button" data-action="add" data-id="{{ $menu->id }}"
                                            class="add-to-cart-button w-full rounded-xl {{ $inCart > 0 ? 'bg-orange-50 text-orange-600' : 'bg-orange-500 text-white' }} px-2 py-2.5 text-[10px] font-extrabold transition duration-200 hover:bg-orange-600 hover:text-white active:scale-95 sm:text-xs">
                                            @if ($inCart > 0)
                                                ✓ Di keranjang ({{ $inCart }})
                                            @else
                                                + Tambah ke Keranjang
                                            @endif
                                        </button>
                                    @else
                                        <button type="button" disabled
                                            class="w-full cursor-not-allowed rounded-xl bg-stone-100 px-2 py-2.5 text-[10px] font-bold text-stone-400 sm:text-xs">
                                            Tidak Tersedia
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-home.card-drawer />
</x-layouts.home>
