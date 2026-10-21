<x-layouts.owner title="Bahan Baku">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <header>
            <p class="mt-1 text-sm text-stone-500">Kelola informasi bahan dan pantau saldo persediaan.</p>
        </header>

        {{-- Flash Messages & Errors --}}
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
                role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-bold">Bahan baku belum dapat disimpan:</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Tambah Bahan Baku --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah bahan baku</h2>
            @if ($units->isEmpty())
                <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Belum ada satuan. Tambahkan data satuan melalui proses pengelolaan database terlebih dahulu.
                </p>
            @else
                <form action="{{ route('owner.ingredients.store') }}" method="POST"
                    class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @csrf
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama bahan <span
                                class="text-red-600">*</span></label>
                        <input id="name" name="name" value="{{ old('name') }}" maxlength="150" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                    </div>
                    <div>
                        <label for="unit_id" class="mb-2 block text-sm font-semibold text-stone-700">Satuan <span
                                class="text-red-600">*</span></label>
                        <select id="unit_id" name="unit_id" required
                            class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                            <option value="">Pilih satuan</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}
                                    ({{ $unit->abbreviation }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="opening_stock" class="mb-2 block text-sm font-semibold text-stone-700">Stok awal
                            <span class="text-red-600">*</span></label>
                        <input id="opening_stock" name="opening_stock" type="number" min="0" step="0.001"
                            value="{{ old('opening_stock', '0') }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                    </div>
                    <div>
                        <label for="minimum_stock" class="mb-2 block text-sm font-semibold text-stone-700">Stok minimum
                            <span class="text-red-600">*</span></label>
                        <input id="minimum_stock" name="minimum_stock" type="number" min="0" step="0.001"
                            value="{{ old('minimum_stock', '0') }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                    </div>
                    <div>
                        <label for="average_cost_price" class="mb-2 block text-sm font-semibold text-stone-700">Harga
                            pokok rata-rata (Rp) <span class="text-red-600">*</span></label>
                        <input id="average_cost_price" name="average_cost_price" type="number" min="0"
                            step="0.01" value="{{ old('average_cost_price', '0') }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                    </div>
                    <div class="flex items-end sm:col-span-2 lg:col-span-3">
                        <button type="submit"
                            class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Simpan
                            bahan</button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Form Transaksi Stok --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Catat Stok Masuk --}}
            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-extrabold text-stone-900">Catat stok masuk</h2>
                <p class="mt-1 text-sm text-stone-500">Jumlah stok masuk harus lebih besar dari nol.</p>
                @if ($activeIngredients->isEmpty())
                    <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Belum ada bahan baku aktif
                        untuk menerima stok.</p>
                @else
                    <form action="{{ route('owner.ingredients.stock-in') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="stock_in_token" value="{{ $stockInToken }}">
                        <div>
                            <label for="stock_in_ingredient_id"
                                class="mb-2 block text-sm font-semibold text-stone-700">Bahan baku <span
                                    class="text-red-600">*</span></label>
                            <select id="stock_in_ingredient_id" name="stock_in_ingredient_id" required
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                                <option value="">Pilih bahan baku</option>
                                @foreach ($activeIngredients as $ingredient)
                                    <option value="{{ $ingredient->id }}" @selected(old('stock_in_ingredient_id') == $ingredient->id)>
                                        {{ $ingredient->name }} ({{ $ingredient->unit->abbreviation }})</option>
                                @endforeach
                            </select>
                            @error('stock_in_ingredient_id')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="stock_in_quantity"
                                class="mb-2 block text-sm font-semibold text-stone-700">Jumlah masuk <span
                                    class="text-red-600">*</span></label>
                            <input id="stock_in_quantity" name="stock_in_quantity" type="number" min="0.001"
                                step="0.001" value="{{ old('stock_in_quantity') }}" required
                                class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                            @error('stock_in_quantity')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="stock_in_notes" class="mb-2 block text-sm font-semibold text-stone-700">Catatan
                                <span class="font-normal text-stone-400">(opsional)</span></label>
                            <textarea id="stock_in_notes" name="stock_in_notes" rows="2" maxlength="5000"
                                class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">{{ old('stock_in_notes') }}</textarea>
                            @error('stock_in_notes')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Simpan
                            stok masuk</button>
                    </form>
                @endif
            </article>

            {{-- Penyesuaian Stok --}}
            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-extrabold text-stone-900">Penyesuaian stok</h2>
                <p class="mt-1 text-sm text-stone-500">Gunakan angka positif untuk menambah dan negatif untuk
                    mengurangi stok.</p>
                @if ($allIngredients->isEmpty())
                    <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Tambahkan bahan baku
                        terlebih dahulu.</p>
                @else
                    <form action="{{ route('owner.ingredients.adjustment') }}" method="POST"
                        class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="adjustment_token" value="{{ $adjustmentToken }}">
                        <div>
                            <label for="adjustment_ingredient_id"
                                class="mb-2 block text-sm font-semibold text-stone-700">Bahan baku <span
                                    class="text-red-600">*</span></label>
                            <select id="adjustment_ingredient_id" name="adjustment_ingredient_id" required
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                                <option value="">Pilih bahan baku</option>
                                @foreach ($allIngredients as $ingredient)
                                    <option value="{{ $ingredient->id }}" @selected(old('adjustment_ingredient_id') == $ingredient->id)>
                                        {{ $ingredient->name }}
                                        ({{ $ingredient->unit->abbreviation }})
                                        {{ $ingredient->is_active ? '' : ' (Nonaktif)' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('adjustment_ingredient_id')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="adjustment_quantity"
                                class="mb-2 block text-sm font-semibold text-stone-700">Jumlah penyesuaian <span
                                    class="text-red-600">*</span></label>
                            <input id="adjustment_quantity" name="adjustment_quantity" type="number" step="0.001"
                                value="{{ old('adjustment_quantity') }}" required
                                class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                            @error('adjustment_quantity')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="adjustment_reason"
                                class="mb-2 block text-sm font-semibold text-stone-700">Alasan <span
                                    class="text-red-600">*</span></label>
                            <textarea id="adjustment_reason" name="adjustment_reason" rows="2" maxlength="5000" required
                                class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">{{ old('adjustment_reason') }}</textarea>
                            @error('adjustment_reason')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="rounded-xl border border-orange-300 px-5 py-2.5 text-sm font-bold text-orange-700 transition hover:bg-orange-50">Simpan
                            penyesuaian</button>
                    </form>
                @endif
            </article>
        </div>

        {{-- Tabel Daftar Bahan Baku --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Bahan Baku</h2>
            </div>
            @if ($ingredients->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada bahan baku.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Nama bahan</th>
                                <th scope="col" class="px-5 py-3.5">Satuan</th>
                                <th scope="col" class="px-5 py-3.5">Stok saat ini</th>
                                <th scope="col" class="px-5 py-3.5">Stok minimum</th>
                                <th scope="col" class="px-5 py-3.5">Status</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($ingredients as $ingredient)
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $ingredient->name }}
                                    </td>
                                    <td class="px-5 py-4">{{ $ingredient->unit->name }}
                                        ({{ $ingredient->unit->abbreviation }})
                                    </td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="font-semibold {{ $ingredient->current_stock <= $ingredient->minimum_stock ? 'text-red-700' : 'text-stone-800' }}">{{ $ingredient->current_stock }}</span>
                                        @if ($ingredient->current_stock <= $ingredient->minimum_stock)
                                            <span
                                                class="ml-2 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">Stok
                                                rendah</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">{{ $ingredient->minimum_stock }}</td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $ingredient->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $ingredient->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('owner.ingredients.edit', $ingredient) }}"
                                                class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Edit</a>
                                            <form action="{{ route('owner.ingredients.toggle-status', $ingredient) }}"
                                                method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="font-semibold text-stone-600 hover:text-stone-800 hover:underline">{{ $ingredient->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $ingredients->links() }}</div>
            @endif
        </section>

        {{-- Pergerakan Stok Terbaru --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Pergerakan Stok Terbaru</h2>
            </div>
            @if ($recentMovements->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-stone-500">Belum ada riwayat pergerakan stok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Waktu</th>
                                <th scope="col" class="px-5 py-3.5">Bahan</th>
                                <th scope="col" class="px-5 py-3.5">Jenis</th>
                                <th scope="col" class="px-5 py-3.5">Perubahan</th>
                                <th scope="col" class="px-5 py-3.5">Saldo Setelah</th>
                                <th scope="col" class="px-5 py-3.5">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($recentMovements as $movement)
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="px-5 py-4 text-stone-600 sm:px-6">
                                        {{ $movement->occurred_at->format('d M Y H:i') }}</td>
                                    <td class="px-5 py-4 font-semibold text-stone-900">
                                        {{ $movement->ingredient->name }}
                                        ({{ $movement->ingredient->unit->abbreviation }})
                                    </td>
                                    <td class="px-5 py-4">
                                        {{ match ($movement->movement_type) {
                                            'opening_balance' => 'Saldo awal',
                                            'stock_in' => 'Stok masuk',
                                            'stock_out' => 'Stok keluar',
                                            'adjustment' => 'Penyesuaian',
                                            default => $movement->movement_type,
                                        } }}
                                    </td>
                                    <td
                                        class="px-5 py-4 font-semibold {{ $movement->quantity_change > 0 ? 'text-emerald-600' : ($movement->quantity_change < 0 ? 'text-red-600' : 'text-stone-800') }}">
                                        {{ $movement->quantity_change > 0 ? '+' . $movement->quantity_change : $movement->quantity_change }}
                                    </td>
                                    <td class="px-5 py-4 font-medium text-stone-900">{{ $movement->stock_after }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $movement->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.owner>
