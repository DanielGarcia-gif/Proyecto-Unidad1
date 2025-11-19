document.addEventListener("DOMContentLoaded", () => {


// ==================== MODAL TARJETAS ====================
const modal = document.getElementById("modalTarjetas");
const btnAbrir = document.getElementById("btnPagarTarjeta");
const btnCerrar = document.getElementById("cerrarModalTarjetas");
const btnVolver = document.getElementById("btnVolverSeleccionTarjeta");
const formSeleccion = document.getElementById("formSeleccionTarjeta");
const formAgregar = document.getElementById("formAgregarTarjetaModal");

// Abrir modal
btnAbrir.addEventListener("click", () => {
    modal.style.display = "flex";
    if (formSeleccion) formSeleccion.style.display = "block";
    if (formAgregar) formAgregar.style.display = "none";
});

// Cerrar modal
btnCerrar.addEventListener("click", () => {
    modal.style.display = "none";
});

// Botón para agregar nueva tarjeta
const btnAgregarNueva = document.createElement("button");
btnAgregarNueva.type = "button";
btnAgregarNueva.textContent = "Agregar nueva tarjeta";
btnAgregarNueva.className = "btn-confirmar";
if (formSeleccion) formSeleccion.appendChild(btnAgregarNueva);

btnAgregarNueva.addEventListener("click", () => {
    formSeleccion.style.display = "none";
    formAgregar.style.display = "block";
});

// Volver a seleccionar tarjeta guardada
if (btnVolver) {
    btnVolver.addEventListener("click", () => {
        formAgregar.style.display = "none";
        formSeleccion.style.display = "block";
    });
}

// ==================== FORMULARIO NUEVA TARJETA ====================
const inputNumeroTarjeta = formAgregar.querySelector('input[name="numero_tarjeta"]');
const inputExp = formAgregar.querySelector('input[name="expiracion"]');
const inputCVV = formAgregar.querySelector('input[name="cvv"]');

// Crear spans para mensajes de error arriba del input
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

// Formateo automático del número
function formatearTarjeta(valor) {
    return valor.replace(/\D/g, "").replace(/(.{4})/g, "$1").trim();
}

inputNumeroTarjeta.addEventListener("input", () => {
    inputNumeroTarjeta.value = formatearTarjeta(inputNumeroTarjeta.value);
    spanNum.textContent = validarNumeroTarjeta(inputNumeroTarjeta.value);
});

// Auto "/" en expiración
inputExp.addEventListener("input", () => {
    if (inputExp.value.length === 2 && !inputExp.value.includes("/")) {
        inputExp.value += "/";
    }
    spanExp.textContent = validarExpiracion(inputExp.value);
});

// CVV nueva tarjeta
inputCVV.addEventListener("input", () => {
    spanCVV.textContent = validarCVV(inputCVV.value);
});

// ==================== FORMULARIO SELECCIONAR TARJETA ====================
if (formSeleccion) {
    const inputCVVSeleccion = formSeleccion.querySelector('input[name="cvv"]');

    // Crear span para mensaje de error arriba del input
    let spanCVVSel = document.createElement("span");
    spanCVVSel.className = "error-msg";
    spanCVVSel.style.color = "red";
    spanCVVSel.style.fontSize = "0.9em";
    spanCVVSel.style.display = "block";
    inputCVVSeleccion.parentNode.insertBefore(spanCVVSel, inputCVVSeleccion);

    // Validación en tiempo real
    inputCVVSeleccion.addEventListener("input", () => {
        spanCVVSel.textContent = validarCVV(inputCVVSeleccion.value);
    });

    // Validación al enviar
    const formTag = formSeleccion.querySelector("form");
    if (formTag) {
        formTag.addEventListener("submit", (e) => {
            const errorCVV = validarCVV(inputCVVSeleccion.value);
            spanCVVSel.textContent = errorCVV;
            if (errorCVV) e.preventDefault();
        });
    }
}

// ==================== FUNCIONES DE VALIDACIÓN ====================
function validarNumeroTarjeta(num) {
    num = num.replace(/\s/g, "");
    if (!/^\d{13,16}$/.test(num)) return "❌ Número inválido (13-16 dígitos)";
    return "";
}

function validarExpiracion(valor) {
    if (!/^\d{2}\/\d{2}$/.test(valor)) return "❌ Formato inválido MM/AA";
    const [mesStr, anioStr] = valor.split("/");
    const mes = parseInt(mesStr, 10);
    const anio = parseInt(anioStr, 10) + 2000;
    const hoy = new Date();
    if (mes < 1 || mes > 12) return "❌ Mes inválido";
    if (anio < hoy.getFullYear() || (anio === hoy.getFullYear() && mes < hoy.getMonth() + 1)) {
        return "❌ Tarjeta vencida";
    }
    return "";
}

function validarCVV(cvv) {
    if (cvv.length < 3) return "❌ CVV demasiado corto (mínimo 3 dígitos)";
    if (!/^\d{3,4}$/.test(cvv)) return "❌ CVV inválido (3 o 4 dígitos)";
    return "";
}

// ==================== VALIDACIÓN AL ENVIAR FORM NUEVA TARJETA ====================
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


});
