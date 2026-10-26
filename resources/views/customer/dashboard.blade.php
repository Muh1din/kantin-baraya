<x-layouts.customer>
    {{-- Pembungkus utama menggeser konten dari sidebar fixed lg:pl-72 --}}
        {{-- Inner Container: Menghapus mx-auto dan max-w-full agar langsung mengisi area kanan sidebar --}}
        <div class="space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- 1. HEADER SAMBUTAN & SALDO POIN --}}
            <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="mt-1 text-2xl font-black text-stone-900 sm:text-3xl">
                        Halo, {{ auth()->user()->nama_lengkap ?? auth()->user()->name }}
                    </h1>
                    <p class="text-xs font-medium text-stone-500 sm:text-sm">
                        Mau nikmati hidangan lezat apa hari ini?
                    </p>
                </div>

                {{-- POIN LOYALTY CARD --}}
                <a 
                {{-- href="{{ route('customer.loyalty.index') }}" --}}
                    class="group relative flex items-center justify-between gap-4 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 p-4 text-white shadow-lg shadow-orange-500/20 transition hover:shadow-xl hover:shadow-orange-500/30 sm:min-w-[240px]">
                    <div class="flex items-center gap-3">
                        <div
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/20 text-xl font-bold backdrop-blur-md">
                            ⭐
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-orange-100">
                                poin saya
                            </p>
                            <p class="text-lg font-black leading-none">
                                {{ number_format(auth()->user()->loyaltyAccount->saldo_poin ?? 0) }} <span
                                    class="text-xs font-semibold text-orange-100">Pts</span>
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/20 transition group-hover:translate-x-1">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </section>

            {{-- 2. LIVE TRACKING BANNER (PESANAN AKTIF) --}}
            @if (isset($activeOrder) && $activeOrder)
                <section
                    class="overflow-hidden rounded-2xl border border-orange-200 bg-orange-500/5 p-5 backdrop-blur-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <div
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-500 text-white shadow-md shadow-orange-500/30">
                                🍳
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase text-orange-600">Pesanan Aktif</span>
                                    <span class="text-xs text-stone-400">•</span>
                                    <span
                                        class="text-xs font-mono font-bold text-stone-600">#{{ $activeOrder->kode_pesanan }}</span>
                                </div>
                                <p class="text-base font-black text-stone-900">
                                    {{ ucfirst($activeOrder->status_pesanan) }}
                                </p>
                                <p class="text-xs font-medium text-stone-500">
                                    Ditangani oleh Dapur • Estimasi selesai dalam beberapa menit
                                </p>
                            </div>
                        </div>

                        <a 
                        {{-- href="{{ route('customer.orders.show', $activeOrder->token_pelacakan) }}" --}}
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange-500/20 transition hover:bg-orange-600">
                            <span>Lacak Pesanan</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </section>
            @endif

            {{-- 3. MODE PEMESANAN & SCAN QR MEJA --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                {{-- SCAN QR DINE-IN --}}
                <a 
                {{-- href="{{ route('customer.scan-qr') }}" --}}
                    class="group relative flex items-center justify-between overflow-hidden rounded-2xl border border-stone-200 bg-white p-5 transition hover:border-orange-500 hover:shadow-lg hover:shadow-orange-500/5">
                    <div class="space-y-1">
                        <span
                            class="inline-flex rounded-md bg-orange-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-700">
                            Dine-In
                        </span>
                        <h2 class="text-base font-bold text-stone-900 group-hover:text-orange-600">
                            Scan QR Meja
                        </h2>
                        <p class="text-xs font-medium text-stone-500">
                            Pesan langsung dari tempat duduk kamu
                        </p>
                    </div>
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-stone-100 text-stone-600 transition group-hover:bg-orange-500 group-hover:text-white">
                        📱
                    </div>
                </a>

                {{-- TAKEAWAY --}}
                <a 
                {{-- href="{{ route('customer.menus.index', ['mode' => 'takeaway']) }}" --}}
                    class="group relative flex items-center justify-between overflow-hidden rounded-2xl border border-stone-200 bg-white p-5 transition hover:border-orange-500 hover:shadow-lg hover:shadow-orange-500/5">
                    <div class="space-y-1">
                        <span
                            class="inline-flex rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-700">
                            Takeaway
                        </span>
                        <h2 class="text-base font-bold text-stone-900 group-hover:text-orange-600">
                            Bawa Pulang
                        </h2>
                        <p class="text-xs font-medium text-stone-500">
                            Pesan awal & ambil langsung di kasir
                        </p>
                    </div>
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-stone-100 text-stone-600 transition group-hover:bg-orange-500 group-hover:text-white">
                        🛍️
                    </div>
                </a>

                {{-- DELIVERY --}}
                <a 
                {{-- href="{{ route('customer.menus.index', ['mode' => 'delivery']) }}" --}}
                    class="group relative flex items-center justify-between overflow-hidden rounded-2xl border border-stone-200 bg-white p-5 transition hover:border-orange-500 hover:shadow-lg hover:shadow-orange-500/5">
                    <div class="space-y-1">
                        <span
                            class="inline-flex rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-700">
                            Delivery
                        </span>
                        <h2 class="text-base font-bold text-stone-900 group-hover:text-orange-600">
                            Pengiriman
                        </h2>
                        <p class="text-xs font-medium text-stone-500">
                            Antar hidangan ke lokasimu
                        </p>
                    </div>
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-stone-100 text-stone-600 transition group-hover:bg-orange-500 group-hover:text-white">
                        🛵
                    </div>
                </a>
            </section>

            {{-- 4. KATEGORI MENU CEPAT --}}
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-black text-stone-900">
                        Riwayat pesanan
                    </h2>
                    <a 
                    {{-- href="{{ route('customer.menus.index') }}" --}}
                        class="text-xs font-bold text-orange-600 hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div
                    class="flex items-center gap-3 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($categories ?? [] as $category)
                        <a href="{{ route('customer.menus.index', ['kategori' => $category->id_kategori]) }}"
                            class="flex shrink-0 items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-xs font-bold text-stone-700 transition hover:border-orange-500 hover:bg-orange-50 hover:text-orange-700">
                            <span>🍔</span>
                            <span>{{ $category->nama_kategori }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- 5. REKOMENDASI / MENU TERLARIS --}}
            <section class="space-y-3">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @forelse($popularMenus ?? [] as $menu)
                        <div
                            class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-stone-200 bg-white p-3 transition hover:shadow-lg hover:shadow-stone-200/50">
                            {{-- GAMBAR MENU & STATUS --}}
                            <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-stone-100">
                                <div class="grid h-full w-full place-items-center text-4xl">
                                    🍱
                                </div>

                                @if (!$menu->status_tersedia || $menu->jumlah_stok <= 0)
                                    <div
                                        class="absolute inset-0 grid place-items-center bg-stone-950/60 text-xs font-bold text-white backdrop-blur-[1px]">
                                        Habis
                                    </div>
                                @endif
                            </div>

                            {{-- DETAIL MENU --}}
                            <div class="mt-3 flex-1 space-y-1">
                                <h3 class="line-clamp-1 text-sm font-bold text-stone-900 group-hover:text-orange-600">
                                    {{ $menu->nama_menu }}
                                </h3>
                                <p class="line-clamp-2 text-[11px] text-stone-500">
                                    {{ $menu->deskripsi ?? 'Hidangan lezat khas Kantin Baraya' }}
                                </p>
                            </div>

                            {{-- HARGA & TOMBOL --}}
                            <div class="mt-3 flex items-center justify-between border-t border-stone-100 pt-3">
                                <div>
                                    <span class="text-[10px] font-bold text-stone-400">Harga</span>
                                    <p class="text-sm font-black text-stone-900">
                                        Rp{{ number_format($menu->harga_jual, 0, ',', '.') }}
                                    </p>
                                </div>

                                <button type="button" @if (!$menu->status_tersedia || $menu->jumlah_stok <= 0) disabled @endif
                                    class="grid h-8 w-8 place-items-center rounded-xl bg-orange-500 text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full rounded-2xl border border-dashed border-stone-200 bg-white p-8 text-center">
                            <p class="text-xs font-bold text-stone-500">Belum ada menu yang ditampilkan.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
</x-layouts.customer>