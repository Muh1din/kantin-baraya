<x-layouts.customer title="Notifikasi">
    <div class="space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <header>
            <h1 class="text-xl font-black text-stone-900">Notifikasi</h1>
            <p class="mt-1 text-sm text-stone-500">Pembaruan pesanan dan aktivitas poin Anda.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">{{ session('status') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm">
            @forelse ($notifications as $notification)
                <article class="flex flex-col gap-3 border-b border-stone-100 px-5 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between {{ $notification->read_at ? '' : 'bg-orange-50/60' }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-stone-900">{{ $notification->data['title'] ?? 'Notifikasi' }}</h2>
                            @unless ($notification->read_at)
                                <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-800">Baru</span>
                            @endunless
                        </div>
                        <p class="mt-1 text-sm text-stone-600">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="mt-2 text-xs text-stone-400">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="whitespace-nowrap rounded-lg border border-orange-200 px-3 py-2 text-sm font-semibold text-orange-700 hover:bg-orange-100">Tandai sudah dibaca</button>
                        </form>
                    @endunless
                </article>
            @empty
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada notifikasi.</p>
            @endforelse
            @if ($notifications->hasPages())
                <div class="border-t border-stone-100 px-5 py-4">{{ $notifications->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.customer>
