document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-toggle-password]").forEach((toggle) => {
        const input = document.getElementById(toggle.dataset.togglePassword);
        const eyeOpen = toggle.querySelector("[data-eye-open]");
        const eyeClosed = toggle.querySelector("[data-eye-closed]");

        if (!input) return;

        toggle.addEventListener("click", () => {
            const willShow = input.type === "password";
            input.type = willShow ? "text" : "password";
            toggle.setAttribute("aria-pressed", String(willShow));
            toggle.setAttribute(
                "aria-label",
                willShow ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
            );
            eyeOpen?.classList.toggle("hidden", willShow);
            eyeClosed?.classList.toggle("hidden", !willShow);
        });
    });
});
