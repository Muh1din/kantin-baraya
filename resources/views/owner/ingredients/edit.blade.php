<x-layouts.login title="Edit Bahan Baku">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('owner.ingredients.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Kembali ke daftar bahan</a>
            <section class="mt-5 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
                <h1 class="text-2xl font-black">Edit bahan baku</h1>
                <p class="mt-2 text-sm text-stone-500">Saldo saat ini {{ $ingredient->current_stock }} {{ $ingredient->unit->abbreviation }}. Saldo hanya berubah melalui pencatatan pergerakan stok.</p>
                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <form action="{{ route('owner.ingredients.update', $ingredient) }}" method="POST" class="mt-6 grid gap-5 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold">Nama bahan <span class="text-red-600">*</span></label>
                        <input id="name" name="name" value="{{ old('name', $ingredient->name) }}" maxlength="150" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div>
                        <label for="unit_id" class="mb-2 block text-sm font-semibold">Satuan <span class="text-red-600">*</span></label>
                        <select id="unit_id" name="unit_id" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id', $ingredient->unit_id) == $unit->id)>{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="minimum_stock" class="mb-2 block text-sm font-semibold">Stok minimum <span class="text-red-600">*</span></label>
                        <input id="minimum_stock" name="minimum_stock" type="number" min="0" step="0.001" value="{{ old('minimum_stock', $ingredient->minimum_stock) }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div>
                        <label for="average_cost_price" class="mb-2 block text-sm font-semibold">Harga pokok rata-rata (Rp) <span class="text-red-600">*</span></label>
                        <input id="average_cost_price" name="average_cost_price" type="number" min="0" step="0.01" value="{{ old('average_cost_price', $ingredient->average_cost_price) }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan perubahan</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</x-layouts.login>
