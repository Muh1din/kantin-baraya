<x-layouts.cashier title="Pengelolaan Pesanan">
    @php
        $statusLabels = [
            'pending' => 'Menunggu',
            'completed' => 'Selesai',
            'confirmed' => 'Dikonfirmasi',
            'in_progress' => 'Diproses',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        $statusColors = [
            'pending' => 'bg-amber-100 text-amber-800',
            'completed' => 'bg-green-100 text-green-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-indigo-100 text-indigo-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-700',
        ];
    @endphp

    <section class="space-y-6">
        {{-- Page Heading --}}
        <div>
            <h1 class="text-2xl font-bold text-typography-heading">
                Pengelolaan Pesanan
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Pantau dan cari pesanan pelanggan yang masuk.
            </p>
        </div>

        {{-- Search and Filter --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <form action="{{ route('kasir.orders.index') }}" method="GET" class="flex flex-col gap-3 md:flex-row">
                <div class="flex-1">
                    <label for="search" class="sr-only">
                        Cari pesanan
                    </label>

                    <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari kode atau nama pelanggan..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>

                <div class="md:w-52">
                    <label for="status" class="sr-only">
                        Filter status
                    </label>

                    <select id="status" name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">Semua Status</option>

                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Cari
                </button>

                @if (!empty($filters['search']) || !empty($filters['status']))
                    <a href="{{ route('kasir.orders.index') }}"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Orders Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-typography-heading">
                    Daftar Pesanan
                </h2>

                <span class="text-sm text-gray-500">
                    {{ $orders->total() }} pesanan
                </span>
                
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-4">
                                Kode Pesanan
                            </th>
                            <th class="whitespace-nowrap px-5 py-4">
                                Pelanggan
                            </th>
                            <th class="whitespace-nowrap px-5 py-4">
                                Jenis
                            </th>
                            <th class="whitespace-nowrap px-5 py-4">
                                Total
                            </th>
                            <th class="whitespace-nowrap px-5 py-4">
                                Pembayaran
                            </th>
                            <th class="whitespace-nowrap px-5 py-4">
                                Status
                            </th>
                            
                            <th class="whitespace-nowrap px-5 py-4">
                                Waktu
                            </th>
                            <th class="whitespace-nowrap px-5 py-4">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($orders as $order)
                            @php
                                $payment = $order->payment->first();
                                $status = $order->order_status;
                                $paymentStatus = $payment?->payment_status;
                            @endphp

                            <tr class="transition hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="font-semibold text-indigo-700">
                                        {{ $order->order_code }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $order->customer_name }}
                                    </div>

                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $order->customer_phone ?: 'Nomor telepon tidak tersedia' }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                    @switch($order->order_type)
                                        @case('makan_di_tempat')
                                            Makan di tempat
                                        @break

                                        @case('bungkus')
                                            Bungkus
                                        @break

                                        @default
                                            {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                                    @endswitch
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900">
                                    Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="text-gray-700">
                                        {{ $payment?->payment_method ? strtoupper($payment->payment_method) : 'Belum tersedia' }}
                                    </div>

                                    @if ($paymentStatus)
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ match ($paymentStatus) {
                                                'pending' => 'Menunggu pembayaran',
                                                'paid' => 'Sudah dibayar',
                                                'failed' => 'Pembayaran gagal',
                                                default => ucfirst(str_replace('_', ' ', $paymentStatus)),
                                            } }}
                                        </div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-500">
                                    {{ $order->created_at?->format('d/m/Y H:i') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <a href="{{ route('kasir.orders.show', $order->id) }}"
                                        class="inline-flex items-center rounded-lg border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-50">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-14 text-center">
                                        <p class="font-medium text-gray-700">
                                            Belum ada pesanan
                                        </p>

                                        <p class="mt-1 text-sm text-gray-500">
                                            Pesanan pelanggan akan tampil di sini.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($orders->hasPages())
                    <div class="border-t border-gray-200 px-5 py-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </section>
    </x-layouts.cashier>
