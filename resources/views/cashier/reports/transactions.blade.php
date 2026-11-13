<x-layouts.cashier title="Laporan Transaksi">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Laporan Transaksi</h1>
            <p class="mt-1 text-sm text-stone-500">Daftar pesanan dan pembayaran yang tersimpan. Setiap pesanan ditampilkan satu kali.</p>
        </header>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
            <form action="{{ route('kasir.reports.transactions') }}" method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto_auto] lg:items-end">
                <div>
                    <label for="start_date" class="mb-2 block text-sm font-semibold text-stone-700">Dari tanggal</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $filters['start_date'] }}" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="end_date" class="mb-2 block text-sm font-semibold text-stone-700">Sampai tanggal</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $filters['end_date'] }}" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="status" class="mb-2 block text-sm font-semibold text-stone-700">Status Transaksi</label>
                    <select id="status" name="status" class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ['pending' => 'Menunggu Konfirmasi', 'confirmed' => 'Dikonfirmasi', 'rejected' => 'Ditolak', 'in_progress' => 'Sedang Dimasak', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'][$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Terapkan</button>
                <a href="{{ route('kasir.reports.transactions') }}" class="rounded-xl border border-stone-300 px-5 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Reset</a>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-extrabold text-stone-800">Daftar Transaksi</h2>
                    <p class="mt-1 text-xs text-stone-500">{{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</p>
                </div>
                <span class="text-sm text-stone-500">{{ number_format($orders->total(), 0, ',', '.') }} transaksi</span>
            </div>

            @if ($orders->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Tidak ada transaksi yang sesuai dengan filter.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Kode Pesanan</th>
                                <th scope="col" class="px-5 py-3.5">Pelanggan</th>
                                <th scope="col" class="px-5 py-3.5">Tanggal</th>
                                <th scope="col" class="px-5 py-3.5">Total Pembayaran</th>
                                <th scope="col" class="px-5 py-3.5">Metode Pembayaran</th>
                                <th scope="col" class="px-5 py-3.5">Status Transaksi</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($orders as $order)
                                @php
                                    $payment = $order->payment->first();
                                    $paymentStatusLabels = ['pending' => 'Menunggu pembayaran', 'paid' => 'Berhasil', 'failed' => 'Gagal'];
                                    $orderStatusLabels = ['pending' => 'Menunggu Konfirmasi', 'confirmed' => 'Dikonfirmasi', 'rejected' => 'Ditolak', 'in_progress' => 'Sedang Dimasak', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
                                @endphp
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="whitespace-nowrap px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $order->order_code }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-stone-800">{{ $order->customer_name }}</p>
                                        @if ($order->customer_phone)<p class="mt-1 text-xs text-stone-500">{{ $order->customer_phone }}</p>@endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-stone-600">{{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-900">Rp{{ number_format((float) ($payment?->amount ?? $order->total_amount), 0, ',', '.') }}</td>
                                    <td class="px-5 py-4">
                                        @if ($payment)
                                            <p class="font-semibold text-stone-800">{{ strtoupper($payment->payment_method) }}</p>
                                            <p class="mt-1 text-xs {{ $payment->payment_status === 'paid' ? 'text-green-700' : ($payment->payment_status === 'failed' ? 'text-red-700' : 'text-amber-700') }}">{{ $paymentStatusLabels[$payment->payment_status] ?? ucfirst(str_replace('_', ' ', $payment->payment_status)) }}</p>
                                        @else<span class="text-stone-500">Belum tersedia</span>@endif
                                    </td>
                                    <td class="px-5 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ match ($order->order_status) {'pending' => 'bg-amber-100 text-amber-800', 'confirmed' => 'bg-blue-100 text-blue-800', 'in_progress' => 'bg-orange-100 text-orange-800', 'completed' => 'bg-green-100 text-green-800', 'rejected', 'cancelled' => 'bg-red-100 text-red-800', default => 'bg-stone-100 text-stone-700'} }}">{{ $orderStatusLabels[$order->order_status] ?? ucfirst(str_replace('_', ' ', $order->order_status)) }}</span></td>
                                    <td class="px-5 py-4 sm:px-6">
                                        @if ($order->order_channel === 'online')
                                            <a href="{{ route('kasir.orders.show', $order) }}" class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Lihat</a>
                                        @elseif ($order->order_channel === 'pos' && (int) $order->created_by === (int) auth()->id())
                                            <a href="{{ route('kasir.pos.receipt', $order) }}" class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Lihat</a>
                                        @else
                                            <span class="text-stone-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($orders->hasPages())<div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $orders->links() }}</div>@endif
            @endif
        </section>
    </div>
</x-layouts.cashier>
