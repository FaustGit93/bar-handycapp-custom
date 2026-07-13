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

        const icons = {
            light: '☀️',
            dark: '🌙',
            system: '🖥️'
        };

        if (currentIcon) {
            currentIcon.textContent = icons[theme] || '🖥️';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {

        // ===== TEMA =====
        const savedTheme = localStorage.getItem('menu_theme');
        applyTheme(savedTheme || 'system');

        document.querySelectorAll('.theme-options-menu a').forEach(function(a) {
            a.addEventListener('click', function(e) {
                e.preventDefault();

                applyTheme(this.dataset.theme);

                const checkbox = document.getElementById('theme-toggle-menu');
                if (checkbox) {
                    checkbox.checked = false;
                }
            });
        });

        // ===== MENU LINGUA / TEMA =====
        const langToggle = document.getElementById('lang-toggle-menu');
        const themeToggle = document.getElementById('theme-toggle-menu');

        document.addEventListener('click', function(e) {

            const clickedInsideLang =
                langToggle && e.target.closest('.lang-fab');

            const clickedInsideTheme =
                themeToggle && e.target.closest('.theme-fab');

            // Chiude il menu lingua se clicco fuori
            if (!clickedInsideLang && langToggle) {
                langToggle.checked = false;
            }

            // Chiude il menu tema se clicco fuori
            if (!clickedInsideTheme && themeToggle) {
                themeToggle.checked = false;
            }
        });

        // Impedisce che siano aperti insieme
        if (langToggle && themeToggle) {

            langToggle.addEventListener('change', function() {
                if (this.checked) {
                    themeToggle.checked = false;
                }
            });

            themeToggle.addEventListener('change', function() {
                if (this.checked) {
                    langToggle.checked = false;
                }
            });

        }
    });

})();