<nav aria-label="Navigasi Kasir" class="flex flex-wrap items-center gap-2">
    @php
        $onDashboard = request()->routeIs('cashier.dashboard');
        $onPos = request()->routeIs('kasir.pos.index');
        $onHistory = request()->routeIs('kasir.pos.history', 'kasir.pos.receipt');
        $activeClass = 'rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-orange-700 shadow-sm';
        $inactiveClass = 'rounded-xl bg-white/20 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/30';
    @endphp
    <a href="{{ route('cashier.dashboard') }}" @if ($onDashboard) aria-current="page" @endif class="{{ $onDashboard ? $activeClass : $inactiveClass }}">Dashboard</a>
    <a href="{{ route('kasir.pos.index') }}" @if ($onPos) aria-current="page" @endif class="{{ $onPos ? $activeClass : $inactiveClass }}">POS</a>
    <a href="{{ route('kasir.pos.history') }}" @if ($onHistory) aria-current="page" @endif class="{{ $onHistory ? $activeClass : $inactiveClass }}">Riwayat transaksi</a>
</nav>
