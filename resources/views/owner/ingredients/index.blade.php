<x-layouts.login title="Bahan Baku">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <header class="mb-6">
                <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Dashboard Owner</a>
                <h1 class="mt-2 text-3xl font-black">Bahan Baku</h1>
                <p class="mt-2 text-sm text-stone-500">Kelola informasi bahan dan pantau saldo persediaan.</p>
            </header>

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <p class="font-bold">Bahan baku belum dapat disimpan:</p>
                    <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="mb-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-extrabold">Tambah bahan baku</h2>
                @if ($units->isEmpty())
                    <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Belum ada satuan. Tambahkan data satuan melalui proses pengelolaan database terlebih dahulu.</p>
                @else
                    <form action="{{ route('owner.ingredients.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @csrf
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold">Nama bahan <span class="text-red-600">*</span></label>
                            <input id="name" name="name" value="{{ old('name') }}" maxlength="150" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>
                        <div>
                            <label for="unit_id" class="mb-2 block text-sm font-semibold">Satuan <span class="text-red-600">*</span></label>
                            <select id="unit_id" name="unit_id" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                <option value="">Pilih satuan</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="opening_stock" class="mb-2 block text-sm font-semibold">Stok awal <span class="text-red-600">*</span></label>
                            <input id="opening_stock" name="opening_stock" type="number" min="0" step="0.001" value="{{ old('opening_stock', '0.000') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>
                        <div>
                            <label for="minimum_stock" class="mb-2 block text-sm font-semibold">Stok minimum <span class="text-red-600">*</span></label>
                            <input id="minimum_stock" name="minimum_stock" type="number" min="0" step="0.001" value="{{ old('minimum_stock', '0.000') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>
                        <div>
                            <label for="average_cost_price" class="mb-2 block text-sm font-semibold">Harga pokok rata-rata (Rp) <span class="text-red-600">*</span></label>
                            <input id="average_cost_price" name="average_cost_price" type="number" min="0" step="0.01" value="{{ old('average_cost_price', '0.00') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>
                        <div class="flex items-end sm:col-span-2 lg:col-span-3">
                            <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan bahan</button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="mb-6 grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <h2 class="text-lg font-extrabold">Catat stok masuk</h2>
                    <p class="mt-1 text-sm text-stone-500">Jumlah stok masuk harus lebih besar dari nol.</p>
                    @if ($activeIngredients->isEmpty())
                        <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Belum ada bahan baku aktif untuk menerima stok.</p>
                    @else
                        <form action="{{ route('owner.ingredients.stock-in') }}" method="POST" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="stock_in_token" value="{{ $stockInToken }}">
                            <div>
                                <label for="stock_in_ingredient_id" class="mb-2 block text-sm font-semibold">Bahan baku <span class="text-red-600">*</span></label>
                                <select id="stock_in_ingredient_id" name="stock_in_ingredient_id" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    <option value="">Pilih bahan baku</option>
                                    @foreach ($activeIngredients as $ingredient)
                                        <option value="{{ $ingredient->id }}" @selected(old('stock_in_ingredient_id') == $ingredient->id)>{{ $ingredient->name }} ({{ $ingredient->unit->abbreviation }})</option>
                                    @endforeach
                                </select>
                                @error('stock_in_ingredient_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="stock_in_quantity" class="mb-2 block text-sm font-semibold">Jumlah masuk <span class="text-red-600">*</span></label>
                                <input id="stock_in_quantity" name="stock_in_quantity" type="number" min="0.001" step="0.001" value="{{ old('stock_in_quantity') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                @error('stock_in_quantity')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="stock_in_notes" class="mb-2 block text-sm font-semibold">Catatan <span class="font-normal text-stone-400">(opsional)</span></label>
                                <textarea id="stock_in_notes" name="stock_in_notes" rows="2" maxlength="5000" class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('stock_in_notes') }}</textarea>
                                @error('stock_in_notes')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan stok masuk</button>
                        </form>
                    @endif
                </article>

                <article class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <h2 class="text-lg font-extrabold">Penyesuaian stok</h2>
                    <p class="mt-1 text-sm text-stone-500">Gunakan angka positif untuk menambah dan negatif untuk mengurangi stok.</p>
                    @if ($allIngredients->isEmpty())
                        <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Tambahkan bahan baku terlebih dahulu.</p>
                    @else
                        <form action="{{ route('owner.ingredients.adjustment') }}" method="POST" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="adjustment_token" value="{{ $adjustmentToken }}">
                            <div>
                                <label for="adjustment_ingredient_id" class="mb-2 block text-sm font-semibold">Bahan baku <span class="text-red-600">*</span></label>
                                <select id="adjustment_ingredient_id" name="adjustment_ingredient_id" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                    <option value="">Pilih bahan baku</option>
                                    @foreach ($allIngredients as $ingredient)
                                        <option value="{{ $ingredient->id }}" @selected(old('adjustment_ingredient_id') == $ingredient->id)>{{ $ingredient->name }} ({{ $ingredient->unit->abbreviation }}){{ $ingredient->is_active ? '' : ' (Nonaktif)' }}</option>
                                    @endforeach
                                </select>
                                @error('adjustment_ingredient_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="adjustment_quantity" class="mb-2 block text-sm font-semibold">Jumlah penyesuaian <span class="text-red-600">*</span></label>
                                <input id="adjustment_quantity" name="adjustment_quantity" type="number" step="0.001" value="{{ old('adjustment_quantity') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                @error('adjustment_quantity')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="adjustment_reason" class="mb-2 block text-sm font-semibold">Alasan <span class="text-red-600">*</span></label>
                                <textarea id="adjustment_reason" name="adjustment_reason" rows="2" maxlength="5000" required class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('adjustment_reason') }}</textarea>
                                @error('adjustment_reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="rounded-xl border border-orange-300 px-5 py-3 text-sm font-bold text-orange-700 transition hover:bg-orange-50">Simpan penyesuaian</button>
                        </form>
                    @endif
                </article>
            </section>

            <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-5 py-4 sm:px-7"><h2 class="font-extrabold">Daftar bahan baku</h2></div>
                @if ($ingredients->isEmpty())
                    <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada bahan baku.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[850px] text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                                <tr><th class="px-5 py-3 sm:px-7">Nama bahan</th><th class="px-5 py-3">Satuan</th><th class="px-5 py-3">Stok saat ini</th><th class="px-5 py-3">Stok minimum</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 sm:px-7">Tindakan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($ingredients as $ingredient)
                                    <tr>
                                        <td class="px-5 py-4 font-bold sm:px-7">{{ $ingredient->name }}</td>
                                        <td class="px-5 py-4">{{ $ingredient->unit->name }} ({{ $ingredient->unit->abbreviation }})</td>
                                        <td class="px-5 py-4">
                                            <span class="font-semibold {{ $ingredient->current_stock <= $ingredient->minimum_stock ? 'text-red-700' : 'text-stone-800' }}">{{ $ingredient->current_stock }}</span>
                                            @if ($ingredient->current_stock <= $ingredient->minimum_stock)
                                                <span class="ml-2 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">Stok rendah</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">{{ $ingredient->minimum_stock }}</td>
                                        <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $ingredient->is_active ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-600' }}">{{ $ingredient->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                        <td class="px-5 py-4 sm:px-7">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('owner.ingredients.edit', $ingredient) }}" class="font-semibold text-orange-600 hover:underline">Edit</a>
                                                <form action="{{ route('owner.ingredients.toggle-status', $ingredient) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="font-semibold text-stone-600 hover:underline">{{ $ingredient->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-7">{{ $ingredients->links() }}</div>
                @endif
            </section>

            <section class="mt-6 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-5 py-4 sm:px-7"><h2 class="font-extrabold">Pergerakan stok terbaru</h2></div>
                @if ($recentMovements->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-stone-500">Belum ada riwayat pergerakan stok.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                                <tr><th class="px-5 py-3 sm:px-7">Waktu</th><th class="px-5 py-3">Bahan</th><th class="px-5 py-3">Jenis</th><th class="px-5 py-3">Perubahan</th><th class="px-5 py-3">Saldo setelah</th><th class="px-5 py-3">Catatan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($recentMovements as $movement)
                                    <tr>
                                        <td class="px-5 py-4 text-stone-600 sm:px-7">{{ $movement->occurred_at->format('d M Y H:i') }}</td>
                                        <td class="px-5 py-4 font-semibold">{{ $movement->ingredient->name }} ({{ $movement->ingredient->unit->abbreviation }})</td>
                                        <td class="px-5 py-4">{{ match ($movement->movement_type) { 'opening_balance' => 'Saldo awal', 'stock_in' => 'Stok masuk', 'stock_out' => 'Stok keluar', 'adjustment' => 'Penyesuaian', default => $movement->movement_type } }}</td>
                                        <td class="px-5 py-4 font-semibold">{{ $movement->quantity_change }}</td>
                                        <td class="px-5 py-4">{{ $movement->stock_after }}</td>
                                        <td class="px-5 py-4 text-stone-600">{{ $movement->notes ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </main>
</x-layouts.login>
