<x-layouts.login title="Edit Kategori Menu">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">
            <a href="{{ route('owner.menu-categories.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Kembali ke kategori</a>
            <section class="mt-5 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
                <h1 class="text-2xl font-black">Edit kategori</h1>
                <p class="mt-2 text-sm text-stone-500">Perbarui nama atau deskripsi kategori.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('owner.menu-categories.update', $menuCategory) }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold">Nama kategori <span class="text-red-600">*</span></label>
                        <input id="name" name="name" value="{{ old('name', $menuCategory->name) }}" required maxlength="255"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div>
                        <label for="description" class="mb-2 block text-sm font-semibold">Deskripsi <span class="font-normal text-stone-400">(opsional)</span></label>
                        <textarea id="description" name="description" rows="4"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('description', $menuCategory->description) }}</textarea>
                    </div>
                    <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan perubahan</button>
                </form>
            </section>
        </div>
    </main>
</x-layouts.login>
