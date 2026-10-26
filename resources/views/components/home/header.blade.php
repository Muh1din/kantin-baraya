<header class="sticky top-0 z-40 border-b border-orange-100 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <!-- Logo -->
        <a href="#" class="flex shrink-0 items-center gap-3">
            <div>
                <h1 class="text-lg font-black tracking-tight">
                    Kantin <span class="text-orange-500">Baraya.</span>
                </h1>
                <p class="text-[10px] font-medium tracking-widest text-stone-500">
                    MAKAN ENAK, HATI SENANG
                </p>
            </div>
        </a>

        <!-- Tombol Aksi -->
        <div class="flex shrink-0 items-center gap-2">
            <!-- TOMBOL NOTIFIKASI -->
            <button type="button" onclick="openNotifications()"
                class="relative rounded-xl border border-gray-200 bg-white p-3 hover:bg-orange-50 transition"
                aria-label="Notifikasi">
                <!-- Ikon lonceng -->
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                    <path d="M10 21h4" />
                </svg>

                <!-- Badge jumlah notifikasi -->
                <span id="notificationBadge"
                    class="absolute -right-1 -top-1 hidden min-w-[18px] rounded-full bg-red-500 px-1 text-center text-[10px] font-bold text-white">
                    0
                </span>
            </button>

            <!-- Pesanan Saya -->
            <a href="tracking-pesanan.html"
                class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-3 text-sm font-bold text-stone-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-600 sm:px-4"
                aria-label="Lihat pesanan saya">
                <span class="text-lg">📋</span>
                <span class="hidden sm:inline">Lihat pesanan saya</span>
            </a>

            <!-- Keranjang -->
            <button id="cartButton" type="button"
                class="relative flex shrink-0 items-center gap-2 rounded-xl bg-orange-500 px-3 py-3 text-sm font-bold text-white transition hover:bg-orange-600 sm:px-4"
                aria-label="Buka keranjang">
                <span class="text-lg">🛒</span>
                <span class="hidden sm:inline">Keranjang</span>

                <span id="cartCount"
                    class="grid h-5 min-w-5 place-items-center rounded-full bg-white px-1 text-[10px] font-black text-orange-600">
                    0
                </span>
            </button>
        </div>
    </div>
</header>
