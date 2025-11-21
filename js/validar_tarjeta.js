document.addEventListener("DOMContentLoaded", () => {

    const inputTarjeta = document.getElementById("numero_tarjeta");
    const msgTarjeta = document.getElementById("mensajeTarjeta");
    const form = document.getElementById("formAgregarTarjeta");
    const icono = document.getElementById("icono-tarjeta");

    const tarjetasRegistradas = window.tarjetasRegistradas || [];

    function formatearTarjeta(valor) {
        return valor.replace(/\D/g, "").replace(/(.{4})/g, "$1 ").trim();
    }

 
    const bancosValidos = {
        visa: /^4\d{12}(\d{3})?$/,             // 13 o 16 dígitos
        mastercard: /^5[1-5]\d{14}$/,          // 16 dígitos
        amex: /^3[47]\d{13}$/                  // 15 dígitos
    };

    function detectarMarca(num) {
        if (bancosValidos.visa.test(num)) return "visa";
        if (bancosValidos.mastercard.test(num)) return "mastercard";
        if (bancosValidos.amex.test(num)) return "amex";
        return "";
    }

    function validarNumeroTarjeta(num) {
        num = num.replace(/\s/g, "");

        if (!/^\d*$/.test(num)) return "❌ Solo números permitidos";

        if (num.length < 13) return `❌ Faltan dígitos (${num.length}/16)`;
        if (num.length > 16) return "❌ Demasiados dígitos";

        const marca = detectarMarca(num);
        if (!marca) return "❌ Prefijo no válido (Visa, Mastercard o AMEX)";

        return "";
    }


    const inputExp = document.getElementById("expiracion");
    const msgExp = document.getElementById("error-exp");

    function validarFechaVencimiento(valor) {
        if (!/^\d{2}\/\d{2}$/.test(valor)) return "❌ Formato inválido, usa MM/AA";

        const [mesStr, anioStr] = valor.split("/");
        const mes = parseInt(mesStr, 10);
        let anio = parseInt(anioStr, 10);

        if (mes < 1 || mes > 12) return "❌ Mes inválido (01-12)";

        const actual = new Date();
        const base = Math.floor(actual.getFullYear() / 100) * 100;
        anio += base;

        if (anio < actual.getFullYear() ||
            (anio === actual.getFullYear() && mes < actual.getMonth() + 1)) {
            return "❌ Tarjeta vencida";
        }

        return "";
    }


    function tarjetaYaExiste(num) {
        num = num.replace(/\s/g, "");
        const ultimos4 = num.slice(-4);
        return tarjetasRegistradas.includes(ultimos4);
    }


    inputTarjeta.addEventListener("input", () => {

        inputTarjeta.value = formatearTarjeta(inputTarjeta.value);
        const soloNum = inputTarjeta.value.replace(/\s/g, "");

        // Detectar marca y mostrar icono
        const marca = detectarMarca(soloNum);
        if (marca) {
            icono.src = `img/${marca}.png`;  
            icono.style.display = "block";
        } else {
            icono.style.display = "none";
        }

        let error = validarNumeroTarjeta(soloNum);

        if (!error && soloNum.length >= 13 && tarjetaYaExiste(soloNum)) {
            error = "❌ Esta tarjeta ya está registrada";
        }

        msgTarjeta.textContent = error;
        msgTarjeta.style.color = error ? "red" : "green";
    });

    inputExp.addEventListener("input", () => {
        if (inputExp.value.length === 2 && !inputExp.value.includes("/")) {
            inputExp.value += "/";
        }
        const error = validarFechaVencimiento(inputExp.value);
        msgExp.textContent = error;
        msgExp.style.color = error ? "red" : "green";
    });


    form.addEventListener("submit", (e) => {
        const tarjeta = inputTarjeta.value.replace(/\s/g, "");
        const fecha = inputExp.value;

        const errorNum = validarNumeroTarjeta(tarjeta);
        const errorFecha = validarFechaVencimiento(fecha);
        const duplicada = tarjetaYaExiste(tarjeta);

        if (errorNum || errorFecha || duplicada) {
            e.preventDefault();
            alert(
                "Corrige los errores:\n" +
                (errorNum ? errorNum + "\n" : "") +
                (duplicada ? "❌ Esta tarjeta ya existe\n" : "") +
                (errorFecha ? errorFecha : "")
            );
        }
    });

});
