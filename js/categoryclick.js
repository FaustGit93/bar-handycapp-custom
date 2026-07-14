document.addEventListener("DOMContentLoaded", () => {

    const links = document.querySelectorAll(".sticky-nav a");
    const titles = document.querySelectorAll(".category-title");


    function attivaCategoria(id) {

        links.forEach(link => {

            link.classList.remove("attiva");

            if (link.getAttribute("href") === "#" + id) {

                link.classList.add("attiva");

                link.scrollIntoView({
                    behavior: "smooth",
                    block: "nearest",
                    inline: "center"
                });

            }

        });

    }



    /*
        CLICK MANUALE
    */

    links.forEach(link => {

        link.addEventListener("click", function() {

            const id = this
                .getAttribute("href")
                .substring(1);

            attivaCategoria(id);

        });

    });



    /*
        CAMBIO AUTOMATICO SCROLL
        basato sui titoli categoria
    */

    const observer = new IntersectionObserver(entries => {


        entries.forEach(entry => {


            if (entry.isIntersecting) {


                const title = entry.target;

                const section = title.closest(".category-section");


                if (section) {

                    attivaCategoria(section.id);

                }

            }


        });


    }, {

        /*
            La categoria cambia quando
            il titolo passa vicino alla navbar
        */

        rootMargin: "-100px 0px -80% 0px",
        threshold: 0

    });



    titles.forEach(title => {

        observer.observe(title);

    });


});