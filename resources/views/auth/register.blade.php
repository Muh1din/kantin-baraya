<x-layouts.login title="Daftar Customer">
    <div class="mx-auto w-full max-w-md rounded-3xl bg-white p-8 shadow-xl">
        <h1 class="text-2xl font-black text-stone-900">Daftar Customer</h1>
        <p class="mt-2 text-sm text-stone-500">Buat akun untuk memesan di Kantin Baraya.</p>
        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-semibold">Nama</label>
                <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="w-full rounded-xl border border-stone-300 px-4 py-3">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-xl border border-stone-300 px-4 py-3">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold">Kata sandi</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-stone-300 px-4 py-3">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold">Ulangi kata sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border border-stone-300 px-4 py-3">
            </div>
            <button class="w-full rounded-xl bg-orange-500 px-5 py-3 font-bold text-white hover:bg-orange-600">Daftar</button>
        </form>
        <p class="mt-5 text-center text-sm text-stone-600">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-orange-600 hover:underline">Masuk</a></p>
    </div>
</x-layouts.login>
