<x-layouts.customer title="Pengaturan Profil">
    <div class="max-w-3xl space-y-6 px-4 pb-12 sm:px-6 lg:px-8">
        <header>
            <h1 class="text-xl font-black text-stone-900">Pengaturan Profil</h1>
            <p class="mt-1 text-sm text-stone-500">Lihat dan perbarui informasi akun Anda.</p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">{{ session('status') }}</div>
        @endif

        <section class="rounded-2xl border border-stone-100 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-black text-stone-900">Informasi akun</h2>
            <form method="POST" action="{{ route('customer.profile.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama lengkap</label>
                    <input id="name" name="name" type="text" required maxlength="255" autocomplete="name"
                        value="{{ old('name', $user->name) }}"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-stone-700">Email</label>
                    <input id="email" name="email" type="email" required maxlength="255" autocomplete="email"
                        value="{{ old('email', $user->email) }}"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl bg-stone-50 p-4">
                    <h3 class="font-bold text-stone-800">Ubah kata sandi</h3>
                    <p class="mt-1 text-xs text-stone-500">Kosongkan jika tidak ingin mengubah kata sandi.</p>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="current_password" class="mb-2 block text-sm font-semibold text-stone-700">Kata sandi saat ini</label>
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                                class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100">
                            @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-stone-700">Kata sandi baru</label>
                            <input id="password" name="password" type="password" minlength="8" autocomplete="new-password"
                                class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100">
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-stone-700">Ulangi kata sandi baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password"
                                class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-100">
                        </div>
                    </div>
                </div>

                <button type="submit" class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan perubahan</button>
            </form>
        </section>
    </div>
</x-layouts.customer>
