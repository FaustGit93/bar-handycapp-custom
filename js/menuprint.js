/**
 * menuprint.js
 * Inserisce dinamicamente un mini-logo prima di ogni categoria
 * (tranne la prima) SOLO durante la stampa/esportazione PDF.
 * Non richiede alcuna modifica al markup generato da menu.php:
 * legge il logo già presente nell'header e lo clona.
 */
(function () {
    function inserisciLoghiStampa() {
        var logoOriginale = document.querySelector('.menu-logo img');
        if (!logoOriginale) return;

        var categorie = document.querySelectorAll('.category-section');

        categorie.forEach(function (sezione, indice) {
            if (indice === 0) return; // la prima ha già il logo dell'header sopra

            var wrapper = document.createElement('div');
            wrapper.className = 'print-category-header';

            var img = document.createElement('img');
            img.src = logoOriginale.currentSrc || logoOriginale.src;
            img.alt = logoOriginale.alt || 'Logo';

            wrapper.appendChild(img);
            sezione.parentNode.insertBefore(wrapper, sezione);
        });
    }

    function rimuoviLoghiStampa() {
        document.querySelectorAll('.print-category-header').forEach(function (el) {
            el.remove();
        });
    }

    window.addEventListener('beforeprint', inserisciLoghiStampa);
    window.addEventListener('afterprint', rimuoviLoghiStampa);
})();
