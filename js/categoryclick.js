document.addEventListener("DOMContentLoaded", () => {

    const links = document.querySelectorAll(".sticky-nav a");
    const sections = document.querySelectorAll(".category-section");


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
     CLICK CATEGORIA
     Mantiene il comportamento #cat-id
    */

    links.forEach(link => {

        link.addEventListener("click", function() {

            const id = this.getAttribute("href").replace("#", "");

            attivaCategoria(id);

        });

    });


    /*
     SCROLL AUTOMATICO
    */

    const observer = new IntersectionObserver(entries => {

        entries.forEach(entry => {

            if (entry.isIntersecting) {

                attivaCategoria(entry.target.id);

            }

        });

    }, {

        rootMargin: "-30% 0px -60% 0px"

    });


    sections.forEach(section => {
        observer.observe(section);
    });


});