<div
    x-data="{
        userProfileOpen: false,
        logoutModalOpen: false
    }"
    @keydown.escape.window="
        userProfileOpen = false;
        logoutModalOpen = false;
    "
>
    {{-- SIDEBAR OWNER --}}
    <aside
        class="border-b border-stone-200 bg-white
               lg:fixed lg:inset-y-0 lg:w-72
               lg:border-b-0 lg:border-r"
    >
        {{-- HEADER --}}
        <div class="flex items-center gap-3 px-5 py-5 lg:px-7 lg:py-7">
            <div
                class="grid h-11 w-11 place-items-center rounded-2xl
                       bg-orange-500 text-lg font-black text-white
                       shadow-lg shadow-orange-500/20"
            >
                KB
            </div>

            <div>
                <p class="font-black text-stone-900">
                    Kantin Baraya
                </p>

                <p class="text-xs font-medium text-stone-500">
                    Panel Owner
                </p>
            </div>
        </div>

        {{-- NAVIGATION --}}
        <nav
            aria-label="Navigasi Owner"
            class="hide-scrollbar flex gap-2 overflow-x-auto px-4 pb-4
                   lg:block lg:space-y-1 lg:overflow-y-auto
                   lg:overflow-x-hidden lg:px-4"
        >
            {{-- Dashboard --}}
            <a
                href="{{ route('owner.dashboard') }}"
                @class([
                    'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                    'bg-orange-50 font-bold text-orange-700' => request()->routeIs('owner.dashboard'),
                    'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs('owner.dashboard'),
                ])
                @if (request()->routeIs('owner.dashboard'))
                    aria-current="page"
                @endif
            >
                <span aria-hidden="true">⌂</span>
                Dashboard
            </a>

            {{-- Kategori Menu --}}
            <a
                href="{{ route('owner.menu-categories.index') }}"
                @class([
                    'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                    'bg-orange-50 font-bold text-orange-700' => request()->routeIs('owner.menu-categories.*'),
                    'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs('owner.menu-categories.*'),
                ])
            >
                <span aria-hidden="true">▦</span>
                Kategori Menu
            </a>

            {{-- Menu --}}
            <a
                href="{{ route('owner.menus.index') }}"
                @class([
                    'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                    'bg-orange-50 font-bold text-orange-700' => request()->routeIs('owner.menus.*'),
                    'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs('owner.menus.*'),
                ])
            >
                <span aria-hidden="true">☷</span>
                Menu
            </a>

            {{-- Bahan Baku --}}
            <a
                href="{{ route('owner.ingredients.index') }}"
                @class([
                    'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                    'bg-orange-50 font-bold text-orange-700' => request()->routeIs('owner.ingredients.*'),
                    'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs('owner.ingredients.*'),
                ])
            >
                <span aria-hidden="true">▤</span>
                Bahan Baku
            </a>

            {{-- Resep Menu --}}
            <a
                href="{{ route('owner.recipes.index') }}"
                @class([
                    'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm lg:w-full',
                    'bg-orange-50 font-bold text-orange-700' => request()->routeIs('owner.recipes.*'),
                    'font-semibold text-stone-600 transition hover:bg-stone-50 hover:text-orange-700' => !request()->routeIs('owner.recipes.*'),
                ])
            >
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
                <span
                    aria-disabled="true"
                    title="Modul {{ $menu['module'] }} belum tersedia"
                    class="flex shrink-0 cursor-not-allowed items-center gap-3
                           rounded-xl px-4 py-3 text-sm font-medium
                           text-stone-400 lg:w-full"
                >
                    <span aria-hidden="true">
                        {{ $menu['icon'] }}
                    </span>

                    {{ $menu['label'] }}
                </span>
            @endforeach
        </nav>

        {{-- USER PROFILE CARD --}}
        <div
            class="relative border-t border-stone-100 p-4
                   lg:absolute lg:inset-x-0 lg:bottom-0"
        >
            {{-- TRIGGER CARD --}}
            <button
                type="button"
                @click="userProfileOpen = !userProfileOpen"
                :aria-expanded="userProfileOpen"
                aria-label="Buka profil pengguna"
                class="flex w-full items-center gap-3 rounded-xl
                       bg-stone-50 p-3 text-left transition
                       hover:bg-orange-50 focus:outline-none
                       focus:ring-2 focus:ring-orange-500"
            >
                {{-- Avatar --}}
                <div
                    class="grid h-10 w-10 shrink-0 place-items-center
                           rounded-full bg-orange-100 font-bold
                           text-orange-700"
                >
                    {{ \Illuminate\Support\Str::upper(
                        \Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)
                    ) }}
                </div>

                {{-- User Info --}}
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-stone-800">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-stone-500">
                        Owner
                    </p>
                </div>

                {{-- Chevron --}}
                <svg
                    class="h-5 w-5 shrink-0 text-stone-400 transition-transform"
                    :class="{ 'rotate-180': userProfileOpen }"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m19 9-7 7-7-7"
                    />
                </svg>
            </button>

            {{-- POPUP PROFIL DI SIDEBAR --}}
            <div
                x-cloak
                x-show="userProfileOpen"
                x-transition.origin.bottom
                @click.outside="userProfileOpen = false"
                class="absolute bottom-full left-4 right-4 z-40 mb-2
                       overflow-hidden rounded-2xl border
                       border-stone-200 bg-white shadow-xl"
            >
                {{-- Informasi Akun --}}
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
                               text-xs font-bold text-orange-700"
                    >
                        Owner
                    </span>
                </div>

                {{-- Tombol Logout --}}
                <div class="p-2">
                    <button
                        type="button"
                        @click="
                            userProfileOpen = false;
                            logoutModalOpen = true;
                        "
                        class="flex w-full items-center gap-3
                               rounded-xl px-3 py-3 text-sm
                               font-semibold text-red-600
                               transition hover:bg-red-50"
                    >
                        <svg
                            class="h-5 w-5"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 15l3-3m0 0-3-3m3 3H9"
                            />
                        </svg>

                        Keluar dari Akun
                    </button>
                </div>
            </div>
        </div>
    </aside>

    {{-- MODAL KONFIRMASI LOGOUT --}}
    {{-- Modal terpisah dari popup profil dan berada di tengah layar --}}
    <div
        x-cloak
        x-show="logoutModalOpen"
        x-transition.opacity
        @click.self="logoutModalOpen = false"
        class="fixed inset-0 z-[100] flex items-center justify-center
               bg-stone-950/50 p-4 backdrop-blur-sm"
    >
        <section
            x-show="logoutModalOpen"
            x-transition.scale
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="logout-title"
            aria-describedby="logout-description"
            class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl"
        >
            {{-- Icon --}}
            <div
                class="mx-auto grid h-14 w-14 place-items-center
                       rounded-full bg-red-50 text-red-600"
            >
                <svg
                    class="h-7 w-7"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 15l3-3m0 0-3-3m3 3H9"
                    />
                </svg>
            </div>

            {{-- Pesan Konfirmasi --}}
            <div class="mt-4 text-center">
                <h2
                    id="logout-title"
                    class="text-lg font-black text-stone-900"
                >
                    Keluar dari Akun?
                </h2>

                <p
                    id="logout-description"
                    class="mt-2 text-sm leading-6 text-stone-500"
                >
                    Apakah kamu yakin ingin keluar dari akun

                    <span class="font-bold text-stone-700">
                        {{ auth()->user()->name }}
                    </span>?

                    Kamu harus login kembali untuk mengakses panel Owner.
                </p>
            </div>

            {{-- Tombol Konfirmasi --}}
            <div class="mt-6 grid grid-cols-2 gap-3">
                {{-- Batal --}}
                <button
                    type="button"
                    @click="logoutModalOpen = false"
                    class="rounded-xl border border-stone-200
                           px-4 py-3 text-sm font-bold
                           text-stone-600 transition hover:bg-stone-50"
                >
                    Batal
                </button>

                {{-- Ya, Keluar --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-red-600
                               px-4 py-3 text-sm font-bold text-white
                               transition hover:bg-red-700
                               focus:outline-none focus:ring-2
                               focus:ring-red-500 focus:ring-offset-2"
                    >
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>