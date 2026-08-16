// Gestione della barra in basso dell'admin: scroll fluido alle sezioni
// e apertura/chiusura del side panel "Impostazioni".

document.addEventListener('DOMContentLoaded', function () {

    // --- Scroll fluido alle sezioni (Categorie, Nuovo piatto) ---
    var linkScroll = document.querySelectorAll('.admin-nav-item[data-scroll-target]');
    linkScroll.forEach(function (link) {
        link.addEventListener('click', function (e) {
            var targetId = link.getAttribute('data-scroll-target');
            var target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // --- Side panel Impostazioni ---
    var pannello = document.getElementById('panel-impostazioni');
    var backdrop = document.getElementById('panel-backdrop');
    var btnApri = document.getElementById('apri-impostazioni');
    var btnChiudi = document.getElementById('panel-chiudi');

    function apriPannello() {
        if (pannello && backdrop) {
            pannello.classList.add('aperto');
            backdrop.classList.add('aperto');
            document.body.classList.add('panel-aperto-blocca-scroll');
        }
    }

    function chiudiPannello() {
        if (pannello && backdrop) {
            pannello.classList.remove('aperto');
            backdrop.classList.remove('aperto');
            document.body.classList.remove('panel-aperto-blocca-scroll');
        }
    }

    if (btnApri) {
        btnApri.addEventListener('click', apriPannello);
    }
    if (btnChiudi) {
        btnChiudi.addEventListener('click', chiudiPannello);
    }
    if (backdrop) {
        backdrop.addEventListener('click', chiudiPannello);
    }
});
