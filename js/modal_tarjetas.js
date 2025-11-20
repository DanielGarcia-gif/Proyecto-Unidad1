document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modalTarjetas");
    const btnAbrir = document.getElementById("btnPagarTarjeta");
    const btnCerrar = document.getElementById("cerrarModalTarjetas");
    
    const formSeleccion = document.getElementById("formSeleccionTarjeta");
    const formAgregar = document.getElementById("formAgregarTarjetaModal");
    const btnVolver = document.getElementById("btnVolverSeleccionTarjeta");

    // ============================================================
    // ABRIR MODAL
    // ============================================================
    btnAbrir.addEventListener("click", () => {
        modal.style.display = "flex";

        if (formSeleccion) {
            formSeleccion.style.display = "block";
            formAgregar.style.display = "none";
        } else {
            formAgregar.style.display = "block";
        }
    });

    // ============================================================
    // CERRAR MODAL
    // ============================================================
    btnCerrar.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // ============================================================
    // CAMBIO ENTRE FORMULARIOS
    // ============================================================
    if (formSeleccion) {

        // Botón agregar nueva tarjeta
        const btnAgregarNueva = document.createElement("button");
        btnAgregarNueva.type = "button";
        btnAgregarNueva.textContent = "Agregar nueva tarjeta";
        btnAgregarNueva.className = "btn-confirmar";
        formSeleccion.appendChild(btnAgregarNueva);

        btnAgregarNueva.addEventListener("click", () => {
            formSeleccion.style.display = "none";
            formAgregar.style.display = "block";
        });

        // Botón volver
        if (btnVolver) {
            btnVolver.addEventListener("click", () => {
                formAgregar.style.display = "none";
                formSeleccion.style.display = "block";
            });
        }
    }

    // ============================================================
    // VALIDACIONES DEL FORMULARIO DE AGREGAR TARJETA
    // ============================================================
    if (formAgregar) {

        const inputNumeroTarjeta = formAgregar.querySelector('input[name="numero_tarjeta"]');
        const inputExp = formAgregar.querySelector('input[name="expiracion"]');
        const inputCVV = formAgregar.querySelector('input[name="cvv"]');

        // Crear spans arriba del input
        [inputNumeroTarjeta, inputExp, inputCVV].forEach(input => {
            let span = document.createElement("span");
            span.className = "error-msg";
            span.style.color = "red";
            span.style.fontSize = "0.9em";
            span.style.display = "block";
            input.parentNode.insertBefore(span, input);
        });

        const spanNum = inputNumeroTarjeta.previousElementSibling;
        const spanExp = inputExp.previousElementSibling;
        const spanCVV = inputCVV.previousElementSibling;

        // ============================================================
        // VALIDACIÓN NUMERO DE TARJETA
        // ============================================================
        const bancosValidos = {
            visa: /^4\d{12}(\d{3})?$/,       // Visa 13 o 16 dígitos
            mastercard: /^5[1-5]\d{14}$/,    // MasterCard: 16 dígitos
            amex: /^3[47]\d{13}$/            // Amex: 15 dígitos
        };

        function detectarMarca(num) {
            if (bancosValidos.visa.test(num)) return "visa";
            if (bancosValidos.mastercard.test(num)) return "mastercard";
            if (bancosValidos.amex.test(num)) return "amex";
            return "";
        }

        function validarNumeroTarjeta(num) {
            num = num.replace(/\s/g, "");

            if (!/^\d+$/.test(num))
                return "❌ Solo números permitidos";

            if (num.length < 13)
                return `❌ Faltan dígitos (${num.length}/13+)`;

            // Validación por marca
            const marca = detectarMarca(num);
            if (!marca)
                return "❌ Tarjeta no válida (Visa/Mastercard/Amex)";

            return "";
        }

        // Formatear mientras escribe
        inputNumeroTarjeta.addEventListener("input", () => {
            inputNumeroTarjeta.value = inputNumeroTarjeta.value
                .replace(/\D/g, "")
                .replace(/(.{4})/g, "$1 ")
                .trim();

            const soloNum = inputNumeroTarjeta.value.replace(/\s/g, "");
            spanNum.textContent = validarNumeroTarjeta(soloNum);
        });

        // ============================================================
        // VALIDACIÓN FECHA EXPIRACIÓN
        // ============================================================
        function validarExpiracion(valor) {

            if (!/^\d{2}\/\d{2}$/.test(valor))
                return "❌ Formato inválido (MM/AA)";

            const [mesStr, anioStr] = valor.split("/");
            const mes = parseInt(mesStr, 10);
            const anio = parseInt(anioStr, 10) + 2000; // convertir AA a AAAA

            const hoy = new Date();

            if (mes < 1 || mes > 12)
                return "❌ Mes inválido";

            if (anio < hoy.getFullYear() || (anio === hoy.getFullYear() && mes <= hoy.getMonth() + 1))
                return "❌ Tarjeta vencida";

            return "";
        }

        inputExp.addEventListener("input", () => {
            if (inputExp.value.length === 2 && !inputExp.value.includes("/"))
                inputExp.value += "/";

            spanExp.textContent = validarExpiracion(inputExp.value);
        });

        // ============================================================
        // VALIDACIÓN CVV
        // ============================================================
        function validarCVV(cvv) {
            if (!/^\d{3,4}$/.test(cvv))
                return "❌ CVV inválido (3 o 4 dígitos)";
            return "";
        }

        inputCVV.addEventListener("input", () => {
            spanCVV.textContent = validarCVV(inputCVV.value);
        });

        // ============================================================
        // VALIDACIÓN FINAL AL ENVIAR FORM
        // ============================================================
        const formNuevo = formAgregar.querySelector("form");

        formNuevo.addEventListener("submit", (e) => {
            const errores = [
                validarNumeroTarjeta(inputNumeroTarjeta.value.replace(/\s/g, "")),
                validarExpiracion(inputExp.value),
                validarCVV(inputCVV.value)
            ];

            spanNum.textContent = errores[0];
            spanExp.textContent = errores[1];
            spanCVV.textContent = errores[2];

            if (errores.some(msg => msg)) {
                e.preventDefault();
            }
        });
    }

});
