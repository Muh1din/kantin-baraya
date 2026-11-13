<x-layouts.owner title="Loyalty & Reward">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Loyalty & Reward</h1>
            <p class="mt-1 text-sm text-stone-500">Atur menu gratis yang dapat ditukar pelanggan menggunakan poin loyalitas.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah Reward Menu Gratis</h2>
            <p class="mt-1 text-sm text-stone-500">Tentukan menu dan jumlah poin yang harus dimiliki pelanggan untuk menukarnya.</p>
            @include('owner.rewards._form', ['reward' => null])
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Reward</h2>
                <span class="text-sm text-stone-500">{{ $rewards->total() }} reward</span>
            </div>

            @if ($rewards->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada reward menu gratis.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Menu Reward</th>
                                <th scope="col" class="px-5 py-3.5">Nilai Menu</th>
                                <th scope="col" class="px-5 py-3.5">Poin Penukaran</th>
                                <th scope="col" class="px-5 py-3.5">Penukaran</th>
                                <th scope="col" class="px-5 py-3.5">Status</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($rewards as $reward)
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $reward->menu->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">Rp{{ number_format($reward->menu->price, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-black text-orange-700">{{ number_format($reward->points_required, 0, ',', '.') }} poin</td>
                                    <td class="px-5 py-4">{{ number_format($reward->redemptions_count, 0, ',', '.') }} kali</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $reward->is_active ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-stone-700' }}">
                                            {{ $reward->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('owner.rewards.edit', $reward) }}" class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Edit</a>
                                            <form action="{{ route('owner.rewards.destroy', $reward) }}" method="POST"
                                                onsubmit="return confirm('Hapus reward ini? Riwayat penukaran pelanggan akan tetap tersimpan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($rewards->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $rewards->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.owner>
