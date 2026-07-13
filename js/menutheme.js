(function() {
    const html = document.documentElement;

    function applyTheme(theme) {
        if (theme === 'system') {
            html.removeAttribute('data-theme');
        } else {
            html.setAttribute('data-theme', theme);
        }
        localStorage.setItem('menu_theme', theme);
        updateSelection(theme);
    }

    function updateSelection(theme) {
        document.querySelectorAll('.theme-options-menu a').forEach(function(a) {
            a.classList.toggle('attiva', a.dataset.theme === theme);
        });
        const currentIcon = document.querySelector('.theme-selected-menu .theme-icon');
        const icons = { light: '☀️', dark: '🌙', system: '🖥️' };
        if (currentIcon) currentIcon.textContent = icons[theme] || '🖥️';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('menu_theme');
        applyTheme(savedTheme || 'system');

        document.querySelectorAll('.theme-options-menu a').forEach(function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                applyTheme(this.dataset.theme);
                const checkbox = document.getElementById('theme-toggle-menu');
                if (checkbox) checkbox.checked = false;
            });
        });
    });
})();