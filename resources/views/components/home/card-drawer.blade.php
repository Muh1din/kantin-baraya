<div id="cartOverlay" class="overlay fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="cartDrawer"
        class="absolute bottom-0 right-0 flex h-[90dvh] w-full max-w-md flex-col rounded-t-3xl bg-white shadow-2xl sm:bottom-0 sm:top-0 sm:h-full sm:rounded-none"
        role="dialog" aria-modal="true" aria-labelledby="cartTitle">
        <div class="flex items-center justify-between border-b border-stone-100 p-5">
            <div>
                <h2 id="cartTitle" class="text-lg font-black">Keranjang Saya</h2>
                <p class="mt-1 text-xs text-stone-500">
                    Periksa pilihan menu kamu.
                </p>
            </div>

            <button id="closeCart"
                class="grid h-10 w-10 place-items-center rounded-xl bg-stone-100 text-xl transition hover:bg-stone-200"
                aria-label="Tutup keranjang">
                ×
            </button>
        </div>

        <div id="cartItems" class="flex-1 space-y-4 overflow-y-auto p-5"></div>

        <div class="border-t border-stone-100 bg-white p-5">
            <div class="flex items-center justify-between text-sm text-stone-500">
                <span>Subtotal</span>
                <span id="cartSubtotal">Rp0</span>
            </div>

            <div class="mt-2 flex items-center justify-between">
                <span class="font-extrabold">Total</span>
                <span id="cartTotal" class="text-xl font-black text-orange-600">Rp0</span>
            </div>

            <button id="checkoutButton" type="button"
                class="mt-5 w-full rounded-xl bg-orange-500 py-4 text-sm font-extrabold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-stone-300">
                Lanjutkan Pesanan
            </button>

            <p class="mt-3 text-center text-[10px] text-stone-400">
                Mockup demo · Tidak ada pembayaran sungguhan
            </p>
        </div>
    </div>
</div>
