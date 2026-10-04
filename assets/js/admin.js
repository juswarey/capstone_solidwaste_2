document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const themeToggle = document.getElementById('adminThemeToggle');
    const savedTheme = localStorage.getItem('admin-theme') || 'light';
    html.setAttribute('data-theme', savedTheme);
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {

            const nextTheme =
                html.getAttribute('data-theme') === 'dark'
                    ? 'light'
                    : 'dark';

            html.setAttribute('data-theme', nextTheme);

            localStorage.setItem('admin-theme', nextTheme);
        });
    }
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && adminSidebar && sidebarOverlay) {

        sidebarToggle.addEventListener('click', () => {

            adminSidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');

        });

        sidebarOverlay.addEventListener('click', () => {

            adminSidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');

        });
    }
    const clock = document.getElementById('clock');
    if (clock) {
        function updateClock() {
            const now = new Date();
            let hour = now.getHours();
            const minute = String(now.getMinutes()).padStart(2, '0');
            const second = String(now.getSeconds()).padStart(2, '0');
            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12;
            clock.textContent =
                `${String(hour).padStart(2, '0')}:${minute}:${second} ${ampm}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    }
});