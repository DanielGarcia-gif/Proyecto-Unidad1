document.addEventListener("DOMContentLoaded", () => {

    const formRegistro = document.querySelector('.registro-box form');
    if (!formRegistro) return;

    const passwordInput = formRegistro.querySelector('input[name="password"]');

    // Crear mensaje de error debajo del input si no existe
    let errorMsg = document.createElement("p");
    errorMsg.style.color = "red";
    errorMsg.style.fontSize = "14px";
    errorMsg.style.marginTop = "5px";
    errorMsg.style.display = "none";

    passwordInput.insertAdjacentElement("beforebegin", errorMsg);

    // Regex: 8-16 caracteres, al menos un número y un símbolo
    const regex = /^(?=.*\d)(?=.*[^A-Za-z0-9]).{8,16}$/;

    // Validación en tiempo real
    passwordInput.addEventListener("input", () => {
        const pass = passwordInput.value;
        if (!regex.test(pass)) {
            errorMsg.textContent = "Contraseña inválida: 8-16 caracteres, al menos un número y un símbolo.";
            errorMsg.style.display = "block";
            passwordInput.style.border = "2px solid red";
        } else {
            errorMsg.style.display = "none";
            passwordInput.style.border = "2px solid green";
        }
    });

    // Validación al enviar el formulario
    formRegistro.addEventListener("submit", (e) => {
        const pass = passwordInput.value;
        if (!regex.test(pass)) {
            e.preventDefault();
            errorMsg.textContent = "No se pudo registrar: contraseña inválida.";
            errorMsg.style.display = "block";
            passwordInput.style.border = "2px solid red";
        }
    });

});
