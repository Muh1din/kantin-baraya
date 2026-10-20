<x-layouts.login title="Edit Menu">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('owner.menus.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Kembali ke daftar menu</a>
            <section class="mt-5 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
                <h1 class="text-2xl font-black">Edit menu</h1>
                <p class="mt-2 text-sm text-stone-500">Perbarui informasi menu {{ $menu->name }}.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('owner.menus.update', $menu) }}" method="POST" enctype="multipart/form-data" class="mt-6 grid gap-5 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold">Nama menu <span class="text-red-600">*</span></label>
                        <input id="name" name="name" value="{{ old('name', $menu->name) }}" required maxlength="100"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div>
                        <label for="category_id" class="mb-2 block text-sm font-semibold">Kategori <span class="text-red-600">*</span></label>
                        <select id="category_id" name="category_id" required
                            class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $menu->category_id) == $category->id)>
                                    {{ $category->name }}{{ $category->is_active ? '' : ' (Nonaktif)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="price" class="mb-2 block text-sm font-semibold">Harga (Rp) <span class="text-red-600">*</span></label>
                        <input id="price" name="price" type="number" min="1" step="1" value="{{ old('price', $menu->price) }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div>
                        <label for="stock" class="mb-2 block text-sm font-semibold">Stok <span class="text-red-600">*</span></label>
                        <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $menu->stock) }}" required
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="mb-2 block text-sm font-semibold">Deskripsi <span class="font-normal text-stone-400">(opsional)</span></label>
                        <textarea id="description" name="description" rows="4"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('description', $menu->description) }}</textarea>
                    </div>
                    @if ($menu->image)
                        <div class="sm:col-span-2">
                            <p class="mb-2 text-sm font-semibold">Foto saat ini</p>
                            <img src="{{ \Illuminate\Support\Str::startsWith($menu->image, ['http://', 'https://']) ? $menu->image : asset('storage/' . ltrim($menu->image, '/')) }}" alt="{{ $menu->name }}" class="h-36 w-48 rounded-xl object-cover">
                        </div>
                    @endif
                    <div class="sm:col-span-2">
                        <label for="image" class="mb-2 block text-sm font-semibold">Ganti foto <span class="font-normal text-stone-400">(opsional, maks. 2 MB)</span></label>
                        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                            class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:font-semibold file:text-orange-700">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan perubahan</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</x-layouts.login>
