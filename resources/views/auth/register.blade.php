<x-layouts.login title="Daftar Customer">

    <main class="grid min-h-screen lg:grid-cols-2">

        {{-- PANEL BRANDING --}}
        <section
            class="relative hidden overflow-hidden bg-orange-500 p-12 lg:flex lg:flex-col lg:justify-between xl:p-16">
            {{-- BRAND --}}
            <div class="relative z-10">
                <h2 class="text-lg font-semibold text-white">
                    Kantin Baraya
                </h2>

                <p class="text-xs text-white/70">
                    Satu tempat, banyak rasa.
                </p>
            </div>

            {{-- KONTEN UTAMA --}}
            <div class="relative z-10 mb-65 max-w-lg py-12">
                <h1 class="text-4xl font-semibold leading-tight tracking-tight text-white xl:text-5xl">
                    Bergabung dan nikmati
                    <span class="text-orange-100">momen makan enak.</span>
                </h1>

                <p class="mt-5 max-w-md text-sm leading-7 text-white/75">
                    Buat akun Kantin Baraya dan nikmati kemudahan memesan
                    makanan serta minuman favoritmu setiap hari.
                </p>

                <div class="mt-8 flex items-center gap-4">
                    <div class="flex -space-x-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/30 bg-orange-100">
                            🍜
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/30 bg-amber-100">
                            🍛
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/30 bg-orange-50">
                            🥤
                        </div>
                    </div>

                    <p class="text-xs leading-5 text-white/80">
                        Pilihan favoritmu,
                        <br>
                        <span class="font-medium text-white">
                            lebih dekat setiap hari.
                        </span>
                    </p>
                </div>
            </div>
        </section>

        {{-- FORM REGISTRASI --}}
        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-10 lg:px-12">

            <div class="w-full max-w-md">

                {{-- LOGO MOBILE --}}
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-500 text-white shadow-lg">
                        <span class="text-xl font-bold">K</span>
                    </div>

                    <div>
                        <h2 class="font-semibold">Kantin Baraya</h2>

                        <p class="text-xs text-typography-muted">
                            Satu tempat, banyak rasa.
                        </p>
                    </div>
                </div>

                {{-- HEADING --}}
                <div class="mb-8">
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                        Buat akun baru
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-typography-body">
                        Daftarkan diri Anda untuk mulai memesan di Kantin Baraya.
                    </p>
                </div>

                {{-- ERROR --}}
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                        role="alert">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf

                    {{-- NAMA --}}
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium">
                            Nama lengkap
                        </label>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 transition focus-within:border-orange-400 focus-within:ring-4 focus-within:ring-orange-400/10">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" class="shrink-0 text-typography-muted">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M5 21a7 7 0 0 1 14 0" />
                            </svg>

                            <input id="name" name="name" type="text" value="{{ old('name') }}"
                                maxlength="255" required autocomplete="name" placeholder="Nama lengkap Anda"
                                class="w-full min-w-0 bg-transparent py-3.5 text-sm outline-none">
                        </div>

                        @error('name')
                            <p class="mt-1.5 text-xs text-accent-red">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium">
                            Alamat email
                        </label>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 transition focus-within:border-orange-400 focus-within:ring-4 focus-within:ring-orange-400/10">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" class="shrink-0 text-typography-muted">
                                <rect x="3" y="5" width="18" height="14" rx="3" />
                                <path d="m4 7 8 6 8-6" />
                            </svg>

                            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                autocomplete="email" placeholder="nama@email.com"
                                class="w-full min-w-0 bg-transparent py-3.5 text-sm outline-none">
                        </div>

                        @error('email')
                            <p class="mt-1.5 text-xs text-accent-red">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium">
                            Kata sandi
                        </label>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 transition focus-within:border-orange-400 focus-within:ring-4 focus-within:ring-orange-400/10">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" class="shrink-0 text-typography-muted">
                                <rect x="4" y="10" width="16" height="11" rx="3" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                <path d="M12 14v3" />
                            </svg>

                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                placeholder="Buat kata sandi"
                                class="w-full min-w-0 bg-transparent py-3.5 text-sm outline-none">
                        </div>

                        @error('password')
                            <p class="mt-1.5 text-xs text-accent-red">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- KONFIRMASI PASSWORD --}}
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium">
                            Ulangi kata sandi
                        </label>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 transition focus-within:border-orange-400 focus-within:ring-4 focus-within:ring-orange-400/10">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.7" class="shrink-0 text-typography-muted">
                                <rect x="4" y="10" width="16" height="11" rx="3" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                <path d="m9 15 2 2 4-4" />
                            </svg>

                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                autocomplete="new-password" placeholder="Ulangi kata sandi"
                                class="w-full min-w-0 bg-transparent py-3.5 text-sm outline-none">
                        </div>
                    </div>

                    {{-- BUTTON --}}
                    <button type="submit"
                        class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-500 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition duration-200 hover:-translate-y-0.5 hover:bg-orange-600 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-orange-400/30 active:translate-y-0">
                        Buat akun

                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" class="transition group-hover:translate-x-1">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                </form>

                {{-- LOGIN LINK --}}
                <p class="mt-7 text-center text-sm text-typography-body">
                    Sudah punya akun?

                    <a href="{{ route('login') }}" class="font-semibold text-orange-600 hover:text-orange-700">
                        Masuk sekarang
                    </a>
                </p>

                {{-- KETENTUAN --}}
                <p class="mt-10 text-center text-xs leading-5 text-typography-muted">
                    Dengan mendaftar, Anda menyetujui
                    <a href="#" class="underline underline-offset-2">
                        Ketentuan Layanan
                    </a>
                    dan
                    <a href="#" class="underline underline-offset-2">
                        Kebijakan Privasi
                    </a>.
                </p>

            </div>

        </section>

    </main>

</x-layouts.login>
