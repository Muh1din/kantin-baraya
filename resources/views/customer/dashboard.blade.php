<x-layouts.customer title="Beranda">

    @php
        $user = auth()->user();

        $customerName = $user->nama_lengkap ?? ($user->name ?? 'Pelanggan');
        $loyaltyPoints = optional($user->loyaltyAccount)->saldo_poin ?? 0;

        $totalOrders = $totalOrders ?? 0;
        $activeOrderCount = $activeOrderCount ?? 0;
        $completedOrderCount = $completedOrderCount ?? 0;

        $recentOrders = $recentOrders ?? collect();

        $statusLabels = [
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Sedang Diproses',
            'ready' => 'Siap Diambil',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $statusColors = [
            'pending' => 'bg-amber-100 text-amber-700',
            'confirmed' => 'bg-blue-100 text-blue-700',
            'processing' => 'bg-orange-100 text-orange-700',
            'ready' => 'bg-emerald-100 text-emerald-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="space-y-8 px-4 pb-12 sm:px-6 lg:px-8">

        {{-- WELCOME SECTION --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white shadow-lg sm:p-8">
            <div class="relative z-10 max-w-xl">
                <p class="text-sm font-semibold text-orange-100">
                    SELAMAT DATANG DI KANTIN BARAYA
                </p>

                <h1 class="mt-3 text-2xl font-black sm:text-3xl">
                    Halo, {{ $customerName }}!
                </h1>

                <p class="mt-3 text-sm leading-relaxed text-orange-50 sm:text-base">
                    Senang melihatmu kembali. Yuk, nikmati kemudahan memesan makanan
                    favoritmu melalui Kantin Baraya.
                </p>

                <a href="{{ route('customer.menus.index') }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-orange-600 shadow-sm transition hover:bg-orange-50">
                    Mulai Pesan
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <div class="pointer-events-none absolute -right-10 -top-10 h-52 w-52 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-20 right-28 h-56 w-56 rounded-full bg-white/10"></div>
        </section>


        {{-- ORDER SUMMARY --}}
        <section>
            <div class="mb-4">
                <h2 class="text-lg font-black text-stone-800">
                    Ringkasan Pesanan
                </h2>
                <p class="mt-1 text-sm text-stone-500">
                    Lihat aktivitas pemesananmu di Kantin Baraya.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                {{-- TOTAL ORDERS --}}
                <div class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-stone-500">
                                Total Pesanan
                            </p>

                            <h3 class="mt-2 text-3xl font-black text-stone-800">
                                {{ number_format($totalOrders) }}
                            </h3>
                        </div>

                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-orange-50 text-orange-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- ACTIVE ORDERS --}}
                <div class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-stone-500">
                                Pesanan Aktif
                            </p>

                            <h3 class="mt-2 text-3xl font-black text-orange-600">
                                {{ number_format($activeOrderCount) }}
                            </h3>
                        </div>

                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- COMPLETED ORDERS --}}
                <div class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-stone-500">
                                Pesanan Selesai
                            </p>

                            <h3 class="mt-2 text-3xl font-black text-emerald-600">
                                {{ number_format($completedOrderCount) }}
                            </h3>
                        </div>

                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-emerald-50 text-emerald-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </section>


        {{-- LOYALTY POINTS --}}
        <section class="overflow-hidden rounded-2xl border border-orange-100 bg-orange-50 p-5 sm:p-6">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <p class="text-sm font-bold text-orange-600">
                        BARAYA REWARDS
                    </p>

                    <h2 class="mt-2 text-xl font-black text-stone-800">
                        Poin Loyalitas Kamu
                    </h2>

                    <p class="mt-1 text-sm text-stone-500">
                        Dapatkan poin untuk setiap menu yang dibeli. Jumlah poin per menu ditentukan oleh owner dan diberikan setelah pembayaran terverifikasi.
                    </p>
                </div>

                <div class="flex items-center gap-3 rounded-xl bg-white px-5 py-4 shadow-sm">
                    <div class="grid h-11 w-11 place-items-center rounded-full bg-orange-100 text-xl">
                        ⭐
                    </div>

                    <div>
                        <p class="text-xs font-medium text-stone-500">
                            Saldo Poin
                        </p>

                        <p class="text-2xl font-black text-orange-600">
                            {{ number_format($loyaltyPoints) }}
                        </p>
                    </div>

                    <a href="{{ route('customer.loyalty.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-orange-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-orange-600">
                        Lihat Reward
                    </a>
                </div>
            </div>
        </section>


        {{-- RECENT ORDERS --}}
        <section>
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-black text-stone-800">
                        Pesanan Terbaru
                    </h2>

                    <p class="mt-1 text-sm text-stone-500">
                        Pantau perkembangan pesanan terakhirmu.
                    </p>
                </div>

                @if (Route::has('customer.orders.index'))
                    <a href="{{ route('customer.orders.index') }}"
                        class="shrink-0 text-xs font-bold text-orange-600 hover:text-orange-700">
                        Lihat Semua →
                    </a>
                @endif
            </div>

            <div class="overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm">

                @forelse ($recentOrders as $order)
                    @php
                        $status = strtolower($order->order_status ?? 'pending');

                        $pickupSchedule = null;

                        if (
                            preg_match(
                                '/Jadwal preorder: (\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})/',
                                $order->notes ?? '',
                                $matches,
                            )
                        ) {
                            $pickupSchedule = \Carbon\Carbon::createFromFormat(
                                'Y-m-d H:i',
                                $matches[1] . ' ' . $matches[2],
                            );
                        }
                    @endphp

                    <div
                        class="flex flex-col gap-3 border-b border-stone-100 p-5 last:border-0 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-black text-stone-800">
                                {{ $order->order_code }}
                            </p>

                            <p class="mt-1 text-xs text-stone-500">
                                {{ $order->created_at?->format('d M Y, H:i') ?? '-' }}
                            </p>

                            <p class="mt-2 text-sm font-bold text-stone-700">
                                Rp{{ number_format($order->total_amount ?? 0, 0, ',', '.') }}
                            </p>

                            <p class="mt-1 text-xs text-stone-500">
                                Dibuat: {{ $order->created_at?->format('d M Y, H:i') ?? '-' }}
                            </p>

                            <div class="mt-2 flex items-center gap-2 text-sm">

                                <div>
                                    <p class="text-xs text-stone-500">Jadwal Pengambilan</p>

                                    <p class="font-bold text-orange-600">
                                        {{ $pickupSchedule?->translatedFormat('d F Y, H:i') ?? 'Jadwal tidak tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 sm:justify-end">
                            <span
                                class="rounded-full px-3 py-1.5 text-xs font-bold {{ $statusColors[$status] ?? 'bg-stone-100 text-stone-600' }}">
                                {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                            </span>

                            @if (Route::has('customer.orders.index'))
                                <a href="{{ route('customer.orders.index') }}"
                                    class="text-xs font-bold text-orange-600 hover:underline">
                                    Detail
                                </a>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <div class="text-4xl">🧾</div>

                        <h3 class="mt-3 font-bold text-stone-800">
                            Belum Ada Pesanan
                        </h3>

                        <p class="mt-1 text-sm text-stone-500">
                            Pesanan yang kamu buat akan muncul di sini.
                        </p>

                        <a href="{{ route('customer.menus.index') }}"
                            class="mt-5 inline-flex rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">
                            Lihat Katalog Menu
                        </a>
                    </div>
                @endforelse

            </div>
        </section>


        {{-- INFORMATION --}}
        <section class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start gap-4">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" />
                    </svg>
                </div>

                <div>
                    <h3 class="font-bold text-stone-800">
                        Informasi Kantin Baraya
                    </h3>

                    <p class="mt-1 text-sm leading-relaxed text-stone-500">
                        Pastikan kamu memeriksa status pesanan secara berkala.
                        Pesanan dapat diambil setelah status berubah menjadi
                        <span class="font-bold text-emerald-600">Siap Diambil</span>.
                    </p>
                </div>
            </div>
        </section>

    </div>

</x-layouts.customer>
