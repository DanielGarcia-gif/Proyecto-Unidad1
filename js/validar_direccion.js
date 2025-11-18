// validar_direccion.js

document.addEventListener("DOMContentLoaded", () => {

    const listaDirecciones = document.querySelectorAll(".direccion-item");

    // Guardar direcciones existentes como objetos JSON
    const direccionesExistentes = Array.from(listaDirecciones).map(item => {
        return {
            direccion: item.dataset.direccion,
            ciudad: item.dataset.ciudad,
            cp: item.dataset.cp
        };
    });

    const form = document.getElementById("formAgregarDireccion");
    const msg = document.getElementById("mensajeDireccion");

    if (!form) return;

    form.addEventListener("submit", function (e) {

        const direccion = document.querySelector("input[name='direccion']").value.trim().toLowerCase();
        const ciudad = document.querySelector("input[name='ciudad']").value.trim().toLowerCase();
        const cp = document.querySelector("input[name='codigo_postal']").value.trim().toLowerCase();

        // Buscar si ya existe una dirección igual
        const duplicada = direccionesExistentes.some(item =>
            item.direccion === direccion &&
            item.ciudad === ciudad &&
            item.cp === cp
        );

        msg.style.display = "none";
        msg.innerHTML = "";

        if (duplicada) {
            msg.innerHTML = "⚠️ Esta dirección ya está registrada.";
            msg.style.display = "block";

            e.preventDefault();
            return false;
        }
    });
});
