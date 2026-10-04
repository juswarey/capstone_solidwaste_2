document.addEventListener("DOMContentLoaded", () => {

    /* ===========================
       DARK MODE
    =========================== */

    const html = document.documentElement;
    const themeToggle = document.getElementById("theme-toggle");

    const savedTheme = localStorage.getItem("theme") || "light";
    html.setAttribute("data-theme", savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener("click", () => {

            const currentTheme = html.getAttribute("data-theme");

            const newTheme = currentTheme === "dark"
                ? "light"
                : "dark";

            html.setAttribute("data-theme", newTheme);

            localStorage.setItem("theme", newTheme);

        });
    }

    /* ===========================
       MOBILE MENU
    =========================== */

    const hamburger = document.getElementById("hamburger");
    const nav = document.getElementById("main-nav");

    if (hamburger && nav) {

        hamburger.addEventListener("click", () => {

            nav.classList.toggle("active");

            hamburger.classList.toggle("active");

        });

    }
    const clock = document.getElementById('clock');
    const date = document.getElementById('date');

    if (clock && date) {

        function updateDateTime() {

            const now = new Date();

            // Time
            let hour = now.getHours();
            const minute = String(now.getMinutes()).padStart(2, '0');
            const second = String(now.getSeconds()).padStart(2, '0');

            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12;

            clock.textContent =
                `${String(hour).padStart(2, '0')}:${minute}:${second} ${ampm}`;

            // Date
            date.textContent = now.toLocaleDateString('en-US', {
                month: 'long',
                day: '2-digit',
                year: 'numeric'
            });
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
    }
});