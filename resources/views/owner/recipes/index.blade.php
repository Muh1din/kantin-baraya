<x-layouts.owner title="Resep Menu">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <header>
            <p class="mt-1 text-sm text-stone-500">Atur kebutuhan bahan baku untuk satu porsi setiap menu.</p>
        </header>

        {{-- Flash Message Status --}}
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
                role="status">
                {{ session('status') }}
            </div>
        @endif

        {{-- Tabel Daftar Menu & Status Resep --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Menu & Status Resep</h2>
            </div>

            @if ($menus->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada menu.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Menu</th>
                                <th scope="col" class="px-5 py-3.5">Kategori</th>
                                <th scope="col" class="px-5 py-3.5">Status Menu</th>
                                <th scope="col" class="px-5 py-3.5">Resep</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($menus as $menu)
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">
                                        {{ $menu->name }}
                                    </td>
                                    <td class="px-5 py-4">
                                        {{ $menu->menuCategory?->name ?? $menu->category }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $menu->is_available ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $menu->is_available ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($menu->recipe_ingredients_count > 0)
                                            <span
                                                class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700">
                                                {{ $menu->recipe_ingredients_count }} bahan
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                                Belum dibuat
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <a href="{{ route('owner.recipes.show', $menu) }}"
                                            class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">
                                            Kelola resep
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-stone-100 px-5 py-4 sm:px-6">
                    {{ $menus->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layouts.owner>
