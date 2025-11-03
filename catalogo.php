<?php
require_once 'php/conexion.php';

// Traer todos los productos con su primera variante (solo para mostrar precio ejemplo)
$sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.material, p.imagen, p.categoria,
        ANY_VALUE(v.precio) as precio
        FROM productos p
        LEFT JOIN variantesProducto v ON p.id_producto = v.id_producto
        GROUP BY p.id_producto
        ORDER BY p.categoria, p.nombre";

        
// Consulta para traer productos con tallas, colores y precio de ejemplo
$sql = "SELECT 
    p.id_producto,
    p.nombre,
    p.descripcion,
    p.material,
    p.imagen,
    p.categoria,
    GROUP_CONCAT(DISTINCT t.nombre_talla ORDER BY t.id_talla SEPARATOR ', ') AS tallas,
    GROUP_CONCAT(DISTINCT c.nombre_color ORDER BY c.id_color SEPARATOR ', ') AS colores,
    ANY_VALUE(v.precio)as precio
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
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div>
    <nav class="menu">
        <a href="index.html">Inicio</a>
        <a href="quienes.html">Quiénes Somos</a>
        <a href="catalogo.html">Catálogo</a>
        <a href="carrito/carrito.html">Carrito</a>
        <a href="registro.html">Registro</a>
        <a href="contacto.html">Contacto</a>
    </nav>
</header>

<section class="section catalogo">
    <h2>Catálogo</h2>

    <?php foreach ($categorias as $categoria => $productosCat): ?>
        <h3 class="categoria-titulo"><?= htmlspecialchars($categoria) ?></h3>
        <div class="productos-grid">
            <?php foreach ($productosCat as $p): ?>
                <div class="producto">
                    <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                    <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                    <p>$<?= number_format($p['precio'], 2) ?> MXN</p>
                    <ul class="detalle-producto">
                        <!--<li><?= htmlspecialchars($p['descripcion']) ?></li> -->
                        <li><strong>Material:</strong> <?= htmlspecialchars($p['material']) ?></li>
                        <li><strong>Tallas:</strong> <?= htmlspecialchars($p['tallas']) ?></li>
                        <li><strong>Colores:</strong> <?= htmlspecialchars($p['colores']) ?></li>
                    </ul>
                    <a href="producto/producto.php?id=<?=$p['id_producto']?>" class="btn-producto">Ver Detalles</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</section>

<footer>
    <p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

</body>
</html>
