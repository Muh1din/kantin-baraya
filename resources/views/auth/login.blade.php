
<x-layouts.auth title="Masuk ke Akun">

    <main class="grid min-h-screen lg:grid-cols-2">

        {{-- PANEL BRANDING --}}
        <section class="relative hidden overflow-hidden bg-primary p-12 lg:flex lg:flex-col lg:justify-between xl:p-16">

            <div class="auth-pattern absolute inset-0"></div>

            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full border-[40px] border-white/10"></div>

            <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-primary-light/40 blur-2xl"></div>

            <div class="relative z-10">
                <h2 class="text-lg font-semibold text-white">
                    Kantin Baraya
                </h2>

                <p class="text-xs text-white/70">
                    Satu tempat, banyak rasa.
                </p>
            </div>

            <div class="relative z-10 max-w-lg py-12">
                <h1 class="text-4xl font-semibold leading-tight tracking-tight text-white xl:text-5xl">
                    Momen makan enak, dimulai dari sini.
                </h1>

                <p class="mt-5 max-w-md text-sm leading-7 text-white/75">
                    Masuk ke akun Anda untuk mengelola pesanan dan menikmati
                    pengalaman Kantin Baraya dengan lebih mudah.
                </p>

                <div class="mt-8 flex items-center gap-4">
                    <div class="flex -space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary bg-orange-100">🍜</div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary bg-amber-100">🍛</div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-primary bg-red-100">🥤</div>
                    </div>

                    <p class="text-xs leading-5 text-white/80">
                        Pilihan favoritmu,<br>
                        <span class="font-medium text-white">
                            lebih dekat setiap hari.
                        </span>
                    </p>
                </div>
            </div>

            <div class="relative z-10 text-xs text-white/60">
                &copy; {{ date('Y') }} Kantin Baraya
            </div>
        </section>

        {{-- FORM LOGIN --}}
        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-10 lg:px-12">
            <div class="w-full max-w-md">

                {{-- LOGO MOBILE --}}
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary text-white shadow-lg">
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
                        Masuk ke akun
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-typography-body">
                        Masukkan email dan password untuk melanjutkan.
                    </p>
                </div>

                {{-- ERROR LARAVEL --}}
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form
                    id="loginForm"
                    action="{{ route('login.process') }}"
                    method="POST"
                    class="space-y-5"
                    novalidate
                >
                    @csrf

                    {{-- EMAIL --}}
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium">
                            Alamat email
                        </label>

                        <div id="emailField" class="field flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 transition">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="shrink-0 text-typography-muted">
                                <rect x="3" y="5" width="18" height="14" rx="3"/>
                                <path d="m4 7 8 6 8-6"/>
                            </svg>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                autocomplete="username"
                                placeholder="nama@email.com"
                                class="w-full min-w-0 bg-transparent py-3.5 text-sm"
                                required
                            >
                        </div>

                        <p id="emailError" class="mt-1.5 hidden text-xs text-accent-red">
                            Masukkan alamat email yang valid.
                        </p>
                    </div>

                    {{-- PASSWORD --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="password" class="text-sm font-medium">
                                Password
                            </label>

                            <a href="{{ url('/forgot-password') }}" id="forgotLink" class="text-xs font-medium text-primary hover:text-primary-dark">
                                Lupa password?
                            </a>
                        </div>

                        <div id="passwordField" class="field flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 transition">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="shrink-0 text-typography-muted">
                                <rect x="4" y="10" width="16" height="11" rx="3"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                <path d="M12 14v3"/>
                            </svg>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="Masukkan password"
                                class="w-full min-w-0 bg-transparent py-3.5 text-sm"
                                required
                            >

                            <button type="button" id="togglePassword" class="shrink-0 rounded-lg p-1 text-typography-muted transition hover:bg-gray-100" aria-label="Tampilkan password">
                                <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>
                            </button>
                        </div>

                        <p id="passwordError" class="mt-1.5 hidden text-xs text-accent-red">
                            Password wajib diisi.
                        </p>
                    </div>

                    {{-- INGAT SAYA --}}
                    <div class="flex items-center justify-between gap-3">
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-typography-body">
                            <input type="checkbox" id="remember" name="remember" value="1" class="h-4 w-4 accent-primary">
                            Ingat saya
                        </label>

                        <span class="text-xs text-typography-muted">
                            Akses aman & pribadi
                        </span>
                    </div>

                    {{-- BUTTON LOGIN --}}
                    <button type="submit" class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-primary px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition duration-200 hover:-translate-y-0.5 hover:bg-primary-dark hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-primary/20 active:translate-y-0">
                        Masuk

                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="transition group-hover:translate-x-1">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    {{-- FEEDBACK JAVASCRIPT --}}
                    <div id="formMessage" class="hidden rounded-xl border px-4 py-3 text-sm" role="status" aria-live="polite"></div>
                </form>

                {{-- REGISTER --}}
                <div class="my-7 flex items-center gap-4">
                    <div class="h-px flex-1 bg-gray-200"></div>
                    <span class="text-xs text-typography-muted">atau</span>
                    <div class="h-px flex-1 bg-gray-200"></div>
                </div>

                <p class="text-center text-sm text-typography-body">
                    Belum punya akun?

                    <a href="{{ url('/register') }}" id="registerLink" class="font-semibold text-primary hover:text-primary-dark">
                        Daftar sekarang
                    </a>
                </p>

                {{-- KETENTUAN --}}
                <p class="mt-10 text-center text-xs leading-5 text-typography-muted">
                    Dengan masuk, Anda menyetujui
                    <a href="#" class="underline underline-offset-2">Ketentuan Layanan</a>
                    dan
                    <a href="#" class="underline underline-offset-2">Kebijakan Privasi</a>.
                </p>

            </div>
        </section>
    </main>

    @push('scripts')
        <script src="{{ asset('js/auth/login.js') }}" defer></script>
    @endpush

</x-layouts.auth>
