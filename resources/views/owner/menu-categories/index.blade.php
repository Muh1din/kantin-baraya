<x-layouts.login title="Kategori Menu">
    <main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700">← Dashboard Owner</a>
                    <h1 class="mt-2 text-3xl font-black">Kategori Menu</h1>
                    <p class="mt-2 text-sm text-stone-500">Tambah, ubah, dan atur status kategori menu.</p>
                </div>
            </header>

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <p class="font-bold">Kategori belum dapat disimpan:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="mb-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-extrabold">Tambah kategori</h2>
                <form action="{{ route('owner.menu-categories.store') }}" method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold">Nama kategori <span class="text-red-600">*</span></label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div>
                        <label for="description" class="mb-2 block text-sm font-semibold">Deskripsi <span class="font-normal text-stone-400">(opsional)</span></label>
                        <input id="description" name="description" value="{{ old('description') }}"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan kategori</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-100 px-5 py-4 sm:px-7">
                    <h2 class="font-extrabold">Daftar kategori</h2>
                </div>
                @if ($categories->isEmpty())
                    <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada kategori menu.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[620px] text-left text-sm">
                            <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                                <tr>
                                    <th class="px-5 py-3 sm:px-7">Nama kategori</th>
                                    <th class="px-5 py-3">Deskripsi</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3 sm:px-7">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($categories as $category)
                                    <tr>
                                        <td class="px-5 py-4 font-bold sm:px-7">{{ $category->name }}</td>
                                        <td class="px-5 py-4 text-stone-600">{{ $category->description ?: '—' }}</td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-600' }}">
                                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 sm:px-7">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('owner.menu-categories.edit', $category) }}" class="font-semibold text-orange-600 hover:underline">Edit</a>
                                                <form action="{{ route('owner.menu-categories.toggle-status', $category) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="font-semibold text-stone-600 hover:underline">
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
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-7">{{ $categories->links() }}</div>
                @endif
            </section>
        </div>
    </main>
</x-layouts.login>
