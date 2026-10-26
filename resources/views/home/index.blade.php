<x-layouts.home>
    <x-home.hero />
    <x-home.info-strip />

    <section id="menu" class="px-4 pb-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-orange-600">Pilihan hari ini</p>
                    <h2 class="mt-1 text-2xl font-black text-stone-900 sm:text-3xl">Menu Kantin</h2>
                </div>
                <p class="text-sm text-stone-500">{{ $menus->count() }} menu tersedia</p>
            </div>

            @if ($menus->isEmpty())
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-12 text-center">
                    <p class="text-lg font-bold text-stone-800">Belum ada menu tersedia</p>
                    <p class="mt-2 text-sm text-stone-500">Silakan cek kembali nanti.</p>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($menus as $menu)
                        <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                            @if ($menu->image)
                                <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" class="h-48 w-full object-cover" loading="lazy">
                            @else
                                <div class="grid h-48 place-items-center bg-orange-50 text-5xl" aria-hidden="true">🍽️</div>
                            @endif
                            <div class="p-5">
                                <h3 class="text-lg font-extrabold text-stone-900">{{ $menu->name }}</h3>
                                @if ($menu->description)
                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-stone-600">{{ $menu->description }}</p>
                                @endif
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <span class="font-black text-orange-600">Rp{{ number_format($menu->price, 0, ',', '.') }}</span>
                                    <span class="text-xs font-medium text-stone-500">Stok: {{ $menu->stock }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.home>
