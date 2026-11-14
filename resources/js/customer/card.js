const CART_STORAGE_KEY = "kantin-baraya-cart-v1";

let currentOrderToken = null;

function initializeCart() {
    // ========================================
    // 1. ELEMEN KERANJANG
    // ========================================

    const cartButton = document.getElementById("cartButton");
    const cartOverlay = document.getElementById("cartOverlay");
    const closeCartButton = document.getElementById("closeCart");
    const cartDrawer = document.getElementById("cartDrawer");
    const cartItems = document.getElementById("cartItems");
    const cartCount = document.getElementById("cartCount");
    const cartSubtotal = document.getElementById("cartSubtotal");
    const cartTotal = document.getElementById("cartTotal");
    const cartDiscountRow = document.getElementById("cartDiscountRow");
    const cartDiscountLabel = document.getElementById("cartDiscountLabel");
    const cartDiscount = document.getElementById("cartDiscount");
    const checkoutButton = document.getElementById("checkoutButton");

    // ========================================
    // 2. ELEMEN CHECKOUT
    // ========================================

    const checkoutOverlay = document.getElementById("checkoutOverlay");
    const closeCheckout = document.getElementById("closeCheckout");
    const checkoutForm = document.getElementById("checkoutForm");
    const checkoutTotal = document.getElementById("checkoutTotal");
    const voucherCodeInput = document.getElementById("voucherCode");

    const customerName = document.getElementById("customerName");
    const customerPhone = document.getElementById("customerPhone");
    const pickupDate = document.getElementById("pickupDate");
    const pickupTime = document.getElementById("pickupTime");
    const paymentMethod = document.getElementById("paymentMethod");

    // ========================================
    // 3. ELEMEN QRIS
    // ========================================

    const qrisOverlay = document.getElementById("qrisOverlay");
    const closeQris = document.getElementById("closeQris");
    const backToCheckout = document.getElementById("backToCheckout");
    const qrisTotal = document.getElementById("qrisTotal");
    const qrisDiscount = document.getElementById("qrisDiscount");

    const paymentProof = document.getElementById("paymentProof");
    const paymentProofInfo = document.getElementById("paymentProofInfo");
    const confirmQrisPayment = document.getElementById("confirmQrisPayment");

    const qrisImage = document.getElementById("qrisImage");
    const qrisPlaceholder = document.getElementById("qrisPlaceholder");
    const downloadQris = document.getElementById("downloadQris");
    const qrisImageInfo = document.getElementById("qrisImageInfo");

    // ========================================
    // 4. VALIDASI ELEMEN UTAMA
    // ========================================

    if (
        !cartButton ||
        !cartOverlay ||
        !closeCartButton ||
        !cartDrawer ||
        !cartItems
    ) {
        return;
    }

    if (cartButton.dataset.cartInitialized === "true") {
        return;
    }

    cartButton.dataset.cartInitialized = "true";

    let cart = readCart();
    let closeTimer;
    let automaticVouchers = [];
    try {
        const encodedVouchers = document.querySelector("#menu")?.dataset.autoVouchers;
        if (encodedVouchers) automaticVouchers = JSON.parse(atob(encodedVouchers));
    } catch {
        automaticVouchers = [];
    }

    // ========================================
    // 5. LOCAL STORAGE
    // ========================================

    function readCart() {
        try {
            const storedCart = JSON.parse(
                localStorage.getItem(CART_STORAGE_KEY) ?? "{}",
            );

            if (
                storedCart &&
                typeof storedCart === "object" &&
                !Array.isArray(storedCart)
            ) {
                return storedCart;
            }

            return {};
        } catch {
            return {};
        }
    }

    function persistCart() {
        try {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
        } catch {
            // Keranjang tetap bisa digunakan selama halaman terbuka.
        }
    }

    // ========================================
    // 6. FORMAT HARGA DAN JUMLAH
    // ========================================

    function formatPrice(value) {
        return `Rp${new Intl.NumberFormat("id-ID").format(value)}`;
    }

    function totalQuantity() {
        return Object.values(cart).reduce(
            (total, item) => total + item.quantity,
            0,
        );
    }

    function calculateSubtotal() {
        return Object.values(cart).reduce(
            (total, item) => total + item.price * item.quantity,
            0,
        );
    }

    function automaticDiscountFor(subtotal) {
        const voucher = automaticVouchers
            .filter((item) => Number(item.minimum) <= subtotal)
            .sort((a, b) => Number(b.percent) - Number(a.percent))[0];
        if (!voucher) return { voucher: null, amount: 0 };
        return {
            voucher,
            amount: Math.min(subtotal, Math.round(subtotal * Number(voucher.percent)) / 100),
        };
    }

    // ========================================
    // 7. UPDATE TOMBOL MENU
    // ========================================

    function updateProductButtons() {
        document
            .querySelectorAll('.product-card [data-action="add"]')
            .forEach((button) => {
                const quantity = cart[button.dataset.id]?.quantity ?? 0;

                button.textContent =
                    quantity > 0
                        ? `✓ Di keranjang (${quantity})`
                        : "+ Tambah ke Keranjang";

                button.classList.toggle("bg-orange-50", quantity > 0);
                button.classList.toggle("text-orange-600", quantity > 0);
                button.classList.toggle("bg-orange-500", quantity === 0);
                button.classList.toggle("text-white", quantity === 0);
            });
    }

    // ========================================
    // 8. TOMBOL JUMLAH MENU
    // ========================================

    function createQuantityButton(label, text, action, id) {
        const button = document.createElement("button");

        button.type = "button";
        button.dataset.cartAction = action;
        button.dataset.id = id;

        button.className =
            "grid h-8 w-8 place-items-center rounded-lg bg-stone-100 text-lg font-bold transition hover:bg-orange-100 hover:text-orange-600";

        button.setAttribute("aria-label", label);
        button.textContent = text;

        return button;
    }

    // ========================================
    // 9. RENDER KERANJANG
    // ========================================

    function renderCart() {
        cartItems.replaceChildren();

        const entries = Object.entries(cart);
        const quantity = totalQuantity();
        const subtotal = calculateSubtotal();
        const promotion = automaticDiscountFor(subtotal);
        const total = Math.max(0, subtotal - promotion.amount);

        if (entries.length === 0) {
            const emptyState = document.createElement("div");

            emptyState.className =
                "grid h-full min-h-48 place-items-center text-center";

            emptyState.innerHTML = `
                <div>
                    <span class="text-4xl" aria-hidden="true">🛒</span>
                    <p class="mt-3 text-sm font-bold text-stone-700">
                        Keranjang masih kosong
                    </p>
                    <p class="mt-1 text-xs text-stone-500">
                        Yuk, pilih menu favoritmu.
                    </p>
                </div>
            `;

            cartItems.append(emptyState);
        } else {
            entries.forEach(([id, item]) => {
                const row = document.createElement("article");

                row.className =
                    "flex items-center gap-3 rounded-2xl border border-stone-100 p-3";

                const image = document.createElement("div");

                image.className =
                    "grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-xl bg-stone-100 text-2xl";

                if (item.image) {
                    const img = document.createElement("img");

                    img.src = item.image;
                    img.alt = "";
                    img.className = "h-full w-full object-cover";

                    img.onerror = () => {
                        img.remove();
                        image.textContent = "🍽️";
                    };

                    image.append(img);
                } else {
                    image.textContent = "🍽️";
                }

                const details = document.createElement("div");
                details.className = "min-w-0 flex-1";

                const name = document.createElement("p");
                name.className = "truncate text-sm font-bold text-stone-800";
                name.textContent = item.name;

                const price = document.createElement("p");
                price.className = "mt-1 text-xs font-semibold text-orange-600";
                price.textContent = formatPrice(item.price);

                details.append(name, price);

                const controls = document.createElement("div");
                controls.className = "flex items-center gap-2";

                controls.append(
                    createQuantityButton("Kurangi jumlah", "−", "decrease", id),
                );

                const count = document.createElement("span");
                count.className = "min-w-5 text-center text-sm font-bold";
                count.textContent = String(item.quantity);

                controls.append(
                    count,
                    createQuantityButton("Tambah jumlah", "+", "increase", id),
                );

                row.append(image, details, controls);
                cartItems.append(row);
            });
        }

        if (cartCount) {
            cartCount.textContent = String(quantity);
            cartCount.classList.toggle("cart-count-pop", quantity > 0);
        }

        if (cartSubtotal) {
            cartSubtotal.textContent = formatPrice(subtotal);
        }

        if (cartTotal) {
            cartTotal.textContent = formatPrice(total);
        }

        if (cartDiscountRow && cartDiscount) {
            cartDiscountRow.classList.toggle("hidden", promotion.amount <= 0);
            cartDiscountRow.classList.toggle("flex", promotion.amount > 0);
            cartDiscount.textContent = `-${formatPrice(promotion.amount)}`;
            if (cartDiscountLabel && promotion.voucher) {
                cartDiscountLabel.textContent = `Diskon otomatis ${promotion.voucher.percent}%`;
            }
        }

        if (checkoutButton) {
            checkoutButton.disabled = entries.length === 0;
        }

        updateProductButtons();
    }

    // ========================================
    // 10. BUKA DAN TUTUP KERANJANG
    // ========================================

    function openCart() {
        window.clearTimeout(closeTimer);

        cartOverlay.classList.remove("hidden");
        cartOverlay.setAttribute("aria-hidden", "false");

        cartButton.setAttribute("aria-expanded", "true");

        document.body.classList.add("overflow-hidden");

        requestAnimationFrame(() => {
            cartOverlay.classList.add("cart-overlay-visible");

            cartDrawer.classList.remove(
                "translate-y-full",
                "sm:translate-x-full",
            );
        });

        closeCartButton.focus();
    }

    function closeCart() {
        cartOverlay.classList.remove("cart-overlay-visible");

        cartDrawer.classList.add("translate-y-full", "sm:translate-x-full");

        cartOverlay.setAttribute("aria-hidden", "true");
        cartButton.setAttribute("aria-expanded", "false");

        document.body.classList.remove("overflow-hidden");

        closeTimer = window.setTimeout(() => {
            cartOverlay.classList.add("hidden");
        }, 220);
    }

    cartButton.addEventListener("click", openCart);
    closeCartButton.addEventListener("click", closeCart);

    cartOverlay.addEventListener("click", (event) => {
        if (event.target === cartOverlay) {
            closeCart();
        }
    });

    // ========================================
    // 11. CHECKOUT PREORDER
    // ========================================

    function updateCheckoutOptions() {
        // Semua pesanan Customer adalah preorder.
        // Jenis pesanan selalu bungkus.
        // Pembayaran selalu QRIS.

        if (paymentMethod) {
            paymentMethod.value = "qris";
        }

        if (pickupDate) {
            pickupDate.required = true;

            const today = new Date();

            const localToday = [
                today.getFullYear(),
                String(today.getMonth() + 1).padStart(2, "0"),
                String(today.getDate()).padStart(2, "0"),
            ].join("-");

            pickupDate.min = localToday;
        }

        if (pickupTime) {
            pickupTime.required = true;
        }

        if (customerPhone) {
            customerPhone.required = true;
        }
    }

    function openCheckout() {
        if (!checkoutOverlay || !checkoutTotal) {
            console.error(
                "Checkout tidak ditemukan. Periksa ID checkoutOverlay dan checkoutTotal.",
            );

            return;
        }

        if (Object.keys(cart).length === 0) {
            return;
        }

        const subtotal = calculateSubtotal();
        const previewDiscount = voucherCodeInput?.value.trim()
            ? 0
            : automaticDiscountFor(subtotal).amount;
        checkoutTotal.textContent = formatPrice(Math.max(0, subtotal - previewDiscount));

        updateCheckoutOptions();

        checkoutOverlay.classList.remove("hidden");
        checkoutOverlay.classList.add("flex");

        checkoutOverlay.setAttribute("aria-hidden", "false");

        closeCart();
    }

    function closeCheckoutModal() {
        if (!checkoutOverlay) {
            return;
        }

        checkoutOverlay.classList.add("hidden");
        checkoutOverlay.classList.remove("flex");

        checkoutOverlay.setAttribute("aria-hidden", "true");
    }

    checkoutButton?.addEventListener("click", (event) => {
        event.preventDefault();
        openCheckout();
    });

    closeCheckout?.addEventListener("click", closeCheckoutModal);

    checkoutOverlay?.addEventListener("click", (event) => {
        if (event.target === checkoutOverlay) {
            closeCheckoutModal();
        }
    });

    // ========================================
    // 12. SUBMIT CHECKOUT
    // ========================================

    checkoutForm?.addEventListener("submit", async (event) => {
        event.preventDefault();

        const entries = Object.entries(cart);

        if (entries.length === 0) {
            alert("Keranjang masih kosong.");
            return;
        }

        if (!pickupDate?.value || !pickupTime?.value) {
            alert("Silakan tentukan tanggal dan jam pengambilan.");
            return;
        }

        const formData = new FormData(checkoutForm);

        const items = entries.map(([id, item]) => ({
            id: Number(id),
            quantity: Number(item.quantity),
        }));

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;

        if (!csrfToken) {
            alert("Token keamanan tidak ditemukan. Muat ulang halaman.");
            return;
        }

        const submitButton = checkoutForm.querySelector('[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(checkoutForm.dataset.orderUrl, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    customer_name: formData.get("customer_name"),
                    customer_phone: formData.get("customer_phone"),

                    // Preorder selalu dibungkus.
                    order_type: "bungkus",

                    // Pembayaran selalu QRIS.
                    payment_method: "qris",

                    // Jadwal preorder.
                    pickup_date: pickupDate.value,
                    pickup_time: pickupTime.value,

                    voucher_code: formData.get("voucher_code")?.trim() || null,
                    notes: formData.get("notes"),
                    items,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                const message = result.errors
                    ? Object.values(result.errors).flat().join("\n")
                    : result.message || "Checkout gagal.";

                alert(message);
                return;
            }

            currentOrderToken = result.tracking_token;

            if (!qrisTotal || !qrisOverlay) {
                alert("Modal QRIS tidak ditemukan.");
                return;
            }

            const serverTotal = formatPrice(Number(result.total_amount));
            if (checkoutTotal) {
                checkoutTotal.textContent = serverTotal;
            }
            qrisTotal.textContent = serverTotal;

            const discountAmount = Number(result.discount_amount ?? 0);
            if (qrisDiscount) {
                if (discountAmount > 0) {
                    qrisDiscount.textContent = `${result.voucher_name || `Voucher ${result.voucher_code}`}: -${formatPrice(discountAmount)}`;
                    qrisDiscount.classList.remove("hidden");
                } else {
                    qrisDiscount.textContent = "";
                    qrisDiscount.classList.add("hidden");
                }
            }

            openQrisModal();
        } catch (error) {
            console.error("Checkout error:", error);

            alert("Tidak dapat menghubungi server. Silakan coba lagi.");
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });

    // ========================================
    // 13. TAMBAHKAN MENU KE KERANJANG
    // ========================================

    function addMenuToCart(card, button) {
        const id = card.dataset.productId;

        if (!id) {
            return;
        }

        const existing = cart[id];
        const stock = Number(card.dataset.stock ?? 0);
        const quantity = (existing?.quantity ?? 0) + 1;

        if (stock > 0 && quantity > stock) {
            button.animate(
                [
                    { transform: "translateX(0)" },
                    { transform: "translateX(-4px)" },
                    { transform: "translateX(4px)" },
                    { transform: "translateX(0)" },
                ],
                { duration: 220 },
            );

            return;
        }

        cart[id] = {
            name: card.dataset.menuName ?? "Menu",
            price: Number(card.dataset.price ?? 0),
            image: card.dataset.image ?? "",
            stock,
            quantity,
        };

        persistCart();
        renderCart();

        button.animate(
            [
                { transform: "scale(1)" },
                { transform: "scale(1.06)" },
                { transform: "scale(1)" },
            ],
            {
                duration: 260,
                easing: "ease-out",
            },
        );

        cartButton.animate(
            [
                { transform: "translateY(0)" },
                { transform: "translateY(-4px)" },
                { transform: "translateY(0)" },
            ],
            {
                duration: 320,
                easing: "ease-out",
            },
        );
    }

    // ========================================
    // 14. EVENT MENU DAN JUMLAH KERANJANG
    // ========================================

    document.addEventListener("click", (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const addButton = target.closest('.product-card [data-action="add"]');

        if (addButton) {
            const card = addButton.closest(".product-card");

            if (card) {
                addMenuToCart(card, addButton);
            }

            return;
        }

        const quantityButton = target.closest("[data-cart-action]");

        if (!quantityButton || !cart[quantityButton.dataset.id]) {
            return;
        }

        const id = quantityButton.dataset.id;

        if (quantityButton.dataset.cartAction === "increase") {
            const item = cart[id];

            if (item.stock > 0 && item.quantity >= item.stock) {
                return;
            }

            cart[id].quantity++;
        }

        if (quantityButton.dataset.cartAction === "decrease") {
            cart[id].quantity--;
        }

        if (cart[id].quantity <= 0) {
            delete cart[id];
        }

        persistCart();
        renderCart();
    });

    // ========================================
    // 15. TOMBOL ESCAPE
    // ========================================

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        if (checkoutOverlay && !checkoutOverlay.classList.contains("hidden")) {
            closeCheckoutModal();
            return;
        }

        if (qrisOverlay && !qrisOverlay.classList.contains("hidden")) {
            closeQrisModal();
            return;
        }

        if (!cartOverlay.classList.contains("hidden")) {
            closeCart();
        }
    });

    // ========================================
    // 16. MODAL QRIS
    // ========================================

    function openQrisModal() {
        if (!qrisOverlay || !checkoutOverlay) {
            return;
        }

        qrisOverlay.classList.remove("hidden");
        qrisOverlay.classList.add("flex");

        qrisOverlay.setAttribute("aria-hidden", "false");

        checkoutOverlay.classList.add("hidden");
        checkoutOverlay.classList.remove("flex");
    }

    function closeQrisModal() {
        if (!qrisOverlay) {
            return;
        }

        qrisOverlay.classList.add("hidden");
        qrisOverlay.classList.remove("flex");

        qrisOverlay.setAttribute("aria-hidden", "true");
    }

    closeQris?.addEventListener("click", closeQrisModal);

    backToCheckout?.addEventListener("click", () => {
        closeQrisModal();

        checkoutOverlay?.classList.remove("hidden");
        checkoutOverlay?.classList.add("flex");
    });

    // ========================================
    // 17. VALIDASI BUKTI PEMBAYARAN
    // ========================================

    if (paymentProof) {
        paymentProof.addEventListener("change", () => {
            const file = paymentProof.files[0];

            if (confirmQrisPayment) {
                confirmQrisPayment.disabled = true;
            }

            if (!file) {
                if (paymentProofInfo) {
                    paymentProofInfo.textContent =
                        "Format JPG, PNG, atau WebP. Maksimal 5 MB.";
                }

                return;
            }

            const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

            if (!allowedTypes.includes(file.type)) {
                paymentProof.value = "";

                if (paymentProofInfo) {
                    paymentProofInfo.textContent =
                        "Format file harus JPG, PNG, atau WebP.";
                }

                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                paymentProof.value = "";

                if (paymentProofInfo) {
                    paymentProofInfo.textContent = "Ukuran file maksimal 5 MB.";
                }

                return;
            }

            if (paymentProofInfo) {
                paymentProofInfo.textContent = `File dipilih: ${file.name}`;
            }

            if (confirmQrisPayment) {
                confirmQrisPayment.disabled = false;
            }
        });
    }

    // ========================================
    // 18. UPLOAD BUKTI PEMBAYARAN
    // ========================================

    confirmQrisPayment?.addEventListener("click", async () => {
        const file = paymentProof?.files[0];

        if (!currentOrderToken) {
            alert("Pesanan belum dibuat. Silakan checkout kembali.");
            return;
        }

        if (!file) {
            alert("Pilih bukti pembayaran terlebih dahulu.");
            return;
        }

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;

        if (!csrfToken) {
            alert("Token keamanan tidak ditemukan. Muat ulang halaman.");
            return;
        }

        const data = new FormData();
        data.append("payment_proof", file);

        confirmQrisPayment.disabled = true;

        try {
            const response = await fetch(
                checkoutForm.dataset.proofUrl.replace("__ORDER_TOKEN__", encodeURIComponent(currentOrderToken)),
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    body: data,
                },
            );

            const result = await response.json();

            if (!response.ok) {
                alert(result.message || "Upload bukti pembayaran gagal.");
                return;
            }

            alert(`${result.message}\nKode pesanan: ${result.order_code}`);

            closeQrisModal();
            closeCheckoutModal();

            currentOrderToken = null;
            paymentProof.value = "";

            if (paymentProofInfo) {
                paymentProofInfo.textContent =
                    "Format JPG, PNG, atau WebP. Maksimal 5 MB.";
            }

            cart = {};

            persistCart();
            renderCart();
        } catch (error) {
            console.error("Upload bukti pembayaran error:", error);

            alert("Upload gagal karena masalah koneksi. Silakan coba lagi.");
        } finally {
            confirmQrisPayment.disabled = false;
        }
    });

    // ========================================
    // 19. GAMBAR QRIS
    // ========================================

    function updateQrisImage() {
        const imageUrl = qrisImage?.getAttribute("src");

        const isConfigured =
            Boolean(imageUrl) &&
            qrisImage.complete &&
            qrisImage.naturalWidth > 0;

        qrisImage?.classList.toggle("hidden", !isConfigured);
        qrisPlaceholder?.classList.toggle("hidden", isConfigured);

        if (downloadQris) {
            downloadQris.disabled = !isConfigured;
        }

        if (qrisImageInfo) {
            qrisImageInfo.textContent = isConfigured
                ? "Simpan gambar QRIS untuk memudahkan pembayaran."
                : "QRIS dapat diunduh setelah Owner mengatur gambar pembayaran.";
        }
    }

    updateQrisImage();

    qrisImage?.addEventListener("load", updateQrisImage);
    qrisImage?.addEventListener("error", updateQrisImage);
    downloadQris?.addEventListener("click", () => {
        const imageUrl = qrisImage?.getAttribute("src");
        if (!imageUrl || downloadQris.disabled) return;

        const link = document.createElement("a");
        link.href = imageUrl;
        link.download = "kode-qris-kantin-baraya";
        document.body.appendChild(link);
        link.click();
        link.remove();
    });

    // ========================================
    // 20. INISIALISASI
    // ========================================

    updateCheckoutOptions();
    renderCart();
}

// ========================================
// JALANKAN KETIKA DOM SIAP
// ========================================

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeCart);
} else {
    initializeCart();
}
