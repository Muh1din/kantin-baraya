<x-layouts.owner title="Edit Kategori Menu">
    <div class="mx-auto max-w-2xl space-y-6">
        {{-- Header & Navigasi Kembali --}}
        <div>
            <a href="{{ route('owner.menu-categories.index') }}"
                class="inline-flex items-center gap-1 text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors">
                &larr; Kembali ke kategori
            </a>
        </div>

        {{-- Form Edit Kategori --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
            <h1 class="text-2xl font-black text-stone-900">Edit Kategori</h1>
            <p class="mt-1 text-sm text-stone-500">Perbarui nama atau deskripsi kategori.</p>

            {{-- Flash Message Errors --}}
            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    role="alert">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('owner.menu-categories.update', $menuCategory) }}" method="POST"
                class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama kategori <span
                            class="text-red-600">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $menuCategory->name) }}" required
                        maxlength="255"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-stone-700">Deskripsi <span
                            class="font-normal text-stone-400">(opsional)</span></label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">{{ old('description', $menuCategory->description) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">
                        Simpan perubahan
                    </button>
                    <a href="{{ route('owner.menu-categories.index') }}"
                        class="rounded-xl border border-stone-300 px-5 py-2.5 text-sm font-bold text-stone-700 transition hover:bg-stone-50">
                        Batal
                    </a>
                </div>
            </form>
        </section>
    </div>
</x-layouts.owner>
