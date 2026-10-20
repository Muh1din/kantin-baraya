<x-layouts.login title="Menu Kantin">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Dashboard Owner</a>
                    <h1 class="mt-2 text-3xl font-black">Menu Kantin</h1>
                    <p class="mt-2 text-sm text-stone-500">Kelola informasi dan ketersediaan menu.</p>
                </div>
                <a href="{{ route('owner.menu-categories.index') }}" class="rounded-xl border border-orange-200 px-4 py-3 text-sm font-bold text-orange-700 transition hover:bg-orange-50">Kelola kategori</a>
            </header>

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <p class="font-bold">Menu belum dapat disimpan:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="mb-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-extrabold">Tambah menu</h2>
                @if ($categories->isEmpty())
                    <p class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Buat kategori aktif sebelum menambahkan menu.
                        <a href="{{ route('owner.menu-categories.index') }}" class="font-bold underline">Kelola kategori</a>
                    </p>
                @else
                    <form action="{{ route('owner.menus.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @csrf
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold">Nama menu <span class="text-red-600">*</span></label>
                            <input id="name" name="name" value="{{ old('name') }}" required maxlength="100"
                                class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>
                        <div>
                            <label for="category_id" class="mb-2 block text-sm font-semibold">Kategori <span class="text-red-600">*</span></label>
                            <select id="category_id" name="category_id" required
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="price" class="mb-2 block text-sm font-semibold">Harga (Rp) <span class="text-red-600">*</span></label>
                            <input id="price" name="price" type="number" min="1" step="1" value="{{ old('price') }}" required
                                class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>
                        <div>
                            <label for="stock" class="mb-2 block text-sm font-semibold">Stok awal <span class="text-red-600">*</span></label>
                            <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', 0) }}" required
                                class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>
                        <div>
                            <label for="image" class="mb-2 block text-sm font-semibold">Foto menu <span class="font-normal text-stone-400">(opsional, maks. 2 MB)</span></label>
                            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                                class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:font-semibold file:text-orange-700">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label for="description" class="mb-2 block text-sm font-semibold">Deskripsi <span class="font-normal text-stone-400">(opsional)</span></label>
                            <textarea id="description" name="description" rows="2"
                                class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('description') }}</textarea>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan menu</button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-5 py-4 sm:px-7">
                    <h2 class="font-extrabold">Daftar menu</h2>
                </div>
                @if ($menus->isEmpty())
                    <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada menu.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                                <tr>
                                    <th class="px-5 py-3 sm:px-7">Menu</th>
                                    <th class="px-5 py-3">Kategori</th>
                                    <th class="px-5 py-3">Harga</th>
                                    <th class="px-5 py-3">Stok</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3 sm:px-7">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($menus as $menu)
                                    <tr>
                                        <td class="px-5 py-4 sm:px-7">
                                            <p class="font-bold">{{ $menu->name }}</p>
                                            @if ($menu->description)
                                                <p class="mt-1 max-w-xs truncate text-xs text-stone-500">{{ $menu->description }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">{{ $menu->menuCategory?->name ?? $menu->category }}</td>
                                        <td class="px-5 py-4 font-semibold">Rp{{ number_format($menu->price, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4">{{ $menu->stock }}</td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $menu->is_available ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-600' }}">
                                                {{ $menu->is_available ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 sm:px-7">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('owner.menus.edit', $menu) }}" class="font-semibold text-orange-600 hover:underline">Edit</a>
                                                <form action="{{ route('owner.menus.toggle-status', $menu) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="font-semibold text-stone-600 hover:underline">
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
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-7">{{ $menus->links() }}</div>
                @endif
            </section>
        </div>
    </main>
</x-layouts.login>
