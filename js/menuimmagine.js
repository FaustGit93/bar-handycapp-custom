document.addEventListener("DOMContentLoaded", () => {

    const lightbox = document.getElementById("img-lightbox");
    const lightboxImg = document.getElementById("img-lightbox-img");
    const closeBtn = document.getElementById("img-lightbox-close");
    const thumbs = document.querySelectorAll(".item-image-thumb");

    if (!lightbox || !lightboxImg) {
        return;
    }

    /*
        Apre il lightbox con l'immagine
        cliccata a piena risoluzione
    */
    function apriLightbox(thumb) {
        lightboxImg.src = thumb.getAttribute("data-full");
        lightboxImg.alt = thumb.getAttribute("alt") || "";
        lightbox.classList.add("aperta");
        document.body.style.overflow = "hidden";
    }

    /*
        Chiude il lightbox e ripristina
        lo stato precedente della pagina
    */
    function chiudiLightbox() {
        lightbox.classList.remove("aperta");
        lightboxImg.src = "";
        document.body.style.overflow = "";
    }

    thumbs.forEach(thumb => {
        thumb.addEventListener("click", (e) => {
            e.preventDefault();
            apriLightbox(thumb);
        });
    });

    closeBtn.addEventListener("click", chiudiLightbox);

    // Click fuori dall'immagine (sfondo) chiude il lightbox
    lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) {
            chiudiLightbox();
        }
    });

    // Chiusura con tasto ESC
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && lightbox.classList.contains("aperta")) {
            chiudiLightbox();
        }
    });

});
