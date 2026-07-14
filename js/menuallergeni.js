document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.icona-allergeni').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.dataset.target;
            const pannello = document.getElementById(targetId);
            if (!pannello) return;

            const aperto = pannello.classList.contains('aperto');

            if (aperto) {
                pannello.style.maxHeight = null;
                pannello.classList.remove('aperto');
                this.classList.remove('attivo');
            } else {
                pannello.classList.add('aperto');
                pannello.style.maxHeight = pannello.scrollHeight + 'px';
                this.classList.add('attivo');
            }
        });
    });
});