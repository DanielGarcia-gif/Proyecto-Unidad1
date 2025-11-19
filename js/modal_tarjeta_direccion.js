document.addEventListener("DOMContentLoaded", () => {
    const btnPagarTarjeta = document.getElementById("btnPagarTarjeta");
    const modal = document.getElementById("modalTarjetas");
    const modalForm = document.querySelector("#formSeleccionTarjeta form");

    btnPagarTarjeta.addEventListener("click", () => {
        // Abrir modal
        modal.style.display = "block";

        // Obtener dirección seleccionada o escrita del formulario principal
        const selectDireccion = document.querySelector('select[name="id_direccion"]');
        const inputDireccion = document.querySelector('input[name="direccion"]');
        const inputCiudad = document.querySelector('input[name="ciudad"]');
        const inputCP = document.querySelector('input[name="codigo_postal"]');

        // Función para crear/actualizar input oculto en el modal
        function setHiddenInput(name, value) {
            let input = modalForm.querySelector(`input[name="${name}"]`);
            if (!input) {
                input = document.createElement("input");
                input.type = "hidden";
                input.name = name;
                modalForm.appendChild(input);
            }
            input.value = value;
        }

        if (selectDireccion) setHiddenInput("id_direccion", selectDireccion.value);
        if (inputDireccion) setHiddenInput("direccion", inputDireccion.value);
        if (inputCiudad) setHiddenInput("ciudad", inputCiudad.value);
        if (inputCP) setHiddenInput("codigo_postal", inputCP.value);

        // Indicar que el método de pago es tarjeta
        setHiddenInput("metodo_pago", "tarjeta");
    });

    // Cerrar modal
    const btnCerrar = document.getElementById("cerrarModalTarjetas");
    btnCerrar.addEventListener("click", () => {
        modal.style.display = "none";
    });
});
