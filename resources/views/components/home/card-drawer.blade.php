<div id="cartOverlay" class="cart-overlay fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm" aria-hidden="true">
    <div id="cartDrawer"
        class="cart-drawer absolute bottom-0 right-0 flex h-[90dvh] w-full max-w-md translate-y-full flex-col rounded-t-3xl bg-white shadow-2xl sm:bottom-0 sm:top-0 sm:h-full sm:translate-x-full sm:translate-y-0 sm:rounded-none"
        role="dialog" aria-modal="true" aria-labelledby="cartTitle">
        {{-- HEADER --}}
        <div class="flex items-center justify-between border-b border-stone-100 p-5">
            <div>
                <h2 id="cartTitle" class="text-lg font-black">
                    Keranjang Saya
                </h2>

                <p class="mt-1 text-xs text-stone-500">
                    Periksa pilihan menu kamu.
                </p>
            </div>

            <button id="closeCart" type="button"
                class="grid h-10 w-10 place-items-center rounded-xl bg-stone-100 text-xl transition hover:bg-stone-200"
                aria-label="Tutup keranjang">
                &times;
            </button>
        </div>

        {{-- DAFTAR MENU --}}
        <div id="cartItems" class="flex-1 space-y-4 overflow-y-auto p-5">
            {{-- Isi keranjang akan ditampilkan di sini --}}
        </div>

        {{-- FOOTER --}}
        <div class="border-t border-stone-100 bg-white p-5">
            <div class="flex items-center justify-between text-sm text-stone-500">
                <span>Subtotal</span>
                <span id="cartSubtotal">Rp0</span>
            </div>

            <div class="mt-2 flex items-center justify-between">
                <span class="font-extrabold">Total</span>

                <span id="cartTotal" class="text-xl font-black text-orange-600">
                    Rp0
                </span>
            </div>

            <button id="checkoutButton" type="button"
                class="mt-5 w-full rounded-xl bg-orange-500 py-4 text-sm font-extrabold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-stone-300">
                Lanjutkan Pesanan
            </button>

            <p class="mt-3 text-center text-[10px] text-stone-400">
                Kantin Baraya · Pesan makanan favoritmu
            </p>
        </div>
    </div>
</div>

{{-- Checkout Modal --}}
<div id="checkoutOverlay"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
    aria-hidden="true">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-stone-200 px-6 py-5">
            <div>
                <h2 class="text-xl font-bold text-stone-900">Checkout Pesanan</h2>
                <p class="mt-1 text-sm text-stone-500">
                    Lengkapi informasi pesananmu.
                </p>
            </div>

            <button type="button" id="closeCheckout"
                class="flex h-9 w-9 items-center justify-center rounded-full text-2xl text-stone-500 transition hover:bg-stone-100 hover:text-stone-900"
                aria-label="Tutup checkout">
                &times;
            </button>
        </div>

        {{-- Form --}}
        <form id="checkoutForm" class="max-h-[75vh] space-y-5 overflow-y-auto p-6">

            {{-- Nama --}}
            <div>
                <label for="customerName" class="mb-2 block text-sm font-semibold text-stone-700">
                    Nama Pemesan
                </label>
                <input type="text" id="customerName" name="customer_name" required autocomplete="name"
                    placeholder="Masukkan nama"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100">
            </div>
            {{-- Nomor HP --}}
            <div id="customerPhoneGroup">
                <label for="customerPhone" class="mb-2 block text-sm font-semibold text-stone-700">
                    Nomor WhatsApp
                    <span id="phoneRequiredLabel" class="text-red-500">*</span>
                    <span id="phoneOptionalLabel" class="hidden font-normal text-stone-400">
                        (Opsional)
                    </span>
                </label>

                <input type="tel" id="customerPhone" name="customer_phone" autocomplete="tel"
                    placeholder="Contoh: 081234567890"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100">
            </div>

            {{-- Pilihan Waktu Pesanan --}}
            <div>
                <label for="orderMode" class="mb-2 block text-sm font-semibold text-stone-700">
                    Waktu Pemesanan
                </label>

                <select id="orderMode" name="order_mode" required
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100">
                    <option value="sekarang">Pesan Sekarang</option>
                    <option value="preorder">Preorder</option>
                </select>

                <p class="mt-2 text-xs text-stone-500">
                    Preorder digunakan untuk menentukan jadwal pengambilan pesanan.
                </p>
            </div>

            {{-- Jenis Pesanan: hanya Pesan Sekarang --}}
            <div id="orderTypeGroup">
                <label for="orderType" class="mb-2 block text-sm font-semibold text-stone-700">
                    Jenis Pesanan
                </label>

                <select id="orderType" name="order_type"
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100">
                    <option value="bungkus">Bungkus</option>
                    <option value="makan_di_tempat">Makan di Tempat</option>
                </select>
            </div>

            {{-- Jadwal Preorder --}}
            <div id="preorderSchedule" class="hidden space-y-4">

                <div>
                    <label for="pickupDate" class="mb-2 block text-sm font-semibold text-stone-700">
                        Tanggal Pengambilan
                    </label>

                    <input type="date" id="pickupDate" name="pickup_date"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100">
                </div>

                <div>
                    <label for="pickupTime" class="mb-2 block text-sm font-semibold text-stone-700">
                        Jam Pengambilan
                    </label>

                    <input type="time" id="pickupTime" name="pickup_time"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100">
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    Preorder wajib dibayar menggunakan QRIS. Pesanan diproses setelah pembayaran dikonfirmasi.
                </div>
            </div>

            {{-- Metode Pembayaran --}}
            <div>
                <label for="paymentMethod" class="mb-2 block text-sm font-semibold text-stone-700">
                    Metode Pembayaran
                </label>

                <div id="paymentMethodSelect">
                    <select id="paymentMethod" name="payment_method" required
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100">
                        <option value="tunai">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>

                {{-- Tampilan khusus preorder --}}
                <div id="paymentMethodFixed"
                    class="hidden w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                    <span class="font-semibold text-stone-700">QRIS</span>
                    <span class="ml-2 text-xs text-stone-500">Wajib untuk preorder</span>
                </div>

                <p id="paymentInfo" class="mt-2 text-xs text-stone-500">
                    Pilih metode pembayaran pesananmu.
                </p>
            </div>

            {{-- Catatan --}}
            <div>
                <label for="orderNotes" class="mb-2 block text-sm font-semibold text-stone-700">
                    Catatan <span class="font-normal text-stone-400">(Opsional)</span>
                </label>

                <textarea id="orderNotes" name="notes" rows="3" placeholder="Contoh: tidak pedas, sambal dipisah..."
                    class="w-full resize-none rounded-xl border border-stone-300 px-4 py-3 outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-100"></textarea>
            </div>

            {{-- Total --}}
            <div class="rounded-xl bg-stone-50 p-4">
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-stone-600">Total Pembayaran</span>
                    <span id="checkoutTotal" class="text-xl font-bold text-amber-700">
                        Rp0
                    </span>
                </div>
            </div>

            {{-- Tombol --}}
            <button type="submit"
                class="w-full rounded-xl bg-amber-600 px-5 py-3.5 font-bold text-white transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                Lanjutkan Pesanan
            </button>

        </form>
    </div>
</div>

{{-- Modal Pembayaran QRIS --}}
<div id="qrisOverlay"
    class="fixed inset-0 z-[110] hidden items-start justify-center overflow-y-auto overscroll-contain bg-black/60 p-3 backdrop-blur-sm sm:items-center sm:p-5"
    aria-hidden="true">
    <div
        class="my-auto max-h-[calc(100dvh-1.5rem)] w-full max-w-md overflow-y-auto overscroll-contain rounded-2xl bg-white shadow-2xl sm:max-h-[calc(100dvh-2.5rem)]">

        {{-- Header --}}
        <div
            class="sticky top-0 z-10 flex items-center justify-between border-b border-stone-200 bg-white px-4 py-4 sm:px-6 sm:py-5">
            <div>
                <h2 class="text-xl font-bold text-stone-900">
                    Pembayaran QRIS
                </h2>
                <p class="mt-1 text-sm text-stone-500">
                    Scan QRIS untuk menyelesaikan pembayaran.
                </p>
            </div>

            <button type="button" id="closeQris"
                class="flex h-9 w-9 items-center justify-center rounded-full text-2xl text-stone-500 transition hover:bg-stone-100"
                aria-label="Tutup pembayaran">
                &times;
            </button>
        </div>

        <div class="space-y-5 p-6">

            {{-- Total --}}
            <div class="rounded-xl bg-amber-50 p-4 text-center">
                <p class="text-sm text-stone-600">Total Pembayaran</p>

                <p id="qrisTotal" class="mt-1 text-2xl font-bold text-amber-700">
                    Rp0
                </p>
            </div>

            {{-- QRIS --}}
            <div class="rounded-xl border border-stone-200 p-4 text-center">

                <div id="qrisPlaceholder"
                    class="flex min-h-48 flex-col items-center justify-center rounded-xl bg-stone-100 p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-12 w-12 text-stone-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 4.5h6.75v6.75H3.75zM13.5 4.5h6.75v6.75H13.5zM3.75 13.5h6.75v6.75H3.75zM15 13.5h1.5V15H15zM18 13.5h2.25v2.25H18zM13.5 17.25H15v1.5h-1.5zM16.5 17.25h3.75v3H16.5z" />
                    </svg>

                    <p class="text-sm font-semibold text-stone-600">
                        QRIS belum tersedia
                    </p>

                    <p class="mt-1 text-xs text-stone-500">
                        QRIS asli akan diatur melalui dashboard Owner.
                    </p>
                </div>

                <img id="qrisImage" alt="Kode QRIS pembayaran"
                    class="mx-auto hidden max-h-64 w-full max-w-64 object-contain">

                <button type="button" id="downloadQris" disabled
                    class="mt-4 w-full rounded-xl border border-amber-600 px-4 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:cursor-not-allowed disabled:border-stone-300 disabled:text-stone-400">
                    Download QRIS
                </button>

                <p id="qrisImageInfo" class="mt-2 text-xs text-stone-500">
                    QRIS dapat diunduh setelah Owner mengatur gambar pembayaran.
                </p>

            </div>

            {{-- Instruksi --}}
            <div class="rounded-xl border border-stone-200 p-4">
                <h3 class="font-semibold text-stone-800">
                    Instruksi Pembayaran
                </h3>

                <ol class="mt-2 list-inside list-decimal space-y-1 text-sm text-stone-600">
                    <li>Buka aplikasi pembayaran yang mendukung QRIS.</li>
                    <li>Scan kode QRIS yang tersedia.</li>
                    <li>Pastikan nominal pembayaran sesuai.</li>
                    <li>Upload bukti pembayaran di bawah ini.</li>
                </ol>
            </div>

            {{-- Upload Bukti --}}
            <div>
                <label for="paymentProof" class="mb-2 block text-sm font-semibold text-stone-700">
                    Bukti Pembayaran
                </label>

                <input type="file" id="paymentProof" accept="image/jpeg,image/png,image/webp"
                    class="block w-full rounded-xl border border-stone-300 text-sm text-stone-600
                           file:mr-4 file:border-0 file:bg-amber-50
                           file:px-4 file:py-3 file:font-semibold file:text-amber-700
                           hover:file:bg-amber-100">

                <p id="paymentProofInfo" class="mt-2 text-xs text-stone-500">
                    Format JPG, PNG, atau WebP. Maksimal 5 MB.
                </p>
            </div>

            {{-- Tombol --}}
            <div class="space-y-3">
                <button type="button" id="confirmQrisPayment" disabled
                    class="w-full rounded-xl bg-amber-600 px-5 py-3.5 font-bold text-white
                           transition hover:bg-amber-700 disabled:cursor-not-allowed
                           disabled:bg-stone-300">
                    Konfirmasi Pembayaran
                </button>

                <button type="button" id="backToCheckout"
                    class="w-full rounded-xl border border-stone-300 px-5 py-3 font-semibold text-stone-700 transition hover:bg-stone-50">
                    Kembali ke Checkout
                </button>
            </div>

        </div>
    </div>
</div>
