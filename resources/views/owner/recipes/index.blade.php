<x-layouts.login title="Resep Menu">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <header class="mb-6">
                <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Dashboard Owner</a>
                <h1 class="mt-2 text-3xl font-black">Resep Menu</h1>
                <p class="mt-2 text-sm text-stone-500">Atur kebutuhan bahan baku untuk satu porsi setiap menu.</p>
            </header>

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
            @endif

            <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-5 py-4 sm:px-7"><h2 class="font-extrabold">Daftar menu dan status resep</h2></div>
                @if ($menus->isEmpty())
                    <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada menu.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[700px] text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                                <tr><th class="px-5 py-3 sm:px-7">Menu</th><th class="px-5 py-3">Kategori</th><th class="px-5 py-3">Status menu</th><th class="px-5 py-3">Resep</th><th class="px-5 py-3 sm:px-7">Tindakan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($menus as $menu)
                                    <tr>
                                        <td class="px-5 py-4 font-bold sm:px-7">{{ $menu->name }}</td>
                                        <td class="px-5 py-4">{{ $menu->menuCategory?->name ?? $menu->category }}</td>
                                        <td class="px-5 py-4">{{ $menu->is_available ? 'Aktif' : 'Nonaktif' }}</td>
                                        <td class="px-5 py-4">
                                            @if ($menu->recipe_ingredients_count > 0)
                                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">{{ $menu->recipe_ingredients_count }} bahan</span>
                                            @else
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Belum dibuat</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 sm:px-7"><a href="{{ route('owner.recipes.show', $menu) }}" class="font-semibold text-orange-600 hover:underline">Kelola resep</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-7">{{ $menus->links() }}</div>
                @endif
            </section>
        </div>
    </main>
</x-layouts.login>
