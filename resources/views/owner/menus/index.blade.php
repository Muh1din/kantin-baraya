<x-layouts.owner title="Menu Kantin">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <header class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="mt-1 text-sm text-stone-500">Kelola informasi dan ketersediaan menu.</p>
            </div>
            <a href="{{ route('owner.menu-categories.index') }}"
                class="rounded-xl border border-orange-200 bg-white px-4 py-2.5 text-sm font-bold text-orange-700 transition hover:bg-orange-50">
                Kelola kategori
            </a>
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
                <p class="font-bold">Menu belum dapat disimpan:</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Tambah Menu --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah menu</h2>
            @if ($categories->isEmpty())
                <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Buat kategori aktif sebelum menambahkan menu.
                    <a href="{{ route('owner.menu-categories.index') }}" class="font-bold underline">Kelola kategori</a>
                </p>
            @else
                <form action="{{ route('owner.menus.store') }}" method="POST" enctype="multipart/form-data"
                    class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @csrf
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama menu <span
                                class="text-red-600">*</span></label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="100"
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                    </div>
                    <div>
                        <label for="category_id" class="mb-2 block text-sm font-semibold text-stone-700">Kategori <span
                                class="text-red-600">*</span></label>
                        <select id="category_id" name="category_id" required
                            class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="price" class="mb-2 block text-sm font-semibold text-stone-700">Harga (Rp) <span
                                class="text-red-600">*</span></label>
                        <input id="price" name="price" type="number" min="1" step="1"
                            value="{{ old('price') }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                    </div>
                    <div>
                        <label for="stock" class="mb-2 block text-sm font-semibold text-stone-700">Stok awal <span
                                class="text-red-600">*</span></label>
                        <input id="stock" name="stock" type="number" min="0" step="1"
                            value="{{ old('stock', 0) }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                    </div>
                    <div>
                        <label for="image" class="mb-2 block text-sm font-semibold text-stone-700">Foto menu <span
                                class="font-normal text-stone-400">(opsional, maks. 2 MB)</span></label>
                        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                            class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-orange-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-orange-700 transition">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="description" class="mb-2 block text-sm font-semibold text-stone-700">Deskripsi <span
                                class="font-normal text-stone-400">(opsional)</span></label>
                        <textarea id="description" name="description" rows="2"
                            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">{{ old('description') }}</textarea>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <button type="submit"
                            class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Simpan
                            menu</button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Tabel Daftar Menu --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Menu</h2>
            </div>
            @if ($menus->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada menu.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Menu</th>
                                <th scope="col" class="px-5 py-3.5">Kategori</th>
                                <th scope="col" class="px-5 py-3.5">Harga</th>
                                <th scope="col" class="px-5 py-3.5">Stok</th>
                                <th scope="col" class="px-5 py-3.5">Status</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($menus as $menu)
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-bold text-stone-900">{{ $menu->name }}</p>
                                        @if ($menu->description)
                                            <p class="mt-0.5 max-w-xs truncate text-xs text-stone-500">
                                                {{ $menu->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">{{ $menu->menuCategory?->name ?? $menu->category }}</td>
                                    <td class="px-5 py-4 font-semibold text-stone-900">
                                        Rp{{ number_format($menu->price, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 font-medium">{{ $menu->stock }}</td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $menu->is_available ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $menu->is_available ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('owner.menus.edit', $menu) }}"
                                                class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Edit</a>
                                            <form action="{{ route('owner.menus.toggle-status', $menu) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="font-semibold text-stone-600 hover:text-stone-800 hover:underline">
                                                    {{ $menu->is_available ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $menus->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.owner>
