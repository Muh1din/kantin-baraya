<x-layouts.cashier title="Detail Pesanan">
    @php
        $statusLabels = [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'in_progress' => 'Sedang Diproses',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        $statusColors = [
            'pending' => 'bg-amber-100 text-amber-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-indigo-100 text-indigo-800',
            'completed' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-700',
        ];

        $paymentStatusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'failed' => 'Gagal',
        ];

        $paymentMethodLabels = [
            'tunai' => 'Tunai',
            'qris' => 'QRIS',
            'cash' => 'Tunai',
        ];

        $orderTypeLabels = [
            'bungkus' => 'Bungkus',
            'makan_di_tempat' => 'Makan di Tempat',
            'takeaway' => 'Bungkus',
            'dine_in' => 'Makan di Tempat',
        ];
    @endphp

    <section class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('kasir.orders.index') }}"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    &larr; Kembali ke Daftar Pesanan
                </a>

                <h1 class="mt-3 text-2xl font-bold text-typography-heading">
                    Detail Pesanan
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Kode: {{ $order->order_code }}
                </p>
            </div>

            <span
                class="inline-flex w-fit rounded-full px-4 py-2 text-sm font-semibold {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $statusLabels[$order->order_status] ?? ucfirst(str_replace('_', ' ', $order->order_status)) }}
            </span>
            @if ($order->order_status === 'pending')
                <div class="flex flex-col gap-3 sm:flex-row">
                    <form action="{{ route('kasir.orders.confirm', $order->id) }}" method="POST"
                        onsubmit="return confirm('Konfirmasi pesanan ini dan kurangi stok menu?')">
                        @csrf

                        <button type="submit"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Konfirmasi Pesanan
                        </button>
                    </form>

                    <form action="{{ route('kasir.orders.reject', $order->id) }}" method="POST"
                        class="flex flex-col gap-2 sm:flex-row"
                        onsubmit="return confirm('Yakin ingin menolak pesanan ini?')">
                        @csrf

                        <input type="text" name="reason" value="{{ old('reason') }}" maxlength="1000" required
                            placeholder="Alasan penolakan" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">

                        <button type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            Tolak Pesanan
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Customer and Order Information --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Informasi Pelanggan
                </h2>

                <dl class="mt-5 space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">
                            Nama Pelanggan
                        </dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $order->customer_name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Nomor Telepon
                        </dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $order->customer_phone ?: 'Tidak tersedia' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Jenis Pesanan
                        </dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $orderTypeLabels[$order->order_type] ?? ucfirst(str_replace('_', ' ', $order->order_type)) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-gray-500">
                            Waktu Pemesanan
                        </dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Payment Information --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Informasi Pembayaran
                </h2>

                @forelse ($order->payment as $payment)
                    <div class="{{ $loop->first ? 'mt-5' : 'mt-5 border-t border-gray-100 pt-5' }}">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm text-gray-500">
                                    Metode Pembayaran
                                </dt>
                                <dd class="mt-1 font-medium text-gray-900">
                                    {{ $paymentMethodLabels[strtolower($payment->payment_method)] ?? strtoupper($payment->payment_method) }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm text-gray-500">
                                    Status Pembayaran
                                </dt>
                                <dd class="mt-1 font-medium text-gray-900">
                                    {{ $paymentStatusLabels[$payment->payment_status] ?? ucfirst(str_replace('_', ' ', $payment->payment_status)) }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm text-gray-500">
                                    Jumlah Pembayaran
                                </dt>
                                <dd class="mt-1 font-semibold text-gray-900">
                                    Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}
                                </dd>
                            </div>

                            @if ($payment->transaction_reference)
                                <div>
                                    <dt class="text-sm text-gray-500">
                                        Referensi Transaksi
                                    </dt>
                                    <dd class="mt-1 break-all font-medium text-gray-900">
                                        {{ $payment->transaction_reference }}
                                    </dd>
                                </div>
                            @endif

                            @if ($payment->paid_at)
                                <div>
                                    <dt class="text-sm text-gray-500">
                                        Waktu Pembayaran
                                    </dt>
                                    <dd class="mt-1 font-medium text-gray-900">
                                        {{ $payment->paid_at->format('d/m/Y H:i') }}
                                    </dd>
                                </div>
                            @endif

                            @if ($payment->proof_image)
                                <div>
                                    <dt class="mb-2 text-sm text-gray-500">
                                        Bukti Pembayaran
                                    </dt>

                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->proof_image) }}"
                                        target="_blank" rel="noopener noreferrer" class="inline-block">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->proof_image) }}"
                                            alt="Bukti pembayaran pesanan {{ $order->order_code }}"
                                            class="max-h-72 rounded-lg border border-gray-200 object-contain">

                                        <span class="mt-2 block text-sm font-medium text-indigo-600">
                                            Lihat gambar ukuran penuh
                                        </span>
                                    </a>
                                </div>
                            @endif

                            @if ($payment->payment_status === 'pending' && !in_array($order->order_status, ['rejected', 'cancelled'], true))
                                <div class="border-t border-gray-100 pt-4">
                                    @if (strtolower($payment->payment_method) !== 'qris' || $payment->proof_image)
                                        <form
                                            action="{{ route('kasir.orders.payments.verify', [
                                                'order' => $order->id,
                                                'payment' => $payment->id,
                                            ]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Pastikan pembayaran benar-benar sudah diterima. Lanjutkan verifikasi?')">
                                            @csrf

                                            @if (strtolower($payment->payment_method) === 'tunai')
                                                <label class="mb-3 block text-left text-sm font-medium text-gray-700">Uang diterima (minimal Rp{{ number_format($payment->amount, 0, ',', '.') }})
                                                    <input type="number" name="cash_received" min="{{ (int) $payment->amount }}" step="1" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
                                                </label>
                                            @endif

                                            <button type="submit"
                                                class="w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700">
                                                Verifikasi Pembayaran
                                            </button>
                                        </form>
                                    @else
                                        <p class="text-sm text-amber-700">
                                            Menunggu pelanggan mengunggah bukti pembayaran QRIS.
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </dl>
                    </div>
                @empty
                    <p class="mt-5 text-sm text-gray-500">
                        Belum ada informasi pembayaran.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Order Items --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    Rincian Menu
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Nama Menu</th>
                            <th class="whitespace-nowrap px-6 py-4">Harga Satuan</th>
                            <th class="px-6 py-4">Jumlah</th>
                            <th class="whitespace-nowrap px-6 py-4">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $item->menu_name }}
                                    </div>

                                    @if ($item->notes)
                                        <p class="mt-1 text-xs text-gray-500">
                                            Catatan: {{ $item->notes }}
                                        </p>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-gray-600">
                                    Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ $item->quantity }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                    Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    Tidak ada rincian menu pada pesanan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 bg-gray-50 px-6 py-5">
                <dl class="ml-auto max-w-sm space-y-3">
                    <div class="flex justify-between gap-4 text-sm">
                        <dt class="text-gray-600">Subtotal</dt>
                        <dd class="font-medium text-gray-900">
                            Rp{{ number_format((float) $order->subtotal, 0, ',', '.') }}
                        </dd>
                    </div>

                    <div class="flex justify-between gap-4 text-sm">
                        <dt class="text-gray-600">Diskon</dt>
                        <dd class="font-medium text-gray-900">
                            Rp{{ number_format((float) $order->discount_amount, 0, ',', '.') }}
                        </dd>
                    </div>

                    <div class="flex justify-between gap-4 border-t border-gray-200 pt-3">
                        <dt class="font-semibold text-gray-900">Total Pesanan</dt>
                        <dd class="text-lg font-bold text-indigo-700">
                            Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Customer Notes --}}
        @if ($order->notes)
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Catatan Pelanggan
                </h2>

                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600">{{ $order->notes }}</p>
            </div>
        @endif

        {{-- Order Status History --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">
                Riwayat Status Pesanan
            </h2>
            
            @forelse ($order->statusHistories as $history)
                <div class="border-l-2 border-indigo-200 py-2 pl-4 {{ $loop->first ? 'mt-5' : 'mt-3' }}">
                    <p class="font-medium text-gray-900">
                        {{ $statusLabels[$history->new_status] ?? ucfirst(str_replace('_', ' ', $history->new_status)) }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        {{ $history->created_at?->format('d/m/Y H:i') ?? '-' }}
                        &middot;
                        {{ $history->changedBy?->name ?? 'Sistem' }}
                    </p>

                    @if ($history->notes)
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $history->notes }}
                        </p>
                    @endif
                </div>
            @empty
                <p class="mt-4 text-sm text-gray-500">
                    Belum ada riwayat perubahan status.
                </p>
            @endforelse
        </div>
    </section>
</x-layouts.cashier>
