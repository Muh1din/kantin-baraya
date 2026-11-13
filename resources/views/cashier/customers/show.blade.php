<x-layouts.cashier title="Riwayat Transaksi Pelanggan">
    <div class="space-y-6">
        <header>
            <a href="{{ route('kasir.customers.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 hover:underline">&larr; Kembali ke Data Pelanggan</a>
            <h1 class="mt-3 text-xl font-black text-stone-900">Riwayat Transaksi Pelanggan</h1>
        </header>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Informasi Pelanggan</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-sm text-stone-500">Nama</dt><dd class="mt-1 font-semibold text-stone-900">{{ $customer->name }}</dd></div>
                <div><dt class="text-sm text-stone-500">Email</dt><dd class="mt-1 font-semibold text-stone-900">{{ $customer->email }}</dd></div>
                <div><dt class="text-sm text-stone-500">Nomor telepon dari transaksi terakhir</dt><dd class="mt-1 font-semibold text-stone-900">{{ $phone ?: 'Tidak tersedia' }}</dd></div>
                <div><dt class="text-sm text-stone-500">Akun dibuat</dt><dd class="mt-1 font-semibold text-stone-900">{{ $customer->created_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div><dt class="text-sm text-stone-500">Jumlah transaksi tersimpan</dt><dd class="mt-1 font-semibold text-stone-900">{{ number_format($orders->total(), 0, ',', '.') }}</dd></div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Riwayat Transaksi</h2>
                <p class="mt-1 text-sm text-stone-500">Menampilkan pesanan yang terhubung ke akun pelanggan ini.</p>
            </div>
            @if ($orders->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Pelanggan ini belum memiliki transaksi yang terhubung ke akunnya.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr><th scope="col" class="px-5 py-3.5 sm:px-6">Kode Pesanan</th><th scope="col" class="px-5 py-3.5">Waktu</th><th scope="col" class="px-5 py-3.5">Total</th><th scope="col" class="px-5 py-3.5">Status</th><th scope="col" class="px-5 py-3.5">Pembayaran</th><th scope="col" class="px-5 py-3.5 sm:px-6">Telepon saat memesan</th></tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($orders as $order)
                                @php($payment = $order->payment->first())
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="whitespace-nowrap px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $order->order_code }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-stone-600">{{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-800">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4"><span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">{{ ['pending' => 'Menunggu Konfirmasi', 'confirmed' => 'Dikonfirmasi', 'rejected' => 'Ditolak', 'in_progress' => 'Sedang Dimasak', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'][$order->order_status] ?? ucfirst(str_replace('_', ' ', $order->order_status)) }}</span></td>
                                    <td class="px-5 py-4 text-stone-600">@if ($payment){{ strtoupper($payment->payment_method) }} · {{ match ($payment->payment_status) {'pending' => 'Menunggu pembayaran', 'paid' => 'Sudah dibayar', 'failed' => 'Pembayaran gagal', default => ucfirst(str_replace('_', ' ', $payment->payment_status))} }}@else Belum tersedia @endif</td>
                                    <td class="px-5 py-4 text-stone-600 sm:px-6">{{ $order->customer_phone ?: 'Tidak tersedia' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($orders->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $orders->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.cashier>
