<div x-data="{
    mobileNavOpen: false,
    userProfileOpen: false,
    logoutModalOpen: false,
}"
    x-effect="
        document.body.style.overflow =
            mobileNavOpen && window.innerWidth < 1024 ? 'hidden' : '';
    "
    @keydown.escape.window="
        mobileNavOpen = false;
        userProfileOpen = false;
        logoutModalOpen = false;
    "
    @resize.window="
        if (window.innerWidth >= 1024) {
            mobileNavOpen = false;
            userProfileOpen = false;
        }
    ">
    {{-- SIDEBAR OWNER --}}
    <aside
        class="border-b border-stone-200 bg-white
               lg:fixed lg:inset-y-0 lg:left-0 lg:z-40
               lg:flex lg:w-72 lg:flex-col
               lg:border-b-0 lg:border-r">
        {{-- HEADER --}}
        <div
            class="flex items-center justify-between gap-3
                   px-5 py-5
                   lg:shrink-0 lg:px-7 lg:py-7">
            {{-- BRAND --}}
            <a href="{{ route('owner.dashboard') }}" class="flex min-w-0 items-center gap-3">
                <div
                    class="grid h-11 w-11 shrink-0 place-items-center
                           rounded-2xl bg-orange-500 text-lg
                           font-black text-white
                           shadow-lg shadow-orange-500/20">
                    KB
                </div>

                <div class="min-w-0">
                    <p class="font-black text-stone-900">
                        Kantin Baraya
                    </p>

                    <p class="text-xs font-medium text-stone-500">
                        Panel Owner
                    </p>
                </div>
            </a>

            {{-- MOBILE MENU BUTTON --}}
            <button type="button"
                @click="
                    mobileNavOpen = !mobileNavOpen;
                    userProfileOpen = false;
                "
                :aria-expanded="mobileNavOpen.toString()" aria-controls="owner-navigation"
                :aria-label="mobileNavOpen
                    ?
                    'Tutup navigasi' :
                    'Buka navigasi'"
                class="grid h-10 w-10 shrink-0 place-items-center
                       rounded-xl border border-stone-200 relative
                       text-stone-600 transition
                       hover:bg-stone-50
                       focus:outline-none focus:ring-2
                       focus:ring-orange-500 lg:hidden">
                {{-- HAMBURGER --}}
                <svg x-show="!mobileNavOpen"
                    x-transition:enter="transition duration-200 ease-out"
                    x-transition:enter-start="rotate-90 scale-75 opacity-0"
                    x-transition:enter-end="rotate-0 scale-100 opacity-100"
                    x-transition:leave="transition duration-150 ease-in"
                    x-transition:leave-start="rotate-0 scale-100 opacity-100"
                    x-transition:leave-end="-rotate-90 scale-75 opacity-0"
                    class="absolute h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>

                {{-- CLOSE --}}
                <svg x-cloak x-show="mobileNavOpen"
                    x-transition:enter="transition duration-200 ease-out"
                    x-transition:enter-start="-rotate-90 scale-75 opacity-0"
                    x-transition:enter-end="rotate-0 scale-100 opacity-100"
                    x-transition:leave="transition duration-150 ease-in"
                    x-transition:leave-start="rotate-0 scale-100 opacity-100"
                    x-transition:leave-end="rotate-90 scale-75 opacity-0"
                    class="absolute h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        {{-- MOBILE BACKDROP --}}
        <div x-cloak x-show="mobileNavOpen" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="
                mobileNavOpen = false;
                userProfileOpen = false;
            "
            class="fixed inset-x-0 bottom-0 top-[84px] z-40
                   bg-stone-950/40 backdrop-blur-[2px]
                   lg:hidden"
            aria-hidden="true"></div>

        {{-- NAVIGATION --}}
        <nav id="owner-navigation" aria-label="Navigasi Owner" x-cloak x-show="mobileNavOpen"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-2 opacity-0"
            class="fixed inset-x-0 bottom-0 top-[84px] z-50
                   flex flex-col gap-1 overflow-y-auto
                   overscroll-contain border-t border-stone-200
                   bg-white px-4 pb-6 pt-4 shadow-xl
                   [scrollbar-width:none]
                   [&::-webkit-scrollbar]:hidden
                   lg:visible lg:static lg:z-auto
                   lg:!flex lg:min-h-0 lg:flex-1
                   lg:translate-y-0 lg:transform-none
                   lg:overflow-x-hidden lg:overflow-y-auto
                   lg:overscroll-auto lg:border-t-0
                   lg:px-4 lg:pt-4 lg:opacity-100
                   lg:shadow-none">
            {{-- DASHBOARD --}}
            <a href="{{ route('owner.dashboard') }}" @class([
                'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                'bg-orange-50 font-bold text-orange-700' => request()->routeIs(
                    'owner.dashboard'),
                'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs(
                    'owner.dashboard'),
            ])
                @if (request()->routeIs('owner.dashboard')) aria-current="page" @endif
                @click="
                    mobileNavOpen = false;
                    userProfileOpen = false;
                ">
                <span aria-hidden="true">⌂</span>
                Dashboard
            </a>

            {{-- KATEGORI MENU --}}
            <a href="{{ route('owner.menu-categories.index') }}" @class([
                'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                'bg-orange-50 font-bold text-orange-700' => request()->routeIs(
                    'owner.menu-categories.*'),
                'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs(
                    'owner.menu-categories.*'),
            ])
                @if (request()->routeIs('owner.menu-categories.*')) aria-current="page" @endif
                @click="
                    mobileNavOpen = false;
                    userProfileOpen = false;
                ">
                <span aria-hidden="true">▦</span>
                Kategori Menu
            </a>

            {{-- MENU --}}
            <a href="{{ route('owner.menus.index') }}" @class([
                'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                'bg-orange-50 font-bold text-orange-700' => request()->routeIs(
                    'owner.menus.*'),
                'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs(
                    'owner.menus.*'),
            ])
                @if (request()->routeIs('owner.menus.*')) aria-current="page" @endif
                @click="
                    mobileNavOpen = false;
                    userProfileOpen = false;
                ">
                <span aria-hidden="true">☷</span>
                Menu
            </a>

            {{-- BAHAN BAKU --}}
            <a href="{{ route('owner.ingredients.index') }}" @class([
                'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                'bg-orange-50 font-bold text-orange-700' => request()->routeIs(
                    'owner.ingredients.*'),
                'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs(
                    'owner.ingredients.*'),
            ])
                @if (request()->routeIs('owner.ingredients.*')) aria-current="page" @endif
                @click="
                    mobileNavOpen = false;
                    userProfileOpen = false;
                ">
                <span aria-hidden="true">▤</span>
                Bahan Baku
            </a>

            {{-- RESEP MENU --}}
            <a href="{{ route('owner.recipes.index') }}" @class([
                'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                'bg-orange-50 font-bold text-orange-700' => request()->routeIs(
                    'owner.recipes.*'),
                'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs(
                    'owner.recipes.*'),
            ])
                @if (request()->routeIs('owner.recipes.*')) aria-current="page" @endif
                @click="
                    mobileNavOpen = false;
                    userProfileOpen = false;
                ">
                <span aria-hidden="true">▧</span>
                Resep Menu
            </a>

            {{-- MENU YANG BELUM TERSEDIA --}}
            @php
                $disabledMenus = [
                    ['icon' => '▣', 'label' => 'POS', 'module' => 'POS'],
                    ['icon' => '◷', 'label' => 'Pre-order', 'module' => 'pre-order'],
                    ['icon' => '⚙', 'label' => 'Produksi', 'module' => 'produksi'],
                    ['icon' => '☆', 'label' => 'Loyalty', 'module' => 'loyalty'],
                    ['icon' => '▥', 'label' => 'Laporan', 'module' => 'laporan'],
                ];
            @endphp

            @foreach ($disabledMenus as $menu)
                <span aria-disabled="true" title="Modul {{ $menu['module'] }} belum tersedia"
                    class="flex shrink-0 cursor-not-allowed
                           items-center gap-3 rounded-xl px-4 py-3
                           text-sm font-medium text-stone-400 lg:w-full">
                    <span aria-hidden="true">
                        {{ $menu['icon'] }}
                    </span>

                    {{ $menu['label'] }}
                </span>
            @endforeach

            {{-- SPACER DESKTOP --}}
            <div class="hidden lg:block lg:flex-1"></div>

            {{-- PROFILE SECTION --}}
            <div class="relative mt-3 shrink-0 border-t
                       border-stone-100 pt-4 lg:mt-auto"
                @click.outside="userProfileOpen = false">
                {{-- PROFILE TRIGGER CARD --}}
                <button type="button" @click="userProfileOpen = !userProfileOpen"
                    :aria-expanded="userProfileOpen.toString()" aria-label="Buka profil pengguna"
                    class="flex w-full items-center gap-3 rounded-xl
                           bg-stone-50 p-3 text-left transition
                           hover:bg-orange-50 focus:outline-none
                           focus:ring-2 focus:ring-orange-500">
                    {{-- AVATAR --}}
                    <div
                        class="grid h-10 w-10 shrink-0 place-items-center
                               rounded-full bg-orange-100 font-bold
                               text-orange-700">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    {{-- USER INFO --}}
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-stone-800">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-stone-500">
                            Owner
                        </p>
                    </div>

                    {{-- CHEVRON --}}
                    <svg class="h-5 w-5 shrink-0 text-stone-400 transition-transform duration-200"
                        :class="{ 'rotate-180': userProfileOpen }" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                    </svg>
                </button>

                {{-- PROFILE POPUP --}}
                <div x-cloak x-show="userProfileOpen" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-2 scale-95 opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                    x-transition:leave-end="translate-y-2 scale-95 opacity-0"
                    class="absolute bottom-full left-0 right-0 z-[60]
                           mb-2 origin-bottom overflow-hidden
                           rounded-2xl border border-stone-200
                           bg-white shadow-xl">
                    {{-- ACCOUNT INFORMATION --}}
                    <div class="border-b border-stone-100 p-4">
                        <p class="text-xs font-medium text-stone-400">
                            Akun yang sedang login
                        </p>

                        <p class="mt-2 truncate text-sm font-bold text-stone-900">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="mt-1 break-all text-xs text-stone-500">
                            {{ auth()->user()->email }}
                        </p>

                        <span
                            class="mt-3 inline-flex rounded-full
                                   bg-orange-50 px-3 py-1
                                   text-xs font-bold text-orange-700">
                            Owner
                        </span>
                    </div>

                    {{-- LOGOUT TRIGGER --}}
                    <div class="p-2">
                        <button type="button"
                            @click="
                                userProfileOpen = false;
                                mobileNavOpen = false;
                                logoutModalOpen = true;
                            "
                            class="flex w-full items-center gap-3
                                   rounded-xl px-3 py-3 text-sm
                                   font-semibold text-red-600
                                   transition hover:bg-red-50">
                            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 15l3-3m0 0-3-3m3 3H9" />
                            </svg>

                            Keluar dari Akun
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </aside>

    {{-- LOGOUT CONFIRMATION MODAL --}}
    <div x-cloak x-show="logoutModalOpen" x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click.self="logoutModalOpen = false"
        class="fixed inset-0 z-[100] flex items-center
               justify-center bg-stone-950/50 p-4
               backdrop-blur-sm">
        <section x-show="logoutModalOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-3 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-3 scale-95 opacity-0" role="alertdialog" aria-modal="true"
            aria-labelledby="logout-title" aria-describedby="logout-description"
            class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
            {{-- ICON --}}
            <div
                class="mx-auto grid h-14 w-14 place-items-center
                       rounded-full bg-red-50 text-red-600">
                <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 15l3-3m0 0-3-3m3 3H9" />
                </svg>
            </div>

            {{-- CONFIRMATION TEXT --}}
            <div class="mt-4 text-center">
                <h2 id="logout-title" class="text-lg font-black text-stone-900">
                    Keluar dari Akun?
                </h2>

                <p id="logout-description" class="mt-2 text-sm leading-6 text-stone-500">
                    Apakah kamu yakin ingin keluar dari akun

                    <span class="font-bold text-stone-700">
                        {{ auth()->user()->name }}
                    </span>?

                    Kamu harus login kembali untuk mengakses panel Owner.
                </p>
            </div>

            {{-- MODAL BUTTONS --}}
            <div class="mt-6 grid grid-cols-2 gap-3">
                {{-- CANCEL --}}
                <button type="button" @click="logoutModalOpen = false"
                    class="rounded-xl border border-stone-200
                           px-4 py-3 text-sm font-bold
                           text-stone-600 transition hover:bg-stone-50">
                    Batal
                </button>

                {{-- CONFIRM LOGOUT --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="w-full rounded-xl bg-red-600
                               px-4 py-3 text-sm font-bold text-white
                               transition hover:bg-red-700
                               focus:outline-none focus:ring-2
                               focus:ring-red-500 focus:ring-offset-2">
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
