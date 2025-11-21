document.addEventListener("DOMContentLoaded", () => {
    const contenedores = document.querySelectorAll(".carrusel-contenedor");

    contenedores.forEach(contenedor => {
        const carrusel = contenedor.querySelector(".carrusel");
        const btnPrev = contenedor.querySelector(".prev");
        const btnNext = contenedor.querySelector(".next");

        const scrollAmount = 300; // cantidad a deslizar por clic

        btnNext.addEventListener("click", () => {
            carrusel.scrollLeft += scrollAmount;
        });

        btnPrev.addEventListener("click", () => {
            carrusel.scrollLeft -= scrollAmount;
        });
    });
});
