<?php
session_start();
$register_error = $_SESSION['register_error'] ?? null;
$login_error = $_SESSION['login_error'] ?? null;
unset($_SESSION['register_error'], $_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FaDa Sports - Usuario</title>
    <link rel="icon" type="img/logo.jpg" href="img/logo.jpg">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div>
    <nav class="menu">
        <a href="index.php">Inicio</a>
        <a href="quienes.php">Quiénes Somos</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="carrito/carrito.php">Carrito</a>
        <a href="registro.php">Registro</a>
        <a href="contacto.html">Contacto</a>
    </nav>
</header>

<section class="section usuario">
    <div class="form-container">
        <!-- Lado izquierdo: Imagen del logo -->
        <div class="side-logo">
            <img src="img/logo.jpg" alt="FaDa Sports Logo">
        </div>

        <div class="form-tabs">
            <!-- Radios para controlar la pestaña -->
            <input type="radio" name="tab" id="tab-login" checked>
            <input type="radio" name="tab" id="tab-registro">

            <!-- Etiquetas que funcionan como botones de pestañas -->
            <div class="tabs">
                <label for="tab-login" class="tab-btn">Iniciar Sesión</label>
                <label for="tab-registro" class="tab-btn">Registro</label>
            </div>

            <!-- Formularios -->
            <div class="forms-container">
                <div class="form-box login-box">
                    <h2 style="padding-bottom: 10px;">Iniciar Sesión</h2>
                    <?php if ($login_error): ?><div class="alert error"><?php echo htmlspecialchars($login_error); ?></div><?php endif; ?>
                    <form method="post" action="php/login.php">
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                        <input type="password" name="password" placeholder="Contraseña" required>
                        <input type="submit" value="Ingresar" class="btn" style="margin-top: 40px;">
                    </form>
                    <form method="post" action="php/login_invitado.php">
                        <button type="submit" class="btn" style="margin-top: 15px; background:#444;">
                            Ingresar sin cuenta
                        </button>
                    </form>
                </div>

                <div class="form-box registro-box">
                    <h2 style="padding-bottom: 10px;">Registro</h2>
                    <?php if ($register_error): ?><div class="alert error"><?php echo htmlspecialchars($register_error); ?></div><?php endif; ?>
                    <form method="post" action="php/register.php">
                        <input type="text" name="nombre" placeholder="Nombre completo" required>
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                        <input type="password" name="password" placeholder="Contraseña" required>
                        <input type="tel" name="telefono" placeholder="Teléfono (opcional)">
                        <input type="submit" value="Registrarse" class="btn">
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


<footer>
    <p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

</body>
</html>