<x-layouts.login title="Verifikasi Email">
    <main class="flex min-h-screen items-center justify-center px-5 py-10">
        <section class="w-full max-w-lg rounded-3xl bg-white p-8 shadow-xl sm:p-10">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-2xl text-orange-600">✉</div>
            <h1 class="text-2xl font-semibold tracking-tight">Verifikasi alamat email</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">
                Terima kasih sudah mendaftar. Buka link verifikasi yang kami kirim ke
                <strong>{{ auth()->user()->email }}</strong> untuk mengaktifkan akun dan melanjutkan.
            </p>
            @if (session('status'))
                <div class="mt-5 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700" role="status">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('verification.send') }}" class="mt-7">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-orange-500 px-5 py-3 font-semibold text-white hover:bg-orange-600">Kirim ulang link verifikasi</button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-gray-200 px-5 py-3 font-medium text-gray-700 hover:bg-gray-50">Keluar</button>
            </form>
        </section>
    </main>
</x-layouts.login>
