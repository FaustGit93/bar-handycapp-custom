(function() {
    const html = document.documentElement;

    function applyTheme(theme) {
        if (theme === 'system') {
            html.removeAttribute('data-theme');
        } else {
            html.setAttribute('data-theme', theme);
        }
        localStorage.setItem('theme', theme);
        updateSelection(theme);
    }

    function updateSelection(theme) {
        document.querySelectorAll('.theme-options a').forEach(function(a) {
            a.classList.toggle('attiva', a.dataset.theme === theme);
        });
        const currentIcon = document.querySelector('.theme-selected .theme-icon');
        const icons = { light: '☀️', dark: '🌙', system: '🖥️' };
        if (currentIcon) currentIcon.textContent = icons[theme] || '🖥️';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        applyTheme(savedTheme || 'system');

        document.querySelectorAll('.theme-options a').forEach(function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                applyTheme(this.dataset.theme);
                const checkbox = document.getElementById('theme-toggle');
                if (checkbox) checkbox.checked = false;
            });
        });
    });

    // Chiude i dropdown quando si clicca fuori, e chiude l'altro quando se ne apre uno
    document.addEventListener('DOMContentLoaded', function() {
        const langToggle = document.getElementById('lang-toggle');
        const themeToggle = document.getElementById('theme-toggle');

        document.addEventListener('click', function(e) {
            const clickedInsideLang = langToggle && (e.target.closest('.lang-switcher'));
            const clickedInsideTheme = themeToggle && (e.target.closest('.theme-switcher'));

            if (!clickedInsideLang && langToggle) langToggle.checked = false;
            if (!clickedInsideTheme && themeToggle) themeToggle.checked = false;
        });

        if (langToggle) {
            langToggle.addEventListener('change', function() {
                if (this.checked && themeToggle) themeToggle.checked = false;
            });
        }
        if (themeToggle) {
            themeToggle.addEventListener('change', function() {
                if (this.checked && langToggle) langToggle.checked = false;
            });
        }
    });
})();