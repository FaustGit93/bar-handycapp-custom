document.addEventListener('DOMContentLoaded', function() {
    const langToggle = document.getElementById('lang-toggle-menu');

    document.addEventListener('click', function(e) {
        const clickedInside = langToggle && e.target.closest('.lang-fab');
        if (!clickedInside && langToggle) {
            langToggle.checked = false;
        }
    });
});