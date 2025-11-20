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
            // Caso donde SÍ existen tarjetas guardadas
            formSeleccion.style.display = "block";
            formAgregar.style.display = "none";
        } else {
            // Caso donde NO existen tarjetas guardadas
            formAgregar.style.display = "block";
        }
    });

    // ------------------------------------------------------------
    // CERRAR MODAL
    // ------------------------------------------------------------
    btnCerrar.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // ------------------------------------------------------------
    // SOLO SE EJECUTA SI EXISTEN TARJETAS GUARDADAS
    // ------------------------------------------------------------
    if (formSeleccion) {

        // Botón: agregar nueva tarjeta
        const btnAgregarNueva = document.createElement("button");
        btnAgregarNueva.type = "button";
        btnAgregarNueva.textContent = "Agregar nueva tarjeta";
        btnAgregarNueva.className = "btn-confirmar";
        formSeleccion.appendChild(btnAgregarNueva);

        btnAgregarNueva.addEventListener("click", () => {
            formSeleccion.style.display = "none";
            formAgregar.style.display = "block";
        });

        // Botón: volver a seleccionar tarjeta
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

        inputNumeroTarjeta.addEventListener("input", () => {
            inputNumeroTarjeta.value = inputNumeroTarjeta.value.replace(/\D/g, "").replace(/(.{4})/g, "$1 ").trim();
            spanNum.textContent = validarNumeroTarjeta(inputNumeroTarjeta.value);
        });

        inputExp.addEventListener("input", () => {
            if (inputExp.value.length === 2 && !inputExp.value.includes("/")) {
                inputExp.value += "/";
            }
            spanExp.textContent = validarExpiracion(inputExp.value);
        });

        inputCVV.addEventListener("input", () => {
            spanCVV.textContent = validarCVV(inputCVV.value);
        });

        const formNuevo = formAgregar.querySelector("form");
        if (formNuevo) {
            formNuevo.addEventListener("submit", (e) => {
                const errores = [
                    validarNumeroTarjeta(inputNumeroTarjeta.value),
                    validarExpiracion(inputExp.value),
                    validarCVV(inputCVV.value)
                ];
                spanNum.textContent = errores[0];
                spanExp.textContent = errores[1];
                spanCVV.textContent = errores[2];
                if (errores.some(msg => msg)) e.preventDefault();
            });
        }
    }

    // ============================================================
    // VALIDACIONES
    // ============================================================
    function validarNumeroTarjeta(num) {
        num = num.replace(/\s/g, "");
        if (!/^\d{13,16}$/.test(num)) return "❌ Número inválido (13-16 dígitos)";
        return "";
    }

    function validarExpiracion(valor) {
        if (!/^\d{2}\/\d{2}$/.test(valor))
            return "❌ Formato inválido MM/AA";

        const [mesStr, anioStr] = valor.split("/");
        const mes = parseInt(mesStr, 10);
        const anio = parseInt(anioStr, 10) + 2000;
        const hoy = new Date();

        if (mes < 1 || mes > 12) return "❌ Mes inválido";
        if (anio < hoy.getFullYear() || (anio === hoy.getFullYear() && mes < hoy.getMonth() + 1))
            return "❌ Tarjeta vencida";

        return "";
    }

    function validarCVV(cvv) {
        if (!/^\d{3,4}$/.test(cvv)) return "❌ CVV inválido";
        return "";
    }

});
