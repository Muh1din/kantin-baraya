<x-layouts.owner>
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <div><h1 class="h3 text-gray-800">Laporan Penjualan</h1><p class="text-muted">Satu baris per pesanan dengan pembayaran lunas.</p></div>
            <a href="{{ route('owner.reports.index') }}" class="btn btn-secondary btn-sm">Kembali ke Laporan</a>
        </div>
        <form method="GET" class="row g-3 mb-4">
            <div class="col-auto"><label for="from" class="form-label">Dari tanggal</label><input id="from" class="form-control" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
            <div class="col-auto"><label for="to" class="form-label">Sampai tanggal</label><input id="to" class="form-control" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
            <div class="col-auto align-self-end"><button class="btn btn-primary">Terapkan</button></div>
        </form>
        <div class="row mb-4"><div class="col-md-6"><div class="card p-3"><span>Transaksi lunas</span><strong>{{ $transactionCount }}</strong></div></div><div class="col-md-6"><div class="card p-3"><span>Total penjualan</span><strong>Rp {{ number_format($salesTotal, 0, ',', '.') }}</strong></div></div></div>
        <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Invoice</th><th>Waktu</th><th>Sumber</th><th>Pelanggan</th><th>Status pesanan</th><th>Total</th><th>Pembayaran</th></tr></thead><tbody>
            @forelse ($orders as $order)<tr><td>{{ $order->order_code }}</td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td><td>{{ strtoupper($order->order_channel) }}</td><td>{{ $order->customer_name }}</td><td>{{ str_replace('_', ' ', $order->order_status) }}</td><td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td><td>{{ strtoupper($order->payment->firstWhere('payment_status', 'paid')?->payment_method ?? '-') }}</td></tr>@empty<tr><td colspan="7" class="text-center">Belum ada transaksi lunas.</td></tr>@endforelse
        </tbody></table></div>
    </div>
</x-layouts.owner>
