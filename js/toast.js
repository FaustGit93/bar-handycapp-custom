function mostraToast(messaggio, tipo) {
    tipo = tipo || 'success';
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast toast-' + tipo;
    toast.textContent = messaggio;
    container.appendChild(toast);

    requestAnimationFrame(function() {
        toast.classList.add('mostra');
    });

    setTimeout(function() {
        toast.classList.remove('mostra');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3500);
}

document.addEventListener('DOMContentLoaded', function() {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        const testo = alertBox.textContent.trim();
        const tipo = alertBox.classList.contains('success') ? 'success' : 'error';
        mostraToast(testo, tipo);
        alertBox.remove();
    }
});