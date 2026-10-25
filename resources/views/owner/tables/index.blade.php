<x-layouts.owner title="Manajemen Meja">
    <div class="space-y-6">
        <header>
            <p class="mt-1 text-sm text-stone-500">Kelola meja dan token QR masing-masing.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="font-extrabold text-stone-900">Tambah meja</h2>
            <form action="{{ route('owner.tables.store') }}" method="POST"
                class="mt-4 flex flex-col gap-3 sm:flex-row">
                @csrf
                <label for="table_code" class="sr-only">Kode meja</label>
                <input id="table_code" name="table_code" value="{{ old('table_code') }}" required maxlength="255"
                    placeholder="Contoh: Meja 01"
                    class="min-w-0 flex-1 rounded-xl border border-stone-300 px-4 py-2.5 text-sm">
                <button type="submit"
                    class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-600">Tambah
                    meja</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            @if ($tables->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada meja.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th class="px-5 py-3.5 sm:px-6">Kode meja</th>
                                <th class="px-5 py-3.5">Token QR</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5">Dibuat</th>
                                <th class="px-5 py-3.5 sm:px-6">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($tables as $table)
                                <tr>
                                    <td class="px-5 py-4 sm:px-6">
                                        <form action="{{ route('owner.tables.update', $table) }}" method="POST"
                                            class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input name="table_code" value="{{ $table->table_code }}" required
                                                maxlength="255" aria-label="Kode meja {{ $table->table_code }}"
                                                class="w-40 rounded-lg border border-stone-300 px-3 py-2">
                                        </form>
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs">
                                        {{ \Illuminate\Support\Str::limit($table->qr_token, 16) }}</td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $table->is_active ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $table->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">{{ $table->created_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('owner.tables.qr', $table) }}"
                                                class="font-semibold text-orange-600 hover:underline">Lihat QR</a>
                                            <form action="{{ route('owner.tables.toggle-status', $table) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    class="font-semibold text-stone-600 hover:text-stone-900">{{ $table->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $tables->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.owner>
