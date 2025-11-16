<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FaDa Sports - Inicio</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div> 
    <nav class="menu">
        <a href="index2.php">Inicio</a>
        <a href="quienes.html">Quiénes Somos</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="carrito/carrito.php">Carrito</a>

        <?php if (!empty($_SESSION['id_usuario'])): ?>
            <!-- Cambiado: Antes era logout.php -->
            <a href="perfil.php">Mi Perfil (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>

            <?php
                // Mostrar enlace Admin solo si el rol en sesión es Admin
                $esAdmin = false;
                if (!empty($_SESSION['rol_nombre'])) {
                    $esAdmin = ($_SESSION['rol_nombre'] === 'Admin');
                } elseif (!empty($_SESSION['id_rol'])) {
                    $esAdmin = ($_SESSION['id_rol'] == 2);
                }
            ?>

            <?php if ($esAdmin): ?>
                <a href="admin/dashboard.php">Admin</a>
            <?php endif; ?>

        <?php else: ?>
            <a href="registro.php">Registro / Login</a>
        <?php endif; ?>

        <a href="contacto.html">Contacto</a>
    </nav>
</header>

<section class="section inicio">
    <img src="img/indexImagen.jpg" alt="Ropa deportiva" class="inicio-fondo">
    <div class="inicio-overlay"></div>
    <div class="inicio-textoI">
        <h1>Bienvenido a <span>FaDa Sports</span></h1>
        <p>La mejor tienda de ropa deportiva en línea. Calidad, comodidad y estilo para tu entrenamiento, con garantía de satisfacción y envíos rápidos a todo el país.</p>
        <a href="catalogo.php" class="btn btn-gradiente">Ver Catálogo</a>
    </div>
</section>

<section class="section destacados">
    <h2>Productos Destacados</h2>
    <div class="productos-grid">
        <div class="producto">
            <img src="img/Catalogo/Tenis/tenisDep2.jpeg" alt="Producto 1">
            <h3>Tenis Running</h3>
            <p>Comodidad y rendimiento para tus entrenamientos.</p>
            <a href="catalogo.php" class="btn btn-gradiente">Comprar</a>
        </div>
        <div class="producto">
            <img src="img/Catalogo/Sudaderas/sudaderaDep.jpeg" alt="Producto 2">
            <h3>Sudadera Deportiva</h3>
            <p>Perfecta para entrenar o salir con estilo.</p>
            <a href="catalogo.php" class="btn btn-gradiente">Comprar</a>
        </div>
        <div class="producto">
            <img src="img/Catalogo/Pantalones/pantalonDep2.jpeg" alt="Producto 3">
            <h3>Shorts de Entrenamiento</h3>
            <p>Ligeros, cómodos y resistentes.</p>
            <a href="catalogo.php" class="btn btn-gradiente">Comprar</a>
        </div>
    </div>
</section>

<section class="section beneficios">
    <h2>¿Por qué elegirnos?</h2>
    <div class="beneficios-grid">
        <div class="beneficio">
            <div class="icono"><img src="img/Index/envio.png" alt="Envío rápido"></div>
            <h3>Envío rápido</h3>
            <p>Recibe tus pedidos en tiempo récord, directo a tu puerta.</p>
        </div>
        <div class="beneficio">
            <div class="icono"><img src="img/Index/calidad.png" alt="Productos de calidad"></div>
            <h3>Productos de calidad</h3>
            <p>Trabajamos solo con marcas confiables y materiales de primera.</p>
        </div>
        <div class="beneficio">
            <div class="icono"><img src="img/Index/garantia.png" alt="Garantía"></div>
            <h3>Garantía</h3>
            <p>Compra con total confianza y respaldo en cada producto.</p>
        </div>
    </div>
</section>

<footer>
    <p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

</body>
</html>
