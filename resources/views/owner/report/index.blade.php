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
        {{-- <div class="row">
            <!-- Komponen Laporan Penjualan -->
            <x-report-card title="Penjualan" category="Laporan Finansial"
                description="Pantau rekapitulasi omzet transaksi, item menu terlaris, dan data pesanan harian/bulanan."
                :route="route('reports.sales')" color="primary" icon="fas fa-cash-register" />

            <!-- Komponen Laporan Keuntungan -->
            <x-report-card title="Keuntungan (Profit)" category="Analisis Keuangan"
                description="Analisis laba kotor dan bersih berdasarkan harga jual dikurangi harga pokok modal (HPP)."
                :route="route('reports.profit')" color="success" icon="fas fa-chart-pie" />

            <!-- Komponen Laporan Pembelian -->
            <x-report-card title="Pembelian Bahan Baku" category="Manajemen Stok & Supplier"
                description="Lihat riwayat pembelian stok bahan baku dari pemasok beserta total biaya operasional."
                :route="route('reports.purchases')" color="warning" icon="fas fa-boxes" />
        </div> --}}
    </div>
</x-layouts.owner>
