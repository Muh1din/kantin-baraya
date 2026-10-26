<x-layouts.login title="POS Kasir">
    <main class="min-h-screen bg-orange-50/60 px-4 py-8 sm:px-6">
        <div class="mx-auto max-w-7xl">
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-gradient-to-br from-orange-500 via-orange-500 to-amber-400 p-6 text-white shadow-lg">
                <div><p class="text-sm font-semibold text-white/80">Kantin Baraya</p><h1 class="mt-1 text-3xl font-black">POS Kasir</h1><p class="mt-1 text-sm">Buat pesanan langsung dan catat pembayarannya.</p></div>
                <div class="flex flex-wrap items-center gap-3">@include('cashier.pos._navigation')<form action="{{ route('logout') }}" method="POST">@csrf<button class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-orange-700">Keluar</button></form></div>
            </header>

            @if ($errors->any())
                <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><p class="font-bold">Transaksi belum diproses.</p><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                <section class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-xl font-extrabold">Menu tersedia</h2><input id="menu-search" type="search" placeholder="Cari menu..." class="w-full rounded-xl border border-stone-200 px-4 py-2.5 sm:w-64"></div>
                    <div id="menu-list" class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse ($menus as $menu)
                            <article class="menu-card flex min-h-36 flex-col justify-between rounded-2xl border border-stone-200 p-4" data-search="{{ mb_strtolower($menu->name) }}">
                                <div><h3 class="font-bold text-stone-800">{{ $menu->name }}</h3><p class="mt-1 font-semibold text-orange-700">Rp {{ number_format($menu->price, 0, ',', '.') }}</p></div>
                                <button type="button" class="add-menu mt-4 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-orange-600" data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}">Tambah ke keranjang</button>
                            </article>
                        @empty
                            <p class="col-span-full rounded-2xl bg-stone-50 p-6 text-stone-600">Belum ada menu yang tersedia.</p>
                        @endforelse
                    </div>
                    <p id="no-results" class="mt-4 hidden rounded-xl bg-stone-50 p-4 text-sm text-stone-600">Menu tidak ditemukan.</p>
                </section>

                <section class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-xl font-extrabold">Keranjang</h2>
                    <p class="mt-1 text-sm text-stone-500">Pastikan jumlah dan pembayaran sebelum checkout.</p>
                    <form id="checkout-form" class="mt-5" method="POST" action="{{ route('kasir.pos.checkout') }}">
                        @csrf
                        <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
                        <label class="mt-4 block text-sm font-bold text-stone-700">Nama pelanggan
                            <input name="customer_name" value="{{ old('customer_name') }}" maxlength="255" required class="mt-2 w-full rounded-xl border border-stone-200 px-4 py-3">
                        </label>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <label class="text-sm font-bold text-stone-700">Jenis pesanan
                                <select id="order_type" name="order_type" class="mt-2 w-full rounded-xl border border-stone-200 px-4 py-3"><option value="makan_di_tempat">Makan di tempat</option><option value="bungkus">Bungkus</option></select>
                            </label>
                            <label class="text-sm font-bold text-stone-700">Waktu pembayaran
                                <select id="payment_timing" name="payment_timing" class="mt-2 w-full rounded-xl border border-stone-200 px-4 py-3"><option value="now">Bayar sekarang</option><option value="after_meal">Bayar setelah makan</option></select>
                            </label>
                        </div>
                        <label class="mt-4 block text-sm font-bold text-stone-700">Metode pembayaran
                            <select id="payment_method" name="payment_method" class="mt-2 w-full rounded-xl border border-stone-200 px-4 py-3"><option value="cash">Tunai</option><option value="qris">QRIS (menunggu verifikasi kasir)</option></select>
                        </label>
                        <div id="cart-items" class="max-h-[42vh] space-y-3 overflow-y-auto"></div>
                        <p id="empty-cart" class="rounded-xl bg-stone-50 p-5 text-sm text-stone-600">Keranjang masih kosong.</p>
                        <div id="cart-hidden-fields"></div>
                        <div class="mt-5 border-t border-stone-200 pt-4">
                            <div class="flex justify-between text-lg font-extrabold"><span>Total</span><span id="cart-total">Rp 0</span></div>
                            <label id="cash-label" for="cash_received" class="mt-5 block text-sm font-bold text-stone-700">Uang diterima (Rp)</label>
                            <input id="cash_received" name="cash_received" type="number" min="0" step="1" inputmode="numeric" placeholder="Contoh: 50000" value="{{ old('cash_received') }}" class="mt-2 w-full rounded-xl border border-stone-200 px-4 py-3" required>
                            <div class="mt-3 flex justify-between text-sm"><span class="text-stone-600">Perkiraan kembalian</span><span id="change-estimate" class="font-bold">Rp 0</span></div>
                            <button id="checkout-button" type="submit" disabled class="mt-5 w-full rounded-xl bg-orange-500 px-5 py-3.5 font-extrabold text-white hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-stone-300">Buat Pesanan</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>
    <script>
        (() => {
            const cart = new Map();
            const formatRupiah = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            const cartItems = document.getElementById('cart-items');
            const hiddenFields = document.getElementById('cart-hidden-fields');
            const cashInput = document.getElementById('cash_received');
            const totalElement = document.getElementById('cart-total');
            const paymentMethod = document.getElementById('payment_method');
            const paymentTiming = document.getElementById('payment_timing');
            const orderType = document.getElementById('order_type');
            const checkoutButton = document.getElementById('checkout-button');
            const syncPaymentInputs = () => {
                const cashNow = paymentMethod.value === 'cash' && paymentTiming.value === 'now';
                cashInput.required = cashNow;
                cashInput.disabled = !cashNow;
                document.getElementById('cash-label').classList.toggle('hidden', !cashNow);
                document.getElementById('change-estimate').parentElement.classList.toggle('hidden', !cashNow);
                checkoutButton.textContent = paymentMethod.value === 'qris' ? 'Buat Pesanan QRIS' : (paymentTiming.value === 'after_meal' ? 'Buat Pesanan, Bayar Nanti' : 'Checkout Tunai');
                if (paymentTiming.value === 'after_meal') orderType.value = 'makan_di_tempat';
                orderType.querySelector('option[value="bungkus"]').disabled = paymentTiming.value === 'after_meal';
            };
            const render = () => {
                cartItems.replaceChildren(); hiddenFields.replaceChildren();
                let total = 0; let index = 0;
                for (const [id, item] of cart) {
                    const subtotal = item.price * item.quantity; total += subtotal;
                    const row = document.createElement('div'); row.className = 'rounded-2xl border border-stone-200 p-3';
                    const top = document.createElement('div'); top.className = 'flex items-start justify-between gap-2';
                    const info = document.createElement('div');
                    const name = document.createElement('p'); name.className = 'font-bold'; name.textContent = item.name;
                    const price = document.createElement('p'); price.className = 'text-sm text-stone-500'; price.textContent = formatRupiah(item.price) + ' / item';
                    info.append(name, price);
                    const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'text-sm font-bold text-red-600'; remove.textContent = 'Hapus'; remove.addEventListener('click', () => { cart.delete(id); render(); });
                    top.append(info, remove);
                    const bottom = document.createElement('div'); bottom.className = 'mt-3 flex items-center justify-between gap-3';
                    const qty = document.createElement('input'); qty.type = 'number'; qty.min = '1'; qty.step = '1'; qty.value = item.quantity; qty.setAttribute('aria-label', 'Jumlah ' + item.name); qty.className = 'w-20 rounded-lg border border-stone-200 px-2 py-1.5';
                    qty.addEventListener('change', () => { const value = Number(qty.value); if (!Number.isInteger(value) || value < 1) cart.delete(id); else item.quantity = value; render(); });
                    const line = document.createElement('span'); line.className = 'font-bold'; line.textContent = formatRupiah(subtotal);
                    bottom.append(qty, line); row.append(top, bottom); cartItems.append(row);
                    for (const [key, value] of Object.entries({ menu_id: id, quantity: item.quantity })) { const input = document.createElement('input'); input.type = 'hidden'; input.name = `items[${index}][${key}]`; input.value = value; hiddenFields.append(input); }
                    index++;
                }
                totalElement.textContent = formatRupiah(total);
                document.getElementById('empty-cart').classList.toggle('hidden', cart.size > 0);
                document.getElementById('checkout-button').disabled = cart.size === 0;
                const received = Number(cashInput.value);
                document.getElementById('change-estimate').textContent = formatRupiah(Number.isFinite(received) ? Math.max(0, received - total) : 0);
            };
            document.querySelectorAll('.add-menu').forEach(button => button.addEventListener('click', () => {
                const item = cart.get(button.dataset.id);
                if (item) item.quantity++;
                else cart.set(button.dataset.id, { name: button.dataset.name, price: Number(button.dataset.price), quantity: 1 });
                render();
            }));
            document.getElementById('menu-search').addEventListener('input', event => {
                const query = event.target.value.trim().toLocaleLowerCase('id'); let visible = 0;
                document.querySelectorAll('.menu-card').forEach(card => { const matches = card.dataset.search.includes(query); card.classList.toggle('hidden', !matches); if (matches) visible++; });
                document.getElementById('no-results').classList.toggle('hidden', visible > 0);
            });
            cashInput.addEventListener('input', () => { cashInput.setCustomValidity(''); render(); });
            document.getElementById('checkout-form').addEventListener('submit', event => {
                const total = [...cart.values()].reduce((sum, item) => sum + item.price * item.quantity, 0);
                if (cart.size === 0 || (cashInput.required && (!Number.isSafeInteger(Number(cashInput.value)) || Number(cashInput.value) < total))) { event.preventDefault(); cashInput.setCustomValidity('Masukkan uang yang cukup untuk membayar total transaksi.'); cashInput.reportValidity(); }
                else cashInput.setCustomValidity('');
            });
            [paymentMethod, paymentTiming, orderType].forEach(input => input.addEventListener('change', syncPaymentInputs));
            syncPaymentInputs();
            render();
        })();
    </script>
</x-layouts.login>
