@php
    $isEditing = isset($voucher);
    $formAction = $isEditing ? route('owner.vouchers.update', $voucher) : route('owner.vouchers.store');
@endphp

@if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <p class="font-bold">Voucher belum dapat disimpan:</p>
        <ul class="mt-1 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <label for="code" class="mb-2 block text-sm font-semibold text-stone-700">Kode voucher <span class="text-red-600">*</span></label>
        <input id="code" name="code" value="{{ old('code', $voucher->code ?? '') }}" required maxlength="50" autocomplete="off"
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm uppercase outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>
    <div>
        <label for="name" class="mb-2 block text-sm font-semibold text-stone-700">Nama voucher <span class="text-red-600">*</span></label>
        <input id="name" name="name" value="{{ old('name', $voucher->name ?? '') }}" required maxlength="120"
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>
    <div>
        <label for="discount_type" class="mb-2 block text-sm font-semibold text-stone-700">Jenis diskon <span class="text-red-600">*</span></label>
        <select id="discount_type" name="discount_type" required
            class="w-full rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
            <option value="percentage" @selected(old('discount_type', $voucher->discount_type ?? 'percentage') === 'percentage')>Persentase (%)</option>
            <option value="fixed" @selected(old('discount_type', $voucher->discount_type ?? '') === 'fixed')>Potongan tetap (Rp)</option>
        </select>
    </div>
    <div>
        <label for="discount_value" class="mb-2 block text-sm font-semibold text-stone-700">Nilai diskon <span class="text-red-600">*</span></label>
        <input id="discount_value" name="discount_value" type="number" min="0.01" step="0.01" value="{{ old('discount_value', $voucher->discount_value ?? '') }}" required
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
        <p class="mt-1 text-xs text-stone-500">Diskon persentase maksimal 100%.</p>
    </div>
    <div>
        <label for="minimum_order" class="mb-2 block text-sm font-semibold text-stone-700">Minimum belanja (Rp)</label>
        <input id="minimum_order" name="minimum_order" type="number" min="0" step="0.01" value="{{ old('minimum_order', $voucher->minimum_order ?? '0') }}" required
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>
    <div>
        <label for="usage_limit" class="mb-2 block text-sm font-semibold text-stone-700">Batas penggunaan</label>
        <input id="usage_limit" name="usage_limit" type="number" min="1" step="1" value="{{ old('usage_limit', $voucher->usage_limit ?? '') }}" placeholder="Kosong = tanpa batas"
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>
    <div>
        <label for="starts_at" class="mb-2 block text-sm font-semibold text-stone-700">Mulai berlaku</label>
        <input id="starts_at" name="starts_at" type="date" value="{{ old('starts_at', $voucher?->starts_at?->format('Y-m-d')) }}"
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>
    <div>
        <label for="ends_at" class="mb-2 block text-sm font-semibold text-stone-700">Berakhir pada</label>
        <input id="ends_at" name="ends_at" type="date" value="{{ old('ends_at', $voucher?->ends_at?->format('Y-m-d')) }}"
            class="w-full rounded-xl border border-stone-300 px-4 py-2.5 text-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
    </div>
    <div class="flex items-center pt-7">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-stone-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $voucher->is_active ?? true))
                class="rounded border-stone-300 text-orange-600 focus:ring-orange-500">
            Voucher aktif
        </label>
    </div>
    <div class="sm:col-span-2 lg:col-span-3">
        <button type="submit" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-orange-600">
            {{ $isEditing ? 'Simpan Perubahan' : 'Buat Voucher' }}
        </button>
        @if ($isEditing)
            <a href="{{ route('owner.vouchers.index') }}" class="ml-2 inline-flex rounded-xl border border-stone-300 px-5 py-2.5 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">
                Batal
            </a>
        @endif
    </div>
</form>
