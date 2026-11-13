<x-layouts.cashier title="Data Pelanggan">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Data Pelanggan</h1>
            <p class="mt-1 text-sm text-stone-500">Daftar akun pelanggan terdaftar dan ringkasan transaksinya.</p>
        </header>

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <form action="{{ route('kasir.customers.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <label for="search" class="sr-only">Cari nama atau email pelanggan</label>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Cari nama atau email pelanggan..."
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">Cari Pelanggan</button>
                @if (!empty($filters['search']))
                    <a href="{{ route('kasir.customers.index') }}" class="rounded-xl border border-stone-300 px-5 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Reset</a>
                @endif
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Pelanggan</h2>
                <span class="text-sm text-stone-500">{{ $customers->total() }} pelanggan</span>
            </div>
            @if ($customers->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">{{ !empty($filters['search']) ? 'Tidak ada pelanggan yang cocok dengan pencarian.' : 'Belum ada akun pelanggan terdaftar.' }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Nama</th>
                                <th scope="col" class="px-5 py-3.5">Email</th>
                                <th scope="col" class="px-5 py-3.5">Telepon terakhir</th>
                                <th scope="col" class="px-5 py-3.5">Transaksi</th>
                                <th scope="col" class="px-5 py-3.5">Transaksi terakhir</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Riwayat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($customers as $customer)
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $customer->name }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $customer->email }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $customer->latest_order_phone ?: 'Tidak tersedia' }}</td>
                                    <td class="px-5 py-4">{{ number_format($customer->orders_count, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-stone-600">{{ $customer->orders_max_created_at ? \Illuminate\Support\Carbon::parse($customer->orders_max_created_at)->format('d/m/Y H:i') : 'Belum ada transaksi' }}</td>
                                    <td class="px-5 py-4 sm:px-6"><a href="{{ route('kasir.customers.show', $customer) }}" class="font-semibold text-orange-600 hover:text-orange-700 hover:underline">Lihat Riwayat</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($customers->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $customers->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.cashier>
