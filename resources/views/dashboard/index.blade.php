<x-layouts.login :title="'Dashboard ' . $roleLabel">
    <main class="min-h-screen bg-orange-50/60 px-4 py-10 sm:px-6">
        <div class="mx-auto max-w-5xl">
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-gradient-to-br from-orange-500 via-orange-500 to-amber-400 p-6 text-white shadow-lg sm:p-8">
                <div>
                    <p class="text-sm font-semibold text-white/80">Kantin Baraya</p>
                    <h1 class="mt-2 text-3xl font-black">Dashboard {{ $roleLabel }}</h1>
                    <p class="mt-2 text-sm text-white/90">Halo, {{ auth()->user()->name }}. Anda berhasil masuk.</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-orange-600 shadow transition hover:bg-orange-50">
                        Keluar
                    </button>
                </form>
            </header>

            <section class="mt-6 rounded-3xl border border-orange-100 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-xl font-extrabold">Selamat datang, {{ auth()->user()->name }}!</h2>
                <p class="mt-2 text-sm leading-6 text-stone-600">Anda masuk sebagai <span class="font-semibold text-orange-600">{{ $roleLabel }}</span>. Dashboard ini siap menjadi halaman kerja Anda di Kantin Baraya.</p>
                @if ($role === 'pelanggan')
                    <a href="{{ route('home') }}#menu" class="mt-5 inline-flex rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Lihat menu</a>
                @elseif ($role === 'owner')
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('owner.menus.index') }}" class="inline-flex rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Kelola menu</a>
                        <a href="{{ route('owner.menu-categories.index') }}" class="inline-flex rounded-xl border border-orange-200 px-5 py-3 text-sm font-bold text-orange-700 transition hover:bg-orange-50">Kelola kategori</a>
                    </div>
                @elseif ($role === 'kasir')
                    <div class="mt-5 rounded-2xl bg-orange-500 p-2">
                        @include('cashier.pos._navigation')
                    </div>
                @endif
            </section>
        </div>
    </main>
</x-layouts.login>
