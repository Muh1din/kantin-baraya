<x-layouts.owner title="Resep Menu">
    <div class="space-y-6">
        <header>
            <a href="{{ route('owner.recipes.index') }}"
                class="text-sm font-semibold text-orange-600 hover:text-orange-700">&larr; Kembali ke resep</a>
            <h1 class="mt-3 text-2xl font-black text-stone-900">Resep: {{ $menu->name }}</h1>
            <p class="mt-1 text-sm text-stone-500">Atur kebutuhan bahan baku untuk satu porsi menu ini.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
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

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah bahan ke resep</h2>
            @if ($activeIngredients->isEmpty())
                <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Tidak ada bahan baku aktif yang bisa ditambahkan.</p>
            @else
                <form action="{{ route('owner.recipes.ingredients.store', $menu) }}" method="POST" class="mt-4 space-y-4"
                    x-data="{ rows: [{ ingredient_id: '', quantity: '' }] }">
                    @csrf
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                            <div>
                                <label :for="'ingredient-' + index" class="mb-2 block text-sm font-semibold text-stone-700">Bahan baku</label>
                                <select :id="'ingredient-' + index" :name="`ingredients[${index}][ingredient_id]`" x-model="row.ingredient_id" required
                                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm">
                                    <option value="">Pilih bahan baku</option>
                                    @foreach ($activeIngredients as $ingredient)
                                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit->abbreviation }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label :for="'quantity-' + index" class="mb-2 block text-sm font-semibold text-stone-700">Jumlah per porsi</label>
                                <input :id="'quantity-' + index" :name="`ingredients[${index}][quantity]`" type="number" min="0.001" step="0.001" x-model="row.quantity" required
                                    class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm">
                            </div>
                            <button type="button" x-show="rows.length > 1" @click="rows.splice(index, 1)"
                                class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">Hapus</button>
                        </div>
                    </template>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="rows.push({ ingredient_id: '', quantity: '' })"
                            class="rounded-xl border border-orange-300 px-5 py-2.5 text-sm font-bold text-orange-700 hover:bg-orange-50">Tambah baris</button>
                        <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-600">Simpan bahan</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Komposisi per porsi</h2>
            </div>
            @if ($recipeIngredients->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada bahan dalam resep ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr><th class="px-5 py-3.5 sm:px-6">Bahan</th><th class="px-5 py-3.5">Jumlah</th><th class="px-5 py-3.5 sm:px-6">Tindakan</th></tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($recipeIngredients as $recipeIngredient)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-stone-900 sm:px-6">{{ $recipeIngredient->ingredient->name }}</td>
                                    <td class="px-5 py-4">
                                        <form action="{{ route('owner.recipes.ingredients.update', [$menu, $recipeIngredient]) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input name="quantity" type="number" min="0.001" step="0.001" value="{{ old('quantity', $recipeIngredient->quantity) }}" required
                                                class="w-32 rounded-lg border border-stone-300 px-3 py-2">
                                            <span>{{ $recipeIngredient->ingredient->unit->abbreviation }}</span>
                                            <button type="submit" class="font-semibold text-orange-600 hover:text-orange-700">Simpan</button>
                                        </form>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <form action="{{ route('owner.recipes.ingredients.destroy', [$menu, $recipeIngredient]) }}" method="POST" onsubmit="return confirm('Hapus bahan ini dari resep?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.owner>
