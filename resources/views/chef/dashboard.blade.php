<x-layouts.chef>
    <x-slot:title>Dashboard Koki</x-slot:title>

    @php
        $statusLabels = [
            'confirmed' => 'Menunggu Diproses',
            'in_progress' => 'Sedang Dimasak',
            'completed' => 'Selesai',
        ];

        $statusStyles = [
            'confirmed' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'in_progress' => 'bg-orange-50 text-orange-700 ring-orange-200',
            'completed' => 'bg-green-50 text-green-700 ring-green-200',
        ];

        $filters = [
            'confirmed' => 'Menunggu',
            'in_progress' => 'Diproses',
            'completed' => 'Selesai',
        ];

        $currentStatus = $statusLabels[$status] ?? 'Pesanan';
    @endphp

    <div class="min-h-screen space-y-6 bg-gray-50 p-4 sm:p-6 lg:p-8">

        {{-- HEADER --}}
        <section class="overflow-hidden rounded-2xl bg-orange-500 px-6 py-7 text-white shadow-sm sm:px-8">
            <div class="mt-3 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Antrean Pesanan
                    </h1>

                    <p class="mt-2 text-sm text-orange-50 sm:text-base">
                        Pantau antrean dan kelola pesanan yang perlu disiapkan.
                    </p>
                </div>

                <div class="w-fit rounded-xl bg-white/15 px-4 py-3">
                    <p class="text-xs text-orange-100">
                        Status saat ini
                    </p>

                    <p class="mt-1 font-semibold">
                        {{ ucfirst($currentStatus) }}
                    </p>
                </div>
            </div>
        </section>

        <div id="audioWarning" role="alert"
            class="hidden rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
        </div>
        <p id="soundStatus" class="sr-only" aria-live="polite">Notifikasi suara belum dimulai</p>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div role="status"
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div role="alert"
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- RINGKASAN --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
        'confirmed' => ['label' => 'Menunggu Diproses', 'count' => $counts['confirmed'] ?? 0, 'color' => 'bg-amber-500'],
        'in_progress' => ['label' => 'Sedang Dimasak', 'count' => $counts['in_progress'] ?? 0, 'color' => 'bg-orange-500'],
        'completed' => ['label' => 'Pesanan Selesai', 'count' => $counts['completed'] ?? 0, 'color' => 'bg-green-600'],
    ] as $key => $item)
                <a href="{{ route('chef.dashboard', ['status' => $key]) }}" @class([
                    'group rounded-2xl border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                    'border-orange-300 ring-1 ring-orange-200' => $status === $key,
                    'border-gray-200' => $status !== $key,
                ])>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500">
                            {{ $item['label'] }}
                        </p>

                        <span class="h-2.5 w-2.5 rounded-full {{ $item['color'] }}"></span>
                    </div>

                    <p class="mt-4 text-3xl font-bold tracking-tight text-gray-900">
                        {{ $item['count'] }}
                    </p>

                    <p class="mt-2 text-xs font-medium text-gray-400 group-hover:text-orange-600">
                        Lihat pesanan
                    </p>
                </a>
            @endforeach
        </section>

        {{-- DAFTAR PESANAN --}}
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

            {{-- JUDUL DAN FILTER --}}
            <div class="border-b border-gray-200 px-5 pt-5 sm:px-6">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Antrean Pesanan
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Pilih status untuk melihat daftar pesanan.
                        </p>
                    </div>

                    <span id="orderCount"
                        class="w-fit rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600">
                        {{ $orders->count() }} pesanan
                    </span>
                </div>

                <nav class="mt-5 flex gap-6 overflow-x-auto" aria-label="Filter status pesanan">
                    @foreach ($filters as $filterStatus => $label)
                        <a href="{{ route('chef.dashboard', ['status' => $filterStatus]) }}"
                            @if ($status === $filterStatus) aria-current="page" @endif @class([
                                'whitespace-nowrap border-b-2 px-1 pb-3 text-sm font-semibold transition',
                                'border-orange-500 text-orange-600' => $status === $filterStatus,
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-800' =>
                                    $status !== $filterStatus,
                            ])>
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- TABEL PESANAN --}}
            <div id="ordersTableWrapper" class="overflow-x-auto {{ $orders->isEmpty() ? 'hidden' : '' }}">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th scope="col" class="px-5 py-4 sm:px-6">Pesanan</th>
                            <th scope="col" class="px-5 py-4">Pelanggan</th>
                            <th scope="col" class="px-5 py-4">Waktu</th>
                            <th scope="col" class="px-5 py-4">Status</th>
                            <th scope="col" class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody" class="divide-y divide-gray-100">
                        @foreach ($orders as $order)
                            @include('chef.orders.partials.dashboard-row', ['order' => $order])
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="ordersEmptyState" class="px-6 py-16 text-center {{ $orders->isNotEmpty() ? 'hidden' : '' }}">
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                    <span class="text-xl" aria-hidden="true">♨</span>
                </div>
                <h3 class="mt-4 font-bold text-gray-900">Belum Ada Pesanan</h3>
                <p class="mt-1 text-sm text-gray-500">Tidak ada pesanan dengan status {{ strtolower($currentStatus) }}.
                </p>
                @if ($status !== 'confirmed')
                    <a href="{{ route('chef.dashboard', ['status' => 'confirmed']) }}"
                        class="mt-4 inline-flex rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600">
                        Lihat Antrean Baru
                    </a>
                @endif
            </div>
        </section>
    </div>
    <div id="chefDashboardConfig"
        data-poll-url="{{ route('chef.orders.check-new') }}"
        data-alarm-audio-url="{{ asset('audio/universfield-digital-alarm-clock-151920.mp3') }}"
        data-current-status="{{ $status }}"
        data-known-confirmed-order-ids="{{ json_encode($knownConfirmedOrderIds) }}"></div>
    @vite('resources/js/chef/notifikasi.js')
</x-layouts.chef>
