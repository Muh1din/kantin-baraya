<x-layouts.login title="Nota {{ $sale->invoice_number }}">
    <main class="min-h-screen bg-orange-50/60 px-4 py-10 sm:px-6">
        <article class="mx-auto max-w-2xl rounded-3xl border border-orange-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
                <div><p class="font-semibold text-orange-600">Kantin Baraya</p><h1 class="mt-1 text-2xl font-black">Transaksi berhasil</h1><p class="mt-2 text-sm text-stone-500">{{ $sale->invoice_number }}</p></div>
                <div class="text-right text-sm text-stone-500"><p>{{ $sale->created_at->format('d/m/Y H:i') }}</p><p class="mt-1 capitalize">{{ $sale->status }} · {{ $sale->payment_status }}</p></div>
            </div>
            @if (session('status'))<p class="mt-4 rounded-xl bg-green-50 p-3 text-sm font-semibold text-green-800">{{ session('status') }}</p>@endif
            <div class="mt-5 divide-y divide-stone-100">
                @foreach ($sale->items as $item)
                    <div class="flex justify-between gap-4 py-3"><div><p class="font-bold">{{ $item->menu_name }}</p><p class="text-sm text-stone-500">{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p></div><p class="font-bold">Rp {{ number_format($item->line_subtotal, 0, ',', '.') }}</p></div>
                @endforeach
            </div>
            <dl class="mt-4 space-y-2 border-t border-stone-200 pt-4 text-sm">
                <div class="flex justify-between text-lg font-extrabold"><dt>Total</dt><dd>Rp {{ number_format($sale->total, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt>Tunai diterima</dt><dd>Rp {{ number_format($sale->cash_received, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between font-bold text-green-700"><dt>Kembalian</dt><dd>Rp {{ number_format($sale->change_due, 0, ',', '.') }}</dd></div>
            </dl>
            <div class="mt-7 flex flex-wrap items-center justify-between gap-3"><div class="rounded-2xl bg-orange-500 p-2">@include('cashier.pos._navigation')</div><form action="{{ route('logout') }}" method="POST">@csrf<button class="rounded-xl border border-stone-200 px-5 py-3 font-bold text-stone-700">Keluar</button></form></div>
        </article>
    </main>
</x-layouts.login>
