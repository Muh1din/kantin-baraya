<x-layouts.owner>
    <div class="container-fluid px-4">
        <!-- Header Page -->
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <div>
                <h1 class="h3 text-gray-800">Pusat Laporan (Reports Hub)</h1>
                <p class="text-muted">Kelola dan pantau seluruh laporan keuangan, penjualan, serta pembelian kantin di
                    sini.</p>
            </div>
            <div>
                <a href="{{ route('owner.dashboard') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Grid Komponen Laporan -->
        <div class="row">
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('owner.reports.sales') }}" class="card h-100 p-4 text-decoration-none">
                    <strong class="text-primary">Laporan Penjualan</strong>
                    <span class="mt-2 text-muted">Lihat transaksi lunas dari POS dan pemesanan pelanggan.</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.owner>
