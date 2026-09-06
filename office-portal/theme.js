// Office Portal — theme toggle
// Dark is the default look; this only switches to light mode when the
// user asks for it, and remembers the choice in localStorage.
(function () {
    function isLight() {
        return document.documentElement.getAttribute('data-theme') === 'light';
    }

    function updateIcon(btn) {
        if (!btn) return;
        btn.textContent = isLight() ? '☀️' : '🌙';
        btn.setAttribute('aria-pressed', isLight() ? 'true' : 'false');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('themeToggle');
        updateIcon(btn);

        if (btn) {
            btn.addEventListener('click', function () {
                if (isLight()) {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.setItem('op-theme', 'dark');
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                    localStorage.setItem('op-theme', 'light');
                }
                updateIcon(btn);
            });
        }
    });
})();
