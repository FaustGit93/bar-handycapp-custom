document.addEventListener("DOMContentLoaded", () => {

    const links = document.querySelectorAll(".sticky-nav a");
    const titles = document.querySelectorAll(".category-title");


    /*
        Attiva una categoria nella navbar
    */
    function attivaCategoria(id) {

        links.forEach(link => {

            link.classList.remove("attiva");

            if (link.getAttribute("href") === "#" + id) {

                link.classList.add("attiva");

                // Porta la categoria visibile nella navbar
                link.scrollIntoView({
                    behavior: "smooth",
                    block: "nearest",
                    inline: "center"
                });

            }

        });

    }



    /*
        CLICK SULLA CATEGORIA

        Mantiene il comportamento
        originale degli anchor #cat-id
    */
    links.forEach(link => {

        link.addEventListener("click", function() {

            const id = this
                .getAttribute("href")
                .replace("#", "");

            attivaCategoria(id);

        });

    });



    /*
        CAMBIO AUTOMATICO DURANTE LO SCROLL

        Osserviamo solo i titoli
        delle categorie
    */
    const observer = new IntersectionObserver(entries => {


        entries.forEach(entry => {


            if (entry.isIntersecting) {


                const sezione = entry.target
                    .closest(".category-section");


                if (sezione) {

                    attivaCategoria(sezione.id);

                }

            }


        });


    }, {

        /*
            Zona vicino alla navbar.
            Cambia categoria quando
            il titolo arriva in alto.
        */
        rootMargin: "-120px 0px -75% 0px",
        threshold: 0

    });



    /*
        Avvia osservazione titoli
    */
    titles.forEach(title => {

        observer.observe(title);

    });


});