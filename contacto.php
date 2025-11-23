<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FaDa Sports - Contacto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div>
    <nav class="menu">
        <a href="index.php">Inicio</a>
        <a href="quienes.html">Quiénes Somos</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="carrito/carrito.php">Carrito</a>
        <a href="registro.php">Registro</a>
        <a href="contacto.php">Contacto</a>
    </nav>
</header>

<section class="section contacto">
    <div class="form-container">
        <!-- Lado izquierdo: Formulario de contacto -->
        <div class="form-tabs contacto-form-tab">
            <h2>Contacto</h2>
            <p>¿Tienes dudas o comentarios? Contáctanos.</p>
            <form id="contacto-form" class="contacto-form">
                <input type="text" id="nombre" placeholder="Nombre completo" required>
                <input type="email" id="correo" placeholder="Correo electrónico" required>
                <textarea id="mensaje" placeholder="Mensaje" rows="5" required></textarea>
                <input type="submit" value="Enviar" class="btn">
            </form>
        </div>

        <!-- Lado derecho: Imagen del logo -->
        <div class="side-logo">
            <img src="img/logo.jpg" alt="FaDa Sports Logo">
        </div>
    </div>
</section>

<footer>
    <p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
<script>
    (function() {
        emailjs.init("eLASeCbf6lYtoQd5N"); 
    })();

    document.getElementById("contacto-form").addEventListener("submit", function(e){
        e.preventDefault();

        const params = {
            nombre: document.getElementById("nombre").value,
            correo: document.getElementById("correo").value,
            mensaje: document.getElementById("mensaje").value
        };

        emailjs.send("service_aknfa2c", "template_i2sngaw", params)
        .then(function(){
            alert("Mensaje enviado. ¡Gracias por contactarnos!");
            document.getElementById("contact-formo").reset();
        }, function(error){
            console.log("Fallo al enviar", error);
            alert("Error al enviar el mensaje.");
        });
    });
</script>
</body>
</html>
