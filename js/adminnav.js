// Gestione della barra in basso dell'admin: scroll fluido alle sezioni
// e apertura/chiusura del side panel "Impostazioni".

document.addEventListener('DOMContentLoaded', function () {

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

    // --- Scroll fluido alle sezioni (Categorie, Nuovo piatto) ---
    // Chiude anche il pannello Impostazioni se era aperto, così la barra
    // in basso resta sempre utilizzabile senza doverlo chiudere a mano.
    var linkScroll = document.querySelectorAll('.admin-nav-item[data-scroll-target]');
    linkScroll.forEach(function (link) {
        link.addEventListener('click', function (e) {
            chiudiPannello();
            var targetId = link.getAttribute('data-scroll-target');
            var target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // --- Logout: chiude il pannello anche qui, per coerenza ---
    var btnLogout = document.querySelector('.admin-bottom-nav a[href="logout.php"]');
    if (btnLogout) {
        btnLogout.addEventListener('click', function () {
            chiudiPannello();
        });
    }
});