<x-layouts.cashier title="Daftar Bahan Baku">
    <div class="space-y-6">
        <header>
            <p class="mt-1 text-sm text-stone-500">Cari, tambahkan, dan perbarui informasi bahan baku.</p>
        </header>

        @if (session('status'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah bahan baku</h2>
            @if ($units->isEmpty())
                <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">Belum ada satuan yang tersedia.</p>
            @else
                <form action="{{ route('kasir.inventory.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @csrf
                    <div><label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama bahan *</label><input id="name" name="name" value="{{ old('name') }}" maxlength="150" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm"></div>
                    <div><label for="unit_id" class="mb-2 block text-sm font-semibold text-stone-700">Satuan *</label><select id="unit_id" name="unit_id" required class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm"><option value="">Pilih satuan</option>@foreach ($units as $unit)<option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }} ({{ $unit->abbreviation }})</option>@endforeach</select></div>
                    <div><label for="opening_stock" class="mb-2 block text-sm font-semibold text-stone-700">Stok awal *</label><input id="opening_stock" name="opening_stock" type="number" min="0" step="0.001" value="{{ old('opening_stock', '0') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm"></div>
                    <div><label for="minimum_stock" class="mb-2 block text-sm font-semibold text-stone-700">Stok minimum *</label><input id="minimum_stock" name="minimum_stock" type="number" min="0" step="0.001" value="{{ old('minimum_stock', '0') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm"></div>
                    <div><label for="average_cost_price" class="mb-2 block text-sm font-semibold text-stone-700">Harga pokok rata-rata (Rp) *</label><input id="average_cost_price" name="average_cost_price" type="number" min="0" step="0.01" value="{{ old('average_cost_price', '0') }}" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm"></div>
                    <div><label for="opening_reason" class="mb-2 block text-sm font-semibold text-stone-700">Alasan/catatan saldo awal *</label><input id="opening_reason" name="opening_reason" value="{{ old('opening_reason') }}" maxlength="5000" required class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm"></div>
                    <div class="sm:col-span-2 lg:col-span-3"><button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Simpan bahan</button></div>
                </form>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <div class="flex items-center justify-between"><h2 class="font-extrabold text-stone-800">Daftar Bahan Baku</h2><span class="text-sm text-stone-500">{{ $ingredients->total() }} bahan</span></div>
                <form action="{{ route('kasir.inventory.index') }}" method="GET" class="mt-4 flex flex-col gap-3 sm:flex-row">
                    <div class="flex-1"><label for="search" class="sr-only">Cari bahan baku</label><input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama bahan..." class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm"></div>
                    <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-600">Cari</button>
                    @if (!empty($filters['search']))<a href="{{ route('kasir.inventory.index') }}" class="rounded-xl border border-stone-300 px-5 py-2.5 text-center text-sm font-semibold">Reset</a>@endif
                </form>
            </div>
            @if ($ingredients->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-stone-500">{{ !empty($filters['search']) ? 'Tidak ada bahan yang cocok.' : 'Belum ada bahan baku.' }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500"><tr><th class="px-5 py-3.5 sm:px-6">Nama bahan</th><th class="px-5 py-3.5">Satuan</th><th class="px-5 py-3.5">Stok saat ini</th><th class="px-5 py-3.5">Stok minimum</th><th class="px-5 py-3.5">Status</th><th class="px-5 py-3.5 sm:px-6">Tindakan</th></tr></thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($ingredients as $ingredient)
                                @php($low = $ingredient->current_stock <= $ingredient->minimum_stock)
                                <tr>
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $ingredient->name }}</td>
                                    <td class="px-5 py-4">{{ $ingredient->unit->name }} ({{ $ingredient->unit->abbreviation }})</td>
                                    <td class="px-5 py-4 font-semibold">{{ $ingredient->current_stock }}</td>
                                    <td class="px-5 py-4">{{ $ingredient->minimum_stock }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $low ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $low ? 'Stok rendah' : 'Stok aman' }}</span><span class="ml-1 rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-600">{{ $ingredient->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td class="px-5 py-4 sm:px-6"><a href="{{ route('kasir.inventory.edit', $ingredient) }}" class="font-semibold text-orange-600 hover:underline">Edit</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($ingredients->hasPages())<div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $ingredients->links() }}</div>@endif
            @endif
        </section>
    </div>
</x-layouts.cashier>
