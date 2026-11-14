<x-layouts.customer title="Poin & Reward">
    <div class="space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <header>
            <h1 class="text-xl font-black text-stone-900">Poin & Reward</h1>
            <p class="mt-1 text-sm text-stone-500">Tukarkan poinmu dengan menu gratis Kantin Baraya.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
                <a href="{{ route('customer.orders.index') }}" class="ml-1 font-bold underline">Lihat pesanan</a>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="flex flex-col justify-between gap-4 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 p-5 text-white shadow-sm sm:flex-row sm:items-center sm:p-6">
            <div>
                <p class="text-sm font-bold uppercase tracking-wide text-orange-100">Saldo poin</p>
                <p class="mt-1 text-3xl font-black">{{ number_format($account->saldo_poin, 0, ',', '.') }} poin</p>
                <p class="mt-2 text-sm text-orange-50">Poin dari setiap menu yang dibeli ditentukan owner dan ditambahkan setelah pembayaran diverifikasi Kasir.</p>
            </div>
            <span class="text-4xl" aria-hidden="true">⭐</span>
        </section>

        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-black text-stone-900">Tukar Poin dengan Menu Gratis</h2>
                <p class="mt-1 text-sm text-stone-500">Penukaran membuat pesanan gratis dan stok menu langsung dipesan untuk diproses Koki.</p>
            </div>

            @if ($rewards->isEmpty())
                <p class="rounded-2xl border border-stone-100 bg-white px-5 py-10 text-center text-sm text-stone-500">Belum ada reward yang tersedia.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($rewards as $reward)
                        <article class="flex flex-col rounded-2xl border border-stone-100 bg-white p-5 shadow-sm">
                            <div class="flex-1">
                                <p class="text-xs font-bold uppercase tracking-wide text-orange-600">Menu Gratis</p>
                                <h3 class="mt-2 text-lg font-black text-stone-900">{{ $reward->menu->name }}</h3>
                                <p class="mt-1 text-sm text-stone-500">Nilai menu Rp{{ number_format($reward->menu->price, 0, ',', '.') }}</p>
                                <p class="mt-4 text-sm font-bold text-orange-700">{{ number_format($reward->points_required, 0, ',', '.') }} poin</p>
                                <p class="mt-1 text-xs text-stone-500">Stok tersedia: {{ number_format($reward->menu->stock, 0, ',', '.') }}</p>
                            </div>
                            <form action="{{ route('customer.loyalty.redeem', $reward) }}" method="POST" class="mt-5"
                                onsubmit="return confirm('Tukar poin untuk reward menu gratis ini?')">
                                @csrf
                                <button type="submit" @disabled($account->saldo_poin < $reward->points_required || $reward->menu->stock < 1)
                                    class="w-full rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-stone-300">
                                    @if ($reward->menu->stock < 1)
                                        Stok Habis
                                    @elseif ($account->saldo_poin < $reward->points_required)
                                        Poin Belum Cukup
                                    @else
                                        Tukar Reward
                                    @endif
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4">
                <h2 class="font-black text-stone-900">Riwayat Poin</h2>
            </div>
            @if ($transactions->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-stone-500">Belum ada transaksi poin.</p>
            @else
                <div class="divide-y divide-stone-100">
                    @foreach ($transactions as $transaction)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                            <div>
                                <p class="font-semibold text-stone-800">
                                    {{ $transaction->description ?: ($transaction->type === 'earned' ? 'Poin transaksi' : 'Penukaran reward') }}
                                </p>
                                <p class="mt-1 text-xs text-stone-500">
                                    {{ $transaction->created_at?->format('d/m/Y H:i') ?? '—' }}
                                    @if ($transaction->order)
                                        · Pesanan {{ $transaction->order->order_code }}
                                    @endif
                                </p>
                            </div>
                            <span class="font-black {{ $transaction->type === 'earned' ? 'text-green-700' : 'text-orange-700' }}">
                                {{ $transaction->type === 'earned' ? '+' : '−' }}{{ number_format($transaction->points, 0, ',', '.') }} poin
                            </span>
                        </div>
                    @endforeach
                </div>
                @if ($transactions->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4">{{ $transactions->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.customer>
