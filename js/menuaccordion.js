// Gestione dell'accordion per le categorie del menu pubblico.
// Attivo solo quando l'impostazione "layout_accordion_categorie" è ON:
// in quel caso menu.php genera .category-card con data-cat-toggle,
// altrimenti questi elementi non esistono e lo script non fa nulla.

document.addEventListener('DOMContentLoaded', function () {
    var cards = document.querySelectorAll('.category-card[data-cat-toggle]');

    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            var id = card.getAttribute('data-cat-toggle');
            var body = document.getElementById('cat-body-' + id);
            if (body) {
                body.classList.toggle('aperto');
                card.classList.toggle('aperto');
            }
        });
    });

    // Click su una voce del menu sticky in alto (es. "Pizze"): apre
    // automaticamente la card corrispondente. Non tocchiamo lo scroll,
    // che resta gestito da categoryclick.js / menuheader.js come già fanno.
    var navLinks = document.querySelectorAll('.sticky-nav a[href^="#cat-"]');

    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            var catId = link.getAttribute('href').replace('#cat-', '');
            var body = document.getElementById('cat-body-' + catId);
            var card = document.querySelector('.category-card[data-cat-toggle="' + catId + '"]');
            if (body && card) {
                body.classList.add('aperto');
                card.classList.add('aperto');
            }
        });
    });
});
