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
    const checkoutButton = document.getElementById("checkoutButton");

    // ========================================
    // 2. ELEMEN CHECKOUT
    // ========================================
    const checkoutOverlay = document.getElementById("checkoutOverlay");
    const closeCheckout = document.getElementById("closeCheckout");
    const checkoutForm = document.getElementById("checkoutForm");
    const checkoutTotal = document.getElementById("checkoutTotal");

    const orderMode = document.getElementById("orderMode");
    const orderTypeGroup = document.getElementById("orderTypeGroup");
    const orderType = document.getElementById("orderType");
    const preorderSchedule = document.getElementById("preorderSchedule");
    const pickupDate = document.getElementById("pickupDate");
    const pickupTime = document.getElementById("pickupTime");
    const paymentMethod = document.getElementById("paymentMethod");
    const paymentInfo = document.getElementById("paymentInfo");
    const customerPhoneGroup = document.getElementById("customerPhoneGroup");
    const phoneRequiredLabel = document.getElementById("phoneRequiredLabel");
    const phoneOptionalLabel = document.getElementById("phoneOptionalLabel");

    // Modal QRIS
    const qrisOverlay = document.getElementById("qrisOverlay");
    const closeQris = document.getElementById("closeQris");
    const backToCheckout = document.getElementById("backToCheckout");
    const qrisTotal = document.getElementById("qrisTotal");

    const paymentProof = document.getElementById("paymentProof");
    const paymentProofInfo = document.getElementById("paymentProofInfo");
    const confirmQrisPayment = document.getElementById("confirmQrisPayment");
    const qrisImage = document.getElementById("qrisImage");
    const qrisPlaceholder = document.getElementById("qrisPlaceholder");
    const downloadQris = document.getElementById("downloadQris");
    const qrisImageInfo = document.getElementById("qrisImageInfo");

    // Jangan jalankan fitur keranjang jika elemen utamanya tidak tersedia.
    if (
        !cartButton ||
        !cartOverlay ||
        !closeCartButton ||
        !cartDrawer ||
        !cartItems
    ) {
        return;
    }

    // Mencegah event listener terpasang berulang kali.
    if (cartButton.dataset.cartInitialized === "true") {
        return;
    }

    cartButton.dataset.cartInitialized = "true";

    let cart = readCart();
    let closeTimer;

    // ========================================
    // 3. LOCAL STORAGE
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
    // 4. FORMAT HARGA DAN JUMLAH
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

    // ========================================
    // 5. UPDATE TOMBOL MENU
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
    // 6. TOMBOL JUMLAH MENU
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
    // 7. RENDER KERANJANG
    // ========================================
    function renderCart() {
        cartItems.replaceChildren();

        const entries = Object.entries(cart);
        const quantity = totalQuantity();
        const subtotal = calculateSubtotal();

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

                // Gambar menu
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

                // Informasi menu
                const details = document.createElement("div");

                details.className = "min-w-0 flex-1";

                const name = document.createElement("p");

                name.className = "truncate text-sm font-bold text-stone-800";

                name.textContent = item.name;

                const price = document.createElement("p");

                price.className = "mt-1 text-xs font-semibold text-orange-600";

                price.textContent = formatPrice(item.price);

                details.append(name, price);

                // Kontrol jumlah
                const controls = document.createElement("div");

                controls.className = "flex items-center gap-2";

                controls.append(
                    createQuantityButton("Kurangi jumlah", "−", "decrease", id),
                );

                const count = document.createElement("span");

                count.className = "min-w-5 text-center text-sm font-bold";

                count.textContent = String(item.quantity);

                count.setAttribute("aria-label", `Jumlah ${item.name}`);

                controls.append(
                    count,
                    createQuantityButton("Tambah jumlah", "+", "increase", id),
                );

                row.append(image, details, controls);
                cartItems.append(row);
            });
        }

        // Update ringkasan keranjang
        if (cartCount) {
            cartCount.textContent = String(quantity);

            cartCount.classList.toggle("cart-count-pop", quantity > 0);
        }

        if (cartSubtotal) {
            cartSubtotal.textContent = formatPrice(subtotal);
        }

        if (cartTotal) {
            cartTotal.textContent = formatPrice(subtotal);
        }

        if (checkoutButton) {
            checkoutButton.disabled = entries.length === 0;
        }

        updateProductButtons();
    }

    // ========================================
    // 8. BUKA DAN TUTUP KERANJANG
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

        cartButton.focus();
    }

    cartButton.addEventListener("click", openCart);

    closeCartButton.addEventListener("click", closeCart);

    cartOverlay.addEventListener("click", (event) => {
        if (event.target === cartOverlay) {
            closeCart();
        }
    });

    // ========================================
    // 9. PILIHAN CHECKOUT
    // ========================================
    function updateCheckoutOptions() {
        if (
            !orderMode ||
            !orderTypeGroup ||
            !preorderSchedule ||
            !pickupDate ||
            !pickupTime ||
            !paymentMethod
        ) {
            return;
        }

        const isPreorder = orderMode.value === "preorder";

        // Nomor telepon: hanya tampil saat preorder.
        customerPhoneGroup?.classList.toggle("hidden", !isPreorder);

        if (customerPhone) {
            customerPhone.required = isPreorder;

            // Kosongkan nomor telepon saat memilih Pesan Sekarang.
            if (!isPreorder) {
                customerPhone.value = "";
            }
        }

        phoneRequiredLabel?.classList.toggle("hidden", !isPreorder);
        phoneOptionalLabel?.classList.toggle("hidden", isPreorder);

        // Tampilkan pilihan sesuai jenis pesanan.
        orderTypeGroup.classList.toggle("hidden", isPreorder);
        preorderSchedule.classList.toggle("hidden", !isPreorder);

        if (orderType) {
            orderType.disabled = isPreorder;
            orderType.required = !isPreorder;
        }

        // Validasi jadwal preorder.
        pickupDate.required = isPreorder;
        pickupTime.required = isPreorder;

        // Metode pembayaran.
        if (isPreorder) {
            paymentMethod.value = "qris";
            paymentMethod.disabled = true;

            // Hilangkan dropdown dan tampilkan QRIS sebagai teks.
            paymentMethodSelect?.classList.add("hidden");
            paymentMethodFixed?.classList.remove("hidden");

            if (paymentInfo) {
                paymentInfo.textContent =
                    "Preorder wajib dibayar menggunakan QRIS.";
            }
        } else {
            paymentMethod.disabled = false;

            // Kembalikan dropdown saat pesan sekarang.
            paymentMethodSelect?.classList.remove("hidden");
            paymentMethodFixed?.classList.add("hidden");

            if (paymentInfo) {
                paymentInfo.textContent =
                    paymentMethod.value === "tunai"
                        ? "Pembayaran tunai dipilih untuk pesanan sekarang."
                        : "Pembayaran menggunakan QRIS.";
            }
        }
    }

    orderMode?.addEventListener("change", updateCheckoutOptions);

    paymentMethod?.addEventListener("change", () => {
        if (!paymentInfo || !orderMode || orderMode.value !== "sekarang") {
            return;
        }

        paymentInfo.textContent =
            paymentMethod.value === "tunai"
                ? "Pembayaran tunai dipilih untuk pesanan sekarang."
                : "Pembayaran menggunakan QRIS.";
    });

    // ========================================
    // 10. BATAS TANGGAL PREORDER
    // ========================================
    if (pickupDate) {
        const today = new Date();

        const localToday = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, "0"),
            String(today.getDate()).padStart(2, "0"),
        ].join("-");

        pickupDate.min = localToday;
    }

    // ========================================
    // 11. BUKA DAN TUTUP CHECKOUT
    // ========================================
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

        // Perbarui total berdasarkan isi keranjang terbaru.
        checkoutTotal.textContent = formatPrice(calculateSubtotal());

        // Terapkan opsi sesuai pilihan checkout.
        updateCheckoutOptions();

        checkoutOverlay.classList.remove("hidden");
        checkoutOverlay.classList.add("flex");

        checkoutOverlay.setAttribute("aria-hidden", "false");

        // Tutup keranjang setelah checkout dibuka.
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
    // 12. SUBMIT CHECKOUT KE BACKEND
    // ========================================
    checkoutForm?.addEventListener("submit", async (event) => {
        event.preventDefault();

        const entries = Object.entries(cart);

        if (entries.length === 0) {
            alert("Keranjang masih kosong.");
            return;
        }

        const selectedPayment =
            orderMode.value === "preorder" ? "qris" : paymentMethod.value;

        const formData = new FormData(checkoutForm);

        formData.set("payment_method", selectedPayment);

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
            const response = await fetch("/guest/orders", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    customer_name: formData.get("customer_name"),
                    customer_phone: formData.get("customer_phone"),
                    order_type: formData.get("order_type"),
                    payment_method: selectedPayment,
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

            if (selectedPayment === "qris") {
                qrisTotal.textContent = checkoutTotal.textContent;
                openQrisModal();
            } else {
                alert(
                    `Pesanan berhasil dibuat!\nKode pesanan: ${result.order_code}\nTotal: Rp${Number(result.total_amount).toLocaleString("id-ID")}`,
                );

                cart = {};
                persistCart();
                renderCart();
                closeCheckoutModal();
            }
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

        // Pemeriksaan stok sementara dari data kartu menu.
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

        // Tombol tambah menu.
        const addButton = target.closest('.product-card [data-action="add"]');

        if (addButton) {
            const card = addButton.closest(".product-card");

            if (card) {
                addMenuToCart(card, addButton);
            }

            return;
        }

        // Tombol tambah/kurangi jumlah di keranjang.
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

        if (!cartOverlay.classList.contains("hidden")) {
            closeCart();
        }
    });

    // Modal Qris
    function openQrisModal() {
        qrisTotal.textContent = checkoutTotal.textContent;

        checkoutOverlay.classList.add("hidden");
        checkoutOverlay.classList.remove("flex");

        qrisOverlay.classList.remove("hidden");
        qrisOverlay.classList.add("flex");
        qrisOverlay.setAttribute("aria-hidden", "false");
    }

    function closeQrisModal() {
        qrisOverlay.classList.add("hidden");
        qrisOverlay.classList.remove("flex");
        qrisOverlay.setAttribute("aria-hidden", "true");
    }

    closeQris.addEventListener("click", closeQrisModal);

    backToCheckout.addEventListener("click", () => {
        closeQrisModal();

        checkoutOverlay.classList.remove("hidden");
        checkoutOverlay.classList.add("flex");
    });

    // Validasi Bukti pembayaran
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

    // if (confirmQrisPayment) {
    confirmQrisPayment.addEventListener("click", async () => {
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
                `/guest/orders/${encodeURIComponent(currentOrderToken)}/payment-proof`,
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

    function updateQrisImage() {
        const imageUrl = qrisImage?.getAttribute("src");
        const isConfigured =
            Boolean(imageUrl) && qrisImage.complete && qrisImage.naturalWidth > 0;

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

    downloadQris?.addEventListener("click", async () => {
        const imageUrl = qrisImage?.getAttribute("src");

        if (!imageUrl || !qrisImage?.naturalWidth) {
            alert("Gambar QRIS belum tersedia.");
            return;
        }

        try {
            const response = await fetch(qrisImage.src);

            if (!response.ok) {
                throw new Error("Gambar QRIS gagal diunduh.");
            }

            const blob = await response.blob();
            const blobUrl = URL.createObjectURL(blob);

            const link = document.createElement("a");
            link.href = blobUrl;
            link.download = "qris-kantin-baraya.png";

            document.body.appendChild(link);
            link.click();
            link.remove();

            setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
        } catch (error) {
            console.error("Download QRIS gagal:", error);
            alert("QRIS gagal diunduh. Silakan coba lagi.");
        }
    });
    // ========================================
    // 16. INISIALISASI AWAL
    // ========================================
    updateCheckoutOptions();
    renderCart();
}

// ========================================
// JALANKAN SETELAH DOM SIAP
// ========================================
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeCart, {
        once: true,
    });
} else {
    initializeCart();
}
