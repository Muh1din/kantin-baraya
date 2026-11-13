<x-layouts.cashier title="Stok Keluar">
    <div class="max-w-3xl space-y-6">
        <header><h1 class="text-xl font-black text-stone-900">Stok Keluar</h1><p class="mt-1 text-sm text-stone-500">Catat penggunaan atau pengurangan bahan beserta alasannya.</p></header>
        @if (session('status'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            @if ($ingredients->isEmpty())
                <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Belum ada bahan baku yang dapat dicatat stok keluarnya.</p>
            @else
                <form action="{{ route('kasir.inventory.stock-out') }}" method="POST" class="space-y-5">
                    @csrf<input type="hidden" name="stock_out_token" value="{{ $stockOutToken }}">
                    <div><label for="stock_out_ingredient_id" class="mb-2 block text-sm font-semibold text-stone-700">Bahan baku *</label><select id="stock_out_ingredient_id" name="stock_out_ingredient_id" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm"><option value="">Pilih bahan</option>@foreach ($ingredients as $ingredient)<option value="{{ $ingredient->id }}" @selected(old('stock_out_ingredient_id') == $ingredient->id)>{{ $ingredient->name }} — stok {{ $ingredient->current_stock }} {{ $ingredient->unit->abbreviation }}</option>@endforeach</select></div>
                    <div><label for="stock_out_quantity" class="mb-2 block text-sm font-semibold text-stone-700">Jumlah stok keluar *</label><input id="stock_out_quantity" name="stock_out_quantity" type="number" min="0.001" step="0.001" value="{{ old('stock_out_quantity') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm"></div>
                    <div><label for="stock_out_reason" class="mb-2 block text-sm font-semibold text-stone-700">Alasan *</label><textarea id="stock_out_reason" name="stock_out_reason" rows="4" maxlength="5000" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm">{{ old('stock_out_reason') }}</textarea></div>
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">Catat stok keluar</button>
                </form>
            @endif
        </section>
    </div>
</x-layouts.cashier>
