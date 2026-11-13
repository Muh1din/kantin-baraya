<x-layouts.owner title="Hari Libur">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Hari Libur</h1>
            <p class="mt-1 text-sm text-stone-500">Catat tanggal kantin tutup dan hapus hari libur yang tidak berlaku.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-bold">Hari libur belum dapat disimpan:</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah hari libur</h2>
            <form action="{{ route('owner.holidays.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div>
                    <label for="holiday_date" class="mb-2 block text-sm font-semibold text-stone-700">Tanggal <span class="text-red-600">*</span></label>
                    <input id="holiday_date" name="holiday_date" type="date" value="{{ old('holiday_date') }}" required
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama hari libur <span class="text-red-600">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" maxlength="160" required placeholder="Contoh: Libur Hari Raya"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="notes" class="mb-2 block text-sm font-semibold text-stone-700">Catatan <span class="font-normal text-stone-400">(opsional)</span></label>
                    <input id="notes" name="notes" value="{{ old('notes') }}" maxlength="2000"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">
                        Tambah Hari Libur
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Hari Libur</h2>
            </div>

            @if ($holidays->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada hari libur yang tercatat.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Tanggal</th>
                                <th scope="col" class="px-5 py-3.5">Hari Libur</th>
                                <th scope="col" class="px-5 py-3.5">Catatan</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($holidays as $holiday)
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-800 sm:px-6">{{ $holiday->holiday_date->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-4 font-bold text-stone-900">{{ $holiday->name }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $holiday->notes ?: '—' }}</td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <form action="{{ route('owner.holidays.destroy', $holiday) }}" method="POST"
                                            onsubmit="return confirm('Hapus catatan hari libur ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-semibold text-red-600 hover:text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($holidays->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $holidays->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.owner>
