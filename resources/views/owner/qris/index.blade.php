<x-layouts.owner title="Pengaturan QRIS">
    <div class="max-w-3xl space-y-6">
        <header>
            <p class="mt-1 text-sm text-stone-500">Gambar ini ditampilkan kepada customer saat memilih pembayaran QRIS.
            </p>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
                role="status">{{ session('status') }}</div>
        @endif
        @error('qris_image')
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                {{ $message }}</div>
        @enderror

        <section class="space-y-5 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="rounded-xl bg-stone-50 p-4 text-center">
                @if ($setting?->qris_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($setting->qris_image_path) }}"
                        alt="QRIS pembayaran saat ini" class="mx-auto max-h-96 max-w-full object-contain">
                    <p class="mt-3 text-xs text-stone-500">Gambar QRIS aktif saat ini</p>
                @else
                    <p class="py-16 text-sm text-stone-500">Belum ada gambar QRIS yang diatur.</p>
                @endif
            </div>

            <form method="POST" action="{{ route('owner.qris.update') }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                <div>
                    <label for="qris_image"
                        class="mb-2 block text-sm font-semibold text-stone-700">{{ $setting?->qris_image_path ? 'Ganti gambar QRIS' : 'Unggah gambar QRIS' }}</label>
                    <input id="qris_image" name="qris_image" type="file" accept="image/jpeg,image/png,image/webp"
                        required
                        class="block w-full rounded-xl border border-stone-300 px-3 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-orange-50 file:px-3 file:py-2 file:font-semibold file:text-orange-700">
                    <p class="mt-2 text-xs text-stone-500">Format JPG, PNG, atau WebP; ukuran maksimal 5 MB.</p>
                </div>
                <button type="submit"
                    class="rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">Simpan
                    QRIS</button>
            </form>

            @if ($setting?->qris_image_path)
                <form method="POST" action="{{ route('owner.qris.destroy') }}"
                    onsubmit="return confirm('Hapus gambar QRIS saat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="rounded-xl border border-red-200 px-5 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-50">Hapus
                        QRIS</button>
                </form>
            @endif
        </section>
    </div>
</x-layouts.owner>
