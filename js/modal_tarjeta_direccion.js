document.addEventListener("DOMContentLoaded", () => {

    const btnPagarTarjeta = document.getElementById("btnPagarTarjeta");
    const modal = document.getElementById("modalTarjetas");

    btnPagarTarjeta.addEventListener("click", () => {
        modal.style.display = "block";

        // FORMULARIOS DEL MODAL (ambos pueden existir)
        const formSeleccion = document.querySelector("#formSeleccionTarjeta form");
        const formAgregar = document.querySelector("#formAgregarTarjetaModal form");

        // DATOS DE DIRECCIÓN
        const selectDireccion = document.querySelector('select[name="id_direccion"]');
        const inputDireccion = document.querySelector('input[name="direccion"]');
        const inputCiudad = document.querySelector('input[name="ciudad"]');
        const inputCP = document.querySelector('input[name="codigo_postal"]');

        // Función para crear o actualizar hidden inputs en un formulario
        function setHidden(form, name, value) {
            if (!form) return;

            let input = form.querySelector(`input[name="${name}"]`);
            if (!input) {
                input = document.createElement("input");
                input.type = "hidden";
                input.name = name;
                form.appendChild(input);
            }
            input.value = value;
        }

        // Insertar datos en AMBOS formularios
        [formSeleccion, formAgregar].forEach(form => {

            if (selectDireccion) {
                setHidden(form, "id_direccion", selectDireccion.value);
            }
            if (inputDireccion) {
                setHidden(form, "direccion", inputDireccion.value);
            }
            if (inputCiudad) {
                setHidden(form, "ciudad", inputCiudad.value);
            }
            if (inputCP) {
                setHidden(form, "codigo_postal", inputCP.value);
            }

            // Tipo de pago tarjeta
            setHidden(form, "metodo_pago", "tarjeta");
        });

    });

    // Cerrar modal
    const btnCerrar = document.getElementById("cerrarModalTarjetas");
    btnCerrar.addEventListener("click", () => {
        modal.style.display = "none";
    });

});
