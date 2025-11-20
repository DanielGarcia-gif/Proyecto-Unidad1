document.addEventListener("DOMContentLoaded", () => {
    const btnPagarTarjeta = document.getElementById("btnPagarTarjeta");
    const modal = document.getElementById("modalTarjetas");

    btnPagarTarjeta.addEventListener("click", () => {
        modal.style.display = "block";

        // Detectar correctamente el formulario que existe
        let modalForm = document.querySelector("#formSeleccionTarjeta form");

        if (!modalForm) {
            modalForm = document.querySelector("#formAgregarTarjetaModal form");
        }

        // Obtener datos de dirección
        const selectDireccion = document.querySelector('select[name="id_direccion"]');
        const inputDireccion = document.querySelector('input[name="direccion"]');
        const inputCiudad = document.querySelector('input[name="ciudad"]');
        const inputCP = document.querySelector('input[name="codigo_postal"]');

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

        setHiddenInput("metodo_pago", "tarjeta");
    });

    const btnCerrar = document.getElementById("cerrarModalTarjetas");
    btnCerrar.addEventListener("click", () => {
        modal.style.display = "none";
    });
});
