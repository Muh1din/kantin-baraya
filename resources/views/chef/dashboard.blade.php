<x-layouts.login title="Antrean Koki">
    <main class="min-h-screen bg-orange-50/60 px-4 py-8 sm:px-6">
        <div class="mx-auto max-w-6xl space-y-5">
            <header class="rounded-2xl bg-orange-500 p-6 text-white">
                <h1 class="text-2xl font-black">Antrean Koki</h1>
                <p class="mt-1">Pesanan dikirim ke dapur saat dikonfirmasi, terpisah dari status pembayarannya.</p>
            </header>
            @if (session('success'))<p role="status" class="rounded-xl bg-green-50 p-3 text-green-800">{{ session('success') }}</p>@endif
            @if ($errors->any())<p role="alert" class="rounded-xl bg-red-50 p-3 text-red-800">{{ $errors->first() }}</p>@endif
            <nav class="flex flex-wrap gap-3">
                @foreach (['confirmed' => 'Menunggu', 'in_progress' => 'Sedang dimasak', 'completed' => 'Selesai'] as $key => $label)
                    <a href="{{ route('chef.dashboard', ['status' => $key]) }}" class="rounded-xl bg-white px-4 py-3 shadow-sm {{ $status === $key ? 'ring-2 ring-orange-500' : '' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
                @endforeach
            </nav>
            <section class="space-y-4">
                @forelse ($orders as $order)
                    <article class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap justify-between gap-3"><div><h2 class="font-bold">{{ $order->order_code }} · {{ $order->customer_name }}</h2><p class="text-sm text-stone-500">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->payment->first()?->payment_status === 'paid' ? 'Lunas' : 'Belum lunas' }}</p></div></div>
                        <ul class="mt-3 list-inside list-disc text-sm">@foreach ($order->items as $item)<li>{{ $item->quantity }} × {{ $item->menu_name }}</li>@endforeach</ul>
                        @if ($status !== 'completed')<form class="mt-4" method="POST" action="{{ route($status === 'confirmed' ? 'chef.orders.start' : 'chef.orders.complete', $order) }}">@csrf<button class="rounded-lg bg-orange-500 px-4 py-2 font-bold text-white">{{ $status === 'confirmed' ? 'Mulai dimasak' : 'Tandai selesai' }}</button></form>@endif
                    </article>
                @empty
                    <p class="rounded-2xl bg-white p-10 text-center text-stone-500">Tidak ada pesanan pada status ini.</p>
                @endforelse
            </section>
        </div>
    </main>
</x-layouts.login>
