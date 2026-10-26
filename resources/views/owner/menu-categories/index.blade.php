<x-layouts.owner title="Kategori Menu">
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <header>
            <p class="mt-1 text-sm text-stone-500">Tambah, ubah, dan atur status kategori menu.</p>
        </header>

        {{-- Flash Message Status --}}
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
                role="status">
                {{ session('status') }}
            </div>
        @endif

        {{-- Flash Message Errors --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-bold">Kategori belum dapat disimpan:</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Tambah Kategori --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah kategori</h2>
            <form action="{{ route('owner.menu-categories.store') }}" method="POST"
                class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama kategori <span
                            class="text-red-600">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                </div>
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-stone-700">Deskripsi <span
                            class="font-normal text-stone-400">(opsional)</span></label>
                    <input id="description" name="description" value="{{ old('description') }}"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100 transition">
                </div>
                <div class="md:col-span-2">
                    <button type="submit"
                        class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Simpan
                        kategori</button>
                </div>
            </form>
        </section>

        {{-- Tabel Daftar Kategori --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Kategori</h2>
            </div>
            @if ($categories->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada kategori menu.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Nama kategori</th>
                                <th scope="col" class="px-5 py-3.5">Deskripsi</th>
                                <th scope="col" class="px-5 py-3.5">Status</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($categories as $category)
                                <tr class="hover:bg-stone-50/50 transition-colors">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $category->name }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $category->description ?: '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('owner.menu-categories.edit', $category) }}"
                                                class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Edit</a>
                                            <form
                                                action="{{ route('owner.menu-categories.toggle-status', $category) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="font-semibold text-stone-600 hover:text-stone-800 hover:underline">
                                                    {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $categories->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.owner>
