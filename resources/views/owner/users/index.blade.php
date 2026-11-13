<x-layouts.owner title="Kelola Akun">
    <div class="space-y-6">
        <header>
            <h1 class="text-xl font-black text-stone-900">Kelola Akun Karyawan</h1>
            <p class="mt-1 text-sm text-stone-500">Lihat, tambahkan, dan hapus akun Kasir atau Koki.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
                role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-bold">Akun belum dapat disimpan:</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-extrabold text-stone-900">Tambah akun karyawan</h2>
            <form action="{{ route('owner.users.store') }}" method="POST"
                class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama <span
                            class="text-red-600">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                        autocomplete="name"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-stone-700">Email <span
                            class="text-red-600">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        maxlength="255" autocomplete="email"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="role" class="mb-2 block text-sm font-semibold text-stone-700">Jabatan <span
                            class="text-red-600">*</span></label>
                    <select id="role" name="role" required
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        <option value="">Pilih jabatan</option>
                        <option value="cashier" @selected(old('role') === 'cashier')>Kasir</option>
                        <option value="chef" @selected(old('role') === 'chef')>Koki</option>
                    </select>
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-stone-700">Kata sandi <span
                            class="text-red-600">*</span></label>
                    <input id="password" name="password" type="password" required minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-stone-700">Ulangi
                        kata sandi <span class="text-red-600">*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        minlength="8" autocomplete="new-password"
                        class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">
                        Tambah akun
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-stone-800">Daftar Akun Karyawan</h2>
            </div>

            @if ($users->isEmpty())
                <p class="px-5 py-12 text-center text-sm text-stone-500">Belum ada akun Kasir atau Koki.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Nama</th>
                                <th scope="col" class="px-5 py-3.5">Email</th>
                                <th scope="col" class="px-5 py-3.5">Jabatan</th>
                                <th scope="col" class="px-5 py-3.5 sm:px-6">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-stone-700">
                            @foreach ($users as $employee)
                                <tr class="transition-colors hover:bg-stone-50/50">
                                    <td class="px-5 py-4 font-bold text-stone-900 sm:px-6">{{ $employee->name }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $employee->email }}</td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ in_array($employee->role->name, ['cashier', 'kasir'], true) ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ in_array($employee->role->name, ['cashier', 'kasir'], true) ? 'Kasir' : 'Koki' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <form action="{{ route('owner.users.destroy', $employee) }}" method="POST"
                                            onsubmit="return confirm('Hapus akun karyawan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="font-semibold text-red-600 hover:text-red-700 hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())
                    <div class="border-t border-stone-100 px-5 py-4 sm:px-6">{{ $users->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</x-layouts.owner>
