<?php
session_start();
require_once 'php/conexion.php';

// Consulta productos con tallas, colores y precio mínimo
$sql = "SELECT 
    p.id_producto,
    p.nombre,
    p.descripcion,
    p.material,
    p.imagen,
    p.categoria,
    GROUP_CONCAT(DISTINCT t.nombre_talla ORDER BY t.id_talla SEPARATOR ', ') AS tallas,
    GROUP_CONCAT(DISTINCT c.nombre_color ORDER BY c.id_color SEPARATOR ', ') AS colores,
    MIN(v.precio) AS precio
    FROM productos p
    LEFT JOIN variantesProducto v ON p.id_producto = v.id_producto
    LEFT JOIN tallas t ON v.id_talla = t.id_talla
    LEFT JOIN colores c ON v.id_color = c.id_color
    GROUP BY p.id_producto
    ORDER BY p.categoria, p.nombre";

$result = $conn->query($sql);

$categorias = [];
if ($result->num_rows > 0) {
    while ($p = $result->fetch_assoc()) {
        $categorias[$p['categoria']][] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FaDa Sports - Catálogo</title>
    <link rel="icon" type="img/logo.jpg" href="img/logo.jpg">
    <link rel="stylesheet" href="styles.css">
    <!-- OwlCarousel CSS (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" />
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div>
    <nav class="menu">
        <a href="index.php">Inicio</a>
        <a href="quienes.php">Quiénes Somos</a>
        <a href="catalogo.php" class="activo">Catálogo</a>
        <a href="carrito/carrito.php">Carrito</a>

        <?php if (isset($_SESSION['id_usuario'])): ?>
            <a href="perfil.php">Mi Perfil (<?= htmlspecialchars($_SESSION['nombre']) ?>)</a>
        <?php else: ?>
            <a href="registro.php">Registro / Login</a>
        <?php endif; ?>

        <a href="contacto.php">Contacto</a>
    </nav>
</header>

<section class="section catalogo">
    <h2>Catálogo</h2>

    <?php foreach ($categorias as $categoria => $productosCat): ?>
    <h3 class="categoria-titulo"><?= htmlspecialchars($categoria) ?></h3>

    <div class="carrusel-contenedor">
        <button class="carrusel-btn prev">❮</button>

        <div class="carrusel" data-categoria="<?= htmlspecialchars($categoria) ?>">
            <?php foreach ($productosCat as $p): ?>
                <div class="producto item">
                    <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                    <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                    <p>$<?= number_format($p['precio'], 2) ?> MXN</p>

        <div class="carousel-wrapper">
            
            <button class="carousel-nav carousel-prev">
                <img src="img/flecha-izquierda.png" alt="prev">
            </button>

            <div class="owl-carousel owl-theme productos-carousel">
                <?php foreach ($productosCat as $p): ?>
                    <div class="item producto-card">
                        <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <h3><?= htmlspecialchars($p['nombre']) ?></h3>

                        <p class="precio">$<?= number_format($p['precio'], 2) ?> MXN</p>

                        <ul class="detalle-producto">
                            <li><strong>Material:</strong> <?= htmlspecialchars($p['material']) ?></li>
                            <li><strong>Tallas:</strong> <?= htmlspecialchars($p['tallas']) ?></li>
                            <li><strong>Colores:</strong> <?= htmlspecialchars($p['colores']) ?></li>
                        </ul>

                        <a href="producto/producto.php?id=<?= $p['id_producto'] ?>" class="btn-producto">Ver Detalles</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-nav carousel-next">
                <img src="img/flecha-derecha.png" alt="next">
            </button>

        </div>

        <button class="carrusel-btn next">❯</button>
    </div>
<?php endforeach; ?>

</section>

<footer>
    <p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

<!-- jQuery + OwlCarousel JS (CDN) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<script>
        $(document).ready(function(){

        $('.carousel-wrapper').each(function(){

            const $wrapper = $(this);
            const $owl = $wrapper.find('.owl-carousel');

            $owl.owlCarousel({
                loop: true,
                margin: 20,
                dots: false,
                nav: false,
                center: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                smartSpeed: 600,
                responsive:{
                    0:{ items:1 },
                    600:{ items:2 },
                    1000:{ items:3 }
                }
            });

            $wrapper.find('.carousel-prev').click(function(){
                $owl.trigger('prev.owl.carousel');
            });

            $wrapper.find('.carousel-next').click(function(){
                $owl.trigger('next.owl.carousel');
            });

        });

        });
</script>

</body>
</html>
