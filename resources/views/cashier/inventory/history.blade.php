<x-layouts.cashier title="Riwayat Stok">
    <div class="space-y-6">
        <header><h1 class="text-xl font-black text-stone-900">Riwayat Stok</h1><p class="mt-1 text-sm text-stone-500">Seluruh pergerakan saldo bahan baku tercatat di inventory.</p></header>
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6"><h2 class="font-extrabold text-stone-800">Pergerakan Stok</h2><p class="mt-1 text-sm text-stone-500">{{ number_format($movements->total(), 0, ',', '.') }} catatan</p></div>
            @if ($movements->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada riwayat pergerakan stok.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500"><tr><th class="px-5 py-3.5 sm:px-6">Waktu</th><th class="px-5 py-3.5">Bahan</th><th class="px-5 py-3.5">Jenis</th><th class="px-5 py-3.5">Perubahan</th><th class="px-5 py-3.5">Saldo setelah</th><th class="px-5 py-3.5">Alasan</th><th class="px-5 py-3.5 sm:px-6">Dicatat oleh</th></tr></thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">{{ $movement->occurred_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="px-5 py-4 font-semibold">{{ $movement->ingredient->name }} ({{ $movement->ingredient->unit->abbreviation }})</td>
                                    <td class="px-5 py-4">{{ ['opening_balance' => 'Saldo awal', 'stock_in' => 'Stok masuk', 'stock_out' => 'Stok keluar', 'adjustment' => 'Penyesuaian'][$movement->movement_type] ?? $movement->movement_type }}</td>
                                    <td class="px-5 py-4 font-semibold {{ $movement->quantity_change > 0 ? 'text-green-700' : ($movement->quantity_change < 0 ? 'text-red-700' : 'text-stone-700') }}">{{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}</td>
                                    <td class="px-5 py-4">{{ $movement->stock_after }}</td>
                                    <td class="px-5 py-4">{{ $movement->notes ?: '—' }}</td>
                                    <td class="px-5 py-4 sm:px-6">{{ $movement->user?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($movements->hasPages())<div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $movements->links() }}</div>@endif
            @endif
        </section>
    </div>
</x-layouts.cashier>
