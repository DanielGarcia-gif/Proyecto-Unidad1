document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector(".perfil-form");
    const inputTel = form.querySelector("input[name='telefono']");

    // Crear el mensaje de error
    const errorMsg = document.createElement("p");
    errorMsg.style.color = "red";
    errorMsg.style.margin = "0 0 5px 0";
    errorMsg.style.fontSize = "14px";
    errorMsg.style.display = "none";

    // Insertarlo encima del input
    inputTel.parentNode.appendChild(errorMsg);

    form.addEventListener("submit", (e) => {
        let tel = inputTel.value.trim();

        // Si está vacío → mandar como NULL
        if (tel === "") {
            inputTel.value = ""; 
            errorMsg.style.display = "none";
            return;
        }

        // Solo números
        if (!/^[0-9]+$/.test(tel)) {
            e.preventDefault();
            errorMsg.textContent = "El teléfono solo puede contener números.";
            errorMsg.style.display = "block";
            return;
        }

        // Debe tener 10 dígitos
        if (tel.length !== 10) {
            e.preventDefault();
            errorMsg.textContent = "El teléfono debe tener exactamente 10 dígitos.";
            errorMsg.style.display = "block";
            return;
        }

        errorMsg.style.display = "none";
    });

});
