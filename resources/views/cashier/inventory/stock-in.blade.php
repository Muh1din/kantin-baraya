<x-layouts.cashier title="Stok Masuk">
    <div class="max-w-3xl space-y-6">
        <header>
            <p class="mt-1 text-sm text-stone-500">Catat penambahan stok bahan. Setiap transaksi akan masuk ke riwayat
                stok.</p>
        </header>
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
                role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            @if ($ingredients->isEmpty())
                <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Belum ada bahan aktif untuk menerima
                    stok. Tambahkan bahan melalui <a class="font-bold underline"
                        href="{{ route('kasir.inventory.index') }}">Daftar Bahan Baku</a>.</p>
            @else
                <form action="{{ route('kasir.inventory.stock-in') }}" method="POST" class="space-y-5">
                    @csrf<input type="hidden" name="stock_in_token" value="{{ $stockInToken }}">
                    <div><label for="stock_in_ingredient_id"
                            class="mb-2 block text-sm font-semibold text-stone-700">Bahan baku *</label><select
                            id="stock_in_ingredient_id" name="stock_in_ingredient_id" required
                            class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm">
                            <option value="">Pilih bahan</option>
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}" @selected(old('stock_in_ingredient_id') == $ingredient->id)>
                                    {{ $ingredient->name }} — stok {{ $ingredient->current_stock }}
                                    {{ $ingredient->unit->abbreviation }}</option>
                            @endforeach
                        </select></div>
                    <div><label for="stock_in_quantity" class="mb-2 block text-sm font-semibold text-stone-700">Jumlah
                            stok masuk *</label><input id="stock_in_quantity" name="stock_in_quantity" type="number"
                            min="0.001" step="0.001" value="{{ old('stock_in_quantity') }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm"></div>
                    <div><label for="stock_in_reason" class="mb-2 block text-sm font-semibold text-stone-700">Alasan
                            *</label>
                        <textarea id="stock_in_reason" name="stock_in_reason" rows="4" maxlength="5000" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm">{{ old('stock_in_reason') }}</textarea>
                    </div>
                    <button type="submit"
                        class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">Catat
                        stok masuk</button>
                </form>
            @endif
        </section>
    </div>
</x-layouts.cashier>
