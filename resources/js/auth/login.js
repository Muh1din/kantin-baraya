
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");

    if (!form) return;

    const email = document.getElementById("email");
    const password = document.getElementById("password");

    const emailField = document.getElementById("emailField");
    const passwordField = document.getElementById("passwordField");

    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");

    const formMessage = document.getElementById("formMessage");

    // Validasi format email
    function validEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
    }

    // Menampilkan atau menyembunyikan error
    function setError(field, error, hasError) {
        field.classList.toggle("field-error", hasError);
        error.classList.toggle("hidden", !hasError);
    }

    // Menghilangkan pesan feedback
    function hideMessage() {
        formMessage.classList.add("hidden");
    }

    // Menampilkan feedback
    function showMessage(message, type = "warning") {
        const styles = {
            warning:
                "rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800",
            info:
                "rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800",
        };

        formMessage.className = styles[type] || styles.warning;
        formMessage.textContent = message;
        formMessage.classList.remove("hidden");
    }

    // Validasi ketika email diketik
    email.addEventListener("input", function () {
        hideMessage();

        if (validEmail(email.value)) {
            setError(emailField, emailError, false);
        } else if (email.value.length > 0) {
            setError(emailField, emailError, true);
        } else {
            setError(emailField, emailError, false);
        }
    });

    // Validasi ketika password diketik
    password.addEventListener("input", function () {
        hideMessage();

        if (password.value.length > 0) {
            setError(passwordField, passwordError, false);
        }
    });

    // Toggle tampil/sembunyikan password
    document
        .getElementById("togglePassword")
        .addEventListener("click", function () {
            const show = password.type === "password";

            password.type = show ? "text" : "password";

            this.setAttribute(
                "aria-label",
                show ? "Sembunyikan password" : "Tampilkan password"
            );

            document.getElementById("eyeIcon").innerHTML = show
                ? `
                    <path d="M3 3l18 18" stroke-linecap="round"/>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"
                          stroke-linecap="round"/>
                    <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.1 0 9.5 7 9.5 7s-3.4 7-9.5 7-9.5-7-9.5-7a16 16 0 0 1 3.1-3.8"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                `
                : `
                    <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/>
                    <circle cx="12" cy="12" r="2.5"/>
                `;
        });

    // Validasi sebelum mengirim form ke Laravel
    form.addEventListener("submit", function (event) {
        const emailValid = validEmail(email.value);
        const passwordValid = password.value.length > 0;

        setError(emailField, emailError, !emailValid);
        setError(passwordField, passwordError, !passwordValid);

        hideMessage();

        if (!emailValid) {
            event.preventDefault();

            showMessage(
                "Periksa kembali alamat email yang Anda masukkan."
            );

            email.focus();
            return;
        }

        if (!passwordValid) {
            event.preventDefault();

            showMessage("Password wajib diisi.");

            password.focus();
            return;
        }

        // Jika valid, form dikirim ke endpoint Laravel.
        // Autentikasi sebenarnya tetap dilakukan oleh backend.
    });

    // Peringatan jika halaman tujuan belum diimplementasikan
    document
        .getElementById("forgotLink")
        .addEventListener("click", function (event) {
            if (this.getAttribute("href") === "#") {
                event.preventDefault();

                showMessage(
                    "Halaman reset password belum tersedia.",
                    "info"
                );
            }
        });
});
