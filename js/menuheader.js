(function() {
    function impostaAltezzaHeader() {
        var header = document.querySelector('.sticky-header');
        if (header) {
            document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
        }
    }

    window.addEventListener('load', impostaAltezzaHeader);
    window.addEventListener('resize', impostaAltezzaHeader);
})();