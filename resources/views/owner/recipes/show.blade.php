<x-layouts.login title="Kelola Resep Menu">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <header class="mb-6">
                <a href="{{ route('owner.recipes.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Daftar resep</a>
                <h1 class="mt-2 text-3xl font-black">Resep {{ $menu->name }}</h1>
                <p class="mt-2 text-sm text-stone-500">Jumlah bahan dihitung untuk satu porsi. Mengubah resep tidak mengubah saldo inventori.</p>
            </header>

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <p class="font-bold">Komposisi belum dapat disimpan:</p>
                    <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="mb-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div><h2 class="text-lg font-extrabold">Komposisi satu porsi</h2><p class="mt-1 text-sm text-stone-500">Satuan mengikuti satuan bahan baku.</p></div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $menu->is_available ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-600' }}">Menu {{ $menu->is_available ? 'aktif' : 'nonaktif' }}</span>
                </div>

                @if ($recipeIngredients->isEmpty())
                    <p class="rounded-xl bg-stone-50 px-4 py-6 text-center text-sm text-stone-500">Resep belum memiliki komposisi bahan.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[650px] text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                                <tr><th class="px-4 py-3">Bahan</th><th class="px-4 py-3">Jumlah per porsi</th><th class="px-4 py-3">Satuan</th><th class="px-4 py-3">Tindakan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($recipeIngredients as $index => $item)
                                    <tr>
                                        <td class="px-4 py-4 font-bold">{{ $item->ingredient->name }}</td>
                                        <td class="px-4 py-4">
                                            <form id="recipe-update-{{ $item->id }}" action="{{ route('owner.recipes.ingredients.update', [$menu, $item]) }}" method="POST" class="flex items-center gap-2">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="recipe_item_id" value="{{ $item->id }}">
                                                <input name="quantity" aria-label="Jumlah {{ $item->ingredient->name }} per porsi" type="number" min="0.001" step="0.001" placeholder="Contoh: 0.150" value="{{ old('recipe_item_id') == $item->id ? old('quantity') : $item->quantity }}" required class="w-32 rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                                                @if ($errors->has('quantity') && old('recipe_item_id') == $item->id)<span class="text-xs text-red-700">{{ $errors->first('quantity') }}</span>@endif
                                            </form>
                                        </td>
                                        <td class="px-4 py-4">{{ $item->ingredient->unit->name }} ({{ $item->ingredient->unit->abbreviation }})</td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <button form="recipe-update-{{ $item->id }}" type="submit" class="font-semibold text-orange-600 hover:underline">Simpan jumlah</button>
                                                <form action="{{ route('owner.recipes.ingredients.destroy', [$menu, $item]) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="font-semibold text-red-600 hover:underline">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><h2 class="text-lg font-extrabold">Tambah komposisi</h2><p class="mt-1 text-sm text-stone-500">Tambahkan satu atau beberapa bahan sekaligus.</p></div>
                </div>
                @if ($activeIngredients->isEmpty())
                    <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Tidak ada bahan aktif yang belum digunakan pada resep ini.</p>
                @else
                    <form action="{{ route('owner.recipes.ingredients.store', $menu) }}" method="POST" class="mt-5">
                        @csrf
                        <div id="recipe-ingredient-rows" class="space-y-3">
                            @foreach (old('ingredients', [['ingredient_id' => '', 'quantity' => '']]) as $rowIndex => $oldRow)
                                <div class="recipe-ingredient-row grid gap-3 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
                                    <div>
                                        <label for="ingredient-{{ $rowIndex }}" class="mb-2 block text-sm font-semibold">Bahan baku <span class="text-red-600">*</span></label>
                                        <select id="ingredient-{{ $rowIndex }}" name="ingredients[{{ $rowIndex }}][ingredient_id]" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                            <option value="">Pilih bahan aktif</option>
                                            @foreach ($activeIngredients as $ingredient)
                                                <option value="{{ $ingredient->id }}" @selected(($oldRow['ingredient_id'] ?? '') == $ingredient->id)>{{ $ingredient->name }} — {{ $ingredient->unit->abbreviation }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="quantity-{{ $rowIndex }}" class="mb-2 block text-sm font-semibold">Jumlah per porsi <span class="text-red-600">*</span></label>
                                        <input id="quantity-{{ $rowIndex }}" name="ingredients[{{ $rowIndex }}][quantity]" type="number" min="0.001" step="0.001" placeholder="Contoh: 0.150" value="{{ $oldRow['quantity'] ?? '' }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    </div>
                                    <button type="button" class="remove-recipe-row rounded-xl border border-stone-300 px-4 py-3 text-sm font-semibold text-stone-600 hover:bg-stone-50" @disabled($rowIndex === 0)>Hapus baris</button>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button id="add-recipe-row" type="button" class="rounded-xl border border-orange-200 px-4 py-3 text-sm font-bold text-orange-700 hover:bg-orange-50">Tambah baris</button>
                            <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white hover:bg-orange-600">Simpan komposisi</button>
                        </div>
                    </form>
                    <template id="recipe-ingredient-template">
                        <div class="recipe-ingredient-row grid gap-3 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
                            <div>
                                <label class="mb-2 block text-sm font-semibold">Bahan baku <span class="text-red-600">*</span></label>
                                <select name="ingredients[__INDEX__][ingredient_id]" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    <option value="">Pilih bahan aktif</option>
                                    @foreach ($activeIngredients as $ingredient)
                                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }} — {{ $ingredient->unit->abbreviation }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold">Jumlah per porsi <span class="text-red-600">*</span></label>
                                <input name="ingredients[__INDEX__][quantity]" type="number" min="0.001" step="0.001" placeholder="Contoh: 0.150" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                            </div>
                            <button type="button" class="remove-recipe-row rounded-xl border border-stone-300 px-4 py-3 text-sm font-semibold text-stone-600 hover:bg-stone-50">Hapus baris</button>
                        </div>
                    </template>
                    <script>
                        (() => {
                            const rows = document.getElementById('recipe-ingredient-rows');
                            const template = document.getElementById('recipe-ingredient-template');
                            let nextIndex = rows.querySelectorAll('.recipe-ingredient-row').length;

                            document.getElementById('add-recipe-row').addEventListener('click', () => {
                                const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                                rows.insertAdjacentHTML('beforeend', html);
                            });

                            rows.addEventListener('click', (event) => {
                                if (event.target.matches('.remove-recipe-row') && rows.querySelectorAll('.recipe-ingredient-row').length > 1) {
                                    event.target.closest('.recipe-ingredient-row').remove();
                                }
                            });
                        })();
                    </script>
                @endif
            </section>
        </div>
    </main>
</x-layouts.login>
