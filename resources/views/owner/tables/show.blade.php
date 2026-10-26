<x-layouts.owner title="Table QR">
    <div class="max-w-xl space-y-5 ml-0">
        <section class="rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-sm sm:p-8">
            <h1 class="text-2xl font-black text-stone-900">{{ $table->table_code }}</h1>
            <p class="mt-2 text-sm text-stone-500">Scan QR code to open the menu for this table.</p>

            <img src="{{ $qrImage }}" alt="QR code {{ $table->table_code }}"
                class="mx-auto mt-6 h-64 w-64 rounded-xl border border-stone-100 bg-white p-2">

            <p class="mt-4 break-all text-xs text-stone-500">{{ $menuUrl }}</p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('owner.tables.qr.download', $table) }}"
                    class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-600">
                    Download QR
                </a>
            </div>
        </section>
    </div>
</x-layouts.owner>