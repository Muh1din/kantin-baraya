<x-layouts.cashier title="Pesanan Berjalan">

    <div class="space-y-6">

        {{-- Page Heading --}}
        <div>
            <p class="mt-1 text-sm text-gray-500">
                Pantau pesanan yang sedang menunggu diproses dan sedang dimasak oleh koki.
            </p>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Menunggu Diproses
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-amber-600">
                            {{ $orders->where('order_status', 'confirmed')->count() }}
                        </h2>
                    </div>

                    <div class="rounded-lg bg-amber-50 p-3 text-amber-600">
                        {{-- Icon --}}
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Sedang Dimasak
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-orange-600">
                            {{ $orders->where('order_status', 'in_progress')->count() }}
                        </h2>
                    </div>

                    <div class="rounded-lg bg-orange-50 p-3 text-orange-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V3m0 3a6 6 0 016 6v1H6v-1a6 6 0 016-6zm-6 6v8m12-8v8M6 20h12" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- Order List --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div
                class="flex flex-col gap-3 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-lg font-semibold text-typography-heading">
                        Daftar Pesanan
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Total {{ $orders->total() }} pesanan ditemukan.
                    </p>
                </div>

            </div>

            @if ($orders->isNotEmpty())

                <div class="overflow-x-auto">

                    <table class="w-full whitespace-nowrap text-left text-sm">

                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-5 py-4 font-semibold">
                                    Kode Pesanan
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Pelanggan
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Menu
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Total
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Waktu Pesanan
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Status
                                </th>

                                <th class="px-5 py-4 text-right font-semibold">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            @foreach ($orders as $order)
                                <tr class="transition hover:bg-gray-50">

                                    {{-- Order Code --}}
                                    <td class="px-5 py-4">
                                        <span class="font-semibold text-typography-heading">
                                            {{ $order->order_code }}
                                        </span>
                                    </td>

                                    {{-- Customer --}}
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-800">
                                            {{ $order->customer_name }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $order->customer_phone }}
                                        </div>
                                    </td>

                                    {{-- Menu --}}
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            @foreach ($order->items as $item)
                                                <div class="text-gray-700">
                                                    {{ $item->menu_name }}
                                                    <span class="text-gray-400">
                                                        × {{ $item->quantity }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    {{-- Total --}}
                                    <td class="px-5 py-4 font-semibold text-gray-800">
                                        Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>

                                    {{-- Created At --}}
                                    <td class="px-5 py-4 text-gray-600">
                                        <div>
                                            {{ $order->created_at->format('d M Y') }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-400">
                                            {{ $order->created_at->format('H:i') }} WIB
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-5 py-4">

                                        @if ($order->order_status === 'confirmed')
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Menunggu Diproses
                                            </span>
                                        @elseif ($order->order_status === 'in_progress')
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                                Sedang Dimasak
                                            </span>
                                        @endif

                                    </td>

                                    {{-- Action --}}
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('kasir.orders.show', $order->id) }}"
                                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">

                                            Detail

                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $orders->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">

                    <div class="mb-4 rounded-full bg-gray-100 p-5">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800">
                        Belum Ada Pesanan Berjalan
                    </h3>

                    <p class="mt-2 max-w-sm text-sm text-gray-500">
                        Saat ini belum ada pesanan yang menunggu diproses atau sedang dimasak oleh koki.
                    </p>

                </div>

            @endif

        </div>

    </div>

</x-layouts.cashier>
