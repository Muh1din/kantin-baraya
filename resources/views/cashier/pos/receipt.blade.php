<x-layouts.login title="Nota {{ $order->order_code }}">
    <main class="min-h-screen bg-orange-50/60 px-4 py-10 sm:px-6">
        <article class="mx-auto max-w-2xl rounded-3xl border border-orange-100 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
                <div>
                    <p class="font-semibold text-orange-600">Kantin Baraya</p>
                    <h1 class="mt-1 text-2xl font-black">Transaksi berhasil</h1>
                    <p class="mt-2 text-sm text-stone-500">{{ $order->order_code }}</p>
                </div>
                <div class="text-right text-sm text-stone-500">
                    <p>{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p class="mt-1 capitalize">{{ str_replace('_', ' ', $order->order_status) }} ·
                        {{ $order->payment->first()?->payment_status ? str_replace('_', ' ', $order->payment->first()->payment_status) : 'unpaid' }}
                    </p>
                </div>
            </div>
            @if (session('status'))
                <p class="mt-4 rounded-xl bg-green-50 p-3 text-sm font-semibold text-green-800">{{ session('status') }}
                </p>
            @endif
            <div class="mt-5 divide-y divide-stone-100">
                @foreach ($order->items as $item)
                    <div class="flex justify-between gap-4 py-3">
                        <div>
                            <p class="font-bold">{{ $item->menu_name }}</p>
                            <p class="text-sm text-stone-500">{{ $item->quantity }} × Rp
                                {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                        </div>
                        <p class="font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <dl class="mt-4 space-y-2 border-t border-stone-200 pt-4 text-sm">
                <div class="flex justify-between text-lg font-extrabold">
                    <dt>Total</dt>
                    <dd>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</dd>
                </div>
                @php($payment = $order->payment->first())
                @if ($payment?->payment_status === 'paid' && $payment->payment_method === 'cash')
                    <div class="flex justify-between"><dt>Uang diterima</dt><dd>Rp {{ number_format($payment->cash_received, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt>Kembalian</dt><dd>Rp {{ number_format($payment->change_due, 0, ',', '.') }}</dd></div>
                @endif
            </dl>
            @if ($payment?->payment_status === 'pending')
                <form class="mt-6 space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-4" action="{{ route('kasir.pos.settle', $order) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h2 class="font-bold text-amber-900">Selesaikan pembayaran transaksi ini</h2>
                    @if ($payment->payment_method === 'cash')
                        <label class="block text-sm font-semibold text-stone-700">Uang diterima (minimal Rp {{ number_format($payment->amount, 0, ',', '.') }})
                            <input type="number" name="cash_received" min="{{ (int) $payment->amount }}" step="1" required class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
                        </label>
                    @else
                        <label class="block text-sm font-semibold text-stone-700">Bukti QRIS untuk diperiksa kasir
                            <input type="file" name="proof_image" accept="image/*" required class="mt-1 block w-full text-sm">
                        </label>
                        <p class="text-sm text-amber-800">Periksa bukti dan transaksi QRIS sebelum menandai pembayaran lunas.</p>
                    @endif
                    <label class="flex items-start gap-2 text-sm text-stone-700"><input type="checkbox" name="manual_verification" value="1" required class="mt-1">Saya sudah menerima atau memverifikasi pembayaran.</label>
                    <button class="rounded-xl bg-green-600 px-4 py-2.5 font-bold text-white">Simpan pelunasan</button>
                </form>
            @endif
            <div class="mt-7 flex flex-wrap items-center justify-between gap-3">
                <div class="rounded-2xl bg-orange-500 p-2">@include('cashier.pos._navigation')</div>
                <form action="{{ route('logout') }}" method="POST">@csrf<button
                        class="rounded-xl border border-stone-200 px-5 py-3 font-bold text-stone-700">Keluar</button>
                </form>
            </div>
        </article>
    </main>
</x-layouts.login>
