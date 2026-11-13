<x-layouts.customer title="Pesanan Saya">

    <section class="space-y-6 px-4 pb-12 sm:px-6 lg:px-8">

        {{-- HEADER --}}
        <div>
            <p class="text-xs font-black tracking-[.2em] text-orange-500">
                RIWAYAT TRANSAKSI
            </p>
            
            <p class="mt-2 text-sm text-stone-500">
                Pantau dan lihat riwayat pesananmu di Kantin Baraya.
            </p>
        </div>

        {{-- ORDER LIST --}}
        <div class="space-y-4">

            @forelse ($orders as $order)

                <article class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm">

                    <div class="flex flex-wrap items-start justify-between gap-3">

                        <div>
                            <p class="text-xs font-semibold text-stone-400">
                                KODE PESANAN
                            </p>

                            <h2 class="mt-1 font-black text-stone-800">
                                {{ $order->order_code }}
                            </h2>

                            <p class="mt-1 text-xs text-stone-500">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>

                        @php
                            $status = strtolower($order->order_status);

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

                        <span
                            class="rounded-full px-3 py-1.5 text-xs font-bold {{ $statusColors[$status] ?? 'bg-stone-100 text-stone-600' }}">
                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                        </span>

                    </div>

                    <div class="my-4 border-t border-stone-100"></div>

                    {{-- ORDER ITEMS --}}
                    <div class="space-y-3">

                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between gap-3">

                                <div>
                                    <p class="text-sm font-bold text-stone-700">
                                        {{ $item->menu_name }}
                                    </p>

                                    <p class="text-xs text-stone-400">
                                        {{ $item->quantity }} ×
                                        Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                    </p>
                                </div>

                                <p class="text-sm font-bold text-stone-700">
                                    Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                </p>

                            </div>
                        @endforeach

                    </div>

                    <div class="my-4 border-t border-stone-100"></div>

                    {{-- TOTAL --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-stone-500">
                            Total Pembayaran
                        </span>

                        <span class="text-lg font-black text-orange-600">
                            Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- PAYMENT --}}
                    @foreach ($order->payment as $payment)
                        <div class="mt-3 flex justify-between text-xs text-stone-500">
                            <span>
                                Pembayaran {{ strtoupper($payment->payment_method) }}
                            </span>

                            <span class="font-bold">
                                {{ ucfirst($payment->payment_status) }}
                            </span>
                        </div>
                    @endforeach

                </article>

            @empty

                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-14 text-center">

                    <div class="text-5xl">🧾</div>

                    <h3 class="mt-4 text-lg font-black text-stone-800">
                        Belum Ada Pesanan
                    </h3>

                    <p class="mt-2 text-sm text-stone-500">
                        Pesanan yang kamu buat akan muncul di halaman ini.
                    </p>

                    <a href="{{ route('customer.menus.index') }}"
                        class="mt-6 inline-flex rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">
                        Lihat Katalog Menu
                    </a>

                </div>

            @endforelse

        </div>

        {{-- PAGINATION --}}
        @if ($orders->hasPages())
            <div>
                {{ $orders->links() }}
            </div>
        @endif

    </section>

</x-layouts.customer>
