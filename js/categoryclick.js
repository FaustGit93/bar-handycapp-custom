document.querySelectorAll('.sticky-nav a').forEach(link => {

    link.addEventListener('click', () => {

        document.querySelectorAll('.sticky-nav a')
            .forEach(a => a.classList.remove('attiva'));

        link.classList.add('attiva');

    });

});