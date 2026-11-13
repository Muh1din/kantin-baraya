@php
    $isEditing = isset($reward);
    $formAction = $isEditing ? route('owner.rewards.update', $reward) : route('owner.rewards.store');
@endphp

@if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <p class="font-bold">Reward belum dapat disimpan:</p>
        <ul class="mt-1 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <label for="menu_id" class="mb-2 block text-sm font-semibold text-stone-700">Menu gratis <span class="text-red-600">*</span></label>
        <select id="menu_id" name="menu_id" required
            class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            <option value="">Pilih menu</option>
            @foreach ($menus as $menu)
                <option value="{{ $menu->id }}" @selected((string) old('menu_id', $reward->menu_id ?? '') === (string) $menu->id)>
                    {{ $menu->name }} — Rp{{ number_format($menu->price, 0, ',', '.') }} (stok {{ $menu->stock }})
                </option>
            @endforeach
        </select>
        @if ($menus->isEmpty())
            <p class="mt-1 text-xs text-amber-700">Belum ada menu aktif yang tersedia untuk dijadikan reward.</p>
        @endif
    </div>
    <div>
        <label for="points_required" class="mb-2 block text-sm font-semibold text-stone-700">Poin untuk ditukar <span class="text-red-600">*</span></label>
        <input id="points_required" name="points_required" type="number" min="1" step="1" required
            value="{{ old('points_required', $reward->points_required ?? '') }}"
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>
    <div>
        <label class="inline-flex items-center gap-2 text-sm font-medium text-stone-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $reward->is_active ?? true))
                class="rounded border-stone-300 text-orange-600 focus:ring-orange-500">
            Reward aktif dan dapat ditukar
        </label>
    </div>
    <div class="sm:col-span-2">
        <button type="submit" @disabled($menus->isEmpty())
            class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-stone-300">
            {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Reward' }}
        </button>
        @if ($isEditing)
            <a href="{{ route('owner.rewards.index') }}" class="ml-2 inline-flex rounded-xl border border-stone-300 px-5 py-2.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Batal</a>
        @endif
    </div>
</form>
