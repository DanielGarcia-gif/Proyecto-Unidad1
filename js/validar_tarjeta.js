document.addEventListener("DOMContentLoaded", () => {

    // ===========================================================
    //  1. FORMATO AUTOMÁTICO EN GRUPOS DE 4
    // ===========================================================
    const inputTarjeta = document.getElementById("numero_tarjeta");
    const msgTarjeta = document.getElementById("mensajeTarjeta");
    const form = document.getElementById("formAgregarTarjeta");

    // Tarjetas existentes (últimos 4 dígitos)
    const tarjetasRegistradas = window.tarjetasRegistradas || [];

    function formatearTarjeta(valor) {
        return valor.replace(/\D/g, "").replace(/(.{4})/g, "$1 ").trim();
    }


    // ===========================================================
    //  2. VALIDACIÓN DE TARJETA (Bancos + Luhn)
    // ===========================================================
    const bancosValidos = {
        visa: /^4\d{15}$/,
        mastercard: /^5[1-5]\d{14}$/,
        amex: /^3[47]\d{13}$/
    };

    function luhnCheck(num) {
        let arr = num.split("").reverse().map(n => parseInt(n));
        let sum = 0;

        for (let i = 0; i < arr.length; i++) {
            let n = arr[i];

            if (i % 2 === 1) {
                n *= 2;
                if (n > 9) n -= 9;
            }

            sum += n;
        }
        return sum % 10 === 0;
    }

    function validarNumeroTarjeta(num) {
        num = num.replace(/\s/g, "");

        if (!/^\d*$/.test(num)) return "❌ Solo números permitidos";
        if (num.length < 15) return `❌ Faltan dígitos (${num.length}/16)`;
        if (num.length > 16) return "❌ Demasiados dígitos";

        const esVisa = bancosValidos.visa.test(num);
        const esMaster = bancosValidos.mastercard.test(num);
        const esAmex = bancosValidos.amex.test(num);

        if (!esVisa && !esMaster && !esAmex)
            return "❌ Prefijo no pertenece a Visa, Mastercard o AMEX";

        if (!luhnCheck(num)) return "❌ Tarjeta inválida (falló Luhn)";

        return "";
    }


    // ===========================================================
    //  3. VALIDACIÓN DE FECHA MM/AA
    // ===========================================================
    const inputExp = document.getElementById("expiracion");
    const msgExp = document.getElementById("error-exp");

    function validarFechaVencimiento(valor) {
        if (!/^\d{2}\/\d{2}$/.test(valor)) return "❌ Formato inválido, usa MM/AA";

        const [mesStr, anioStr] = valor.split("/");
        const mes = parseInt(mesStr, 10);
        let anio = parseInt(anioStr, 10);

        if (mes < 1 || mes > 12) return "❌ Mes inválido (01-12)";

        const anioActualCompleto = new Date().getFullYear();
        const base = Math.floor(anioActualCompleto / 100) * 100;
        anio += base;

        const ahora = new Date();
        const mesActual = ahora.getMonth() + 1;
        const anioActual = ahora.getFullYear();

        if (anio < anioActual) return "❌ Tarjeta vencida";
        if (anio === anioActual && mes < mesActual) return "❌ Tarjeta vencida";

        return "";
    }


    // ===========================================================
    //  4. DETECTAR TARJETA REPETIDA (Ultimate 4 digits)
    // ===========================================================
    function tarjetaYaExiste(num) {
        num = num.replace(/\s/g, "");
        const ultimos4 = num.slice(-4);

        return tarjetasRegistradas.includes(ultimos4);
    }


    // ===========================================================
    //  5. EVENTOS DE INPUT
    // ===========================================================
    if (inputTarjeta && msgTarjeta) {
        inputTarjeta.addEventListener("input", () => {

            // Formatear automáticamente
            inputTarjeta.value = formatearTarjeta(inputTarjeta.value);

            // Validación
            const soloNum = inputTarjeta.value.replace(/\s/g, "");
            let error = validarNumeroTarjeta(soloNum);

            // Verificación de duplicados
            if (!error && soloNum.length >= 15 && tarjetaYaExiste(soloNum)) {
                error = "❌ Esta tarjeta ya está registrada";
            }

            msgTarjeta.textContent = error;
            msgTarjeta.style.color = error ? "red" : "green";
        });
    }


    if (inputExp && msgExp) {
        inputExp.addEventListener("input", () => {
            if (inputExp.value.length === 2 && !inputExp.value.includes("/")) {
                inputExp.value += "/";
            }
            const error = validarFechaVencimiento(inputExp.value);
            msgExp.textContent = error;
            msgExp.style.color = error ? "red" : "green";
        });
    }


    // ===========================================================
    //  6. VALIDACIÓN FINAL ANTES DE ENVIAR
    // ===========================================================
    if (form) {
        form.addEventListener("submit", (e) => {
            const tarjeta = inputTarjeta.value.replace(/\s/g, "");
            const fecha = inputExp.value;

            const errorNum = validarNumeroTarjeta(tarjeta);
            const errorFecha = validarFechaVencimiento(fecha);
            const duplicada = tarjetaYaExiste(tarjeta);

            if (errorNum || errorFecha || duplicada) {
                e.preventDefault();
                alert(
                    "Corrige los errores antes de guardar:\n" +
                    (errorNum ? errorNum + "\n" : "") +
                    (duplicada ? "❌ Esta tarjeta ya existe\n" : "") +
                    (errorFecha ? errorFecha : "")
                );
            }
        });
    }

});
