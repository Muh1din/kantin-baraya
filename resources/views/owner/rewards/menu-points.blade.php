<x-layouts.owner title="Poin per Menu">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Atur Poin per Menu</h1>
            <p class="mt-1 text-sm text-stone-500">Tentukan poin yang didapat customer untuk setiap satu menu yang dibeli. Poin dikalikan dengan jumlah item dan diberikan setelah pembayaran diverifikasi kasir.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-bold">Pengaturan poin belum dapat disimpan.</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($menus->isEmpty())
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-12 text-center text-sm text-stone-500">
                Belum ada menu untuk diatur.
            </div>
        @else
            <form action="{{ route('owner.rewards.menu-points.update') }}" method="POST" class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                @csrf
                @method('PUT')
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-4 sm:px-6">Menu</th>
                                <th scope="col" class="px-5 py-4">Harga</th>
                                <th scope="col" class="px-5 py-4 sm:px-6">Poin per item</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($menus as $menu)
                                <tr class="hover:bg-stone-50/50">
                                    <th scope="row" class="px-5 py-4 font-bold text-stone-900 sm:px-6">
                                        {{ $menu->name }}
                                        @if (!$menu->is_available)
                                            <span class="ml-1 text-xs font-medium text-stone-400">(nonaktif)</span>
                                        @endif
                                    </th>
                                    <td class="whitespace-nowrap px-5 py-4 text-stone-600">Rp{{ number_format($menu->price, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <label class="sr-only" for="points-{{ $menu->id }}">Poin untuk {{ $menu->name }}</label>
                                        <input id="points-{{ $menu->id }}" name="points[{{ $menu->id }}]" type="number" min="0" max="1000000" step="1"
                                            value="{{ old("points.{$menu->id}", $menu->points_per_item) }}"
                                            class="w-32 rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                                        <span class="ml-1 text-stone-500">poin</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-stone-100 px-5 py-4 sm:px-6">
                    <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">
                        Simpan Pengaturan Poin
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-layouts.owner>
