<?php
require '../php/conexion.php';

if (!isset($_GET['id'])) {
    echo "Producto no encontrado";
    exit;
}

$id_producto = (int)$_GET['id'];

// 1. Información del producto
$sql_producto = "SELECT * FROM productos WHERE id_producto = $id_producto";
$res_producto = $conn->query($sql_producto);
if (!$res_producto->num_rows) {
    echo "Producto no encontrado";
    exit;
}
$producto = $res_producto->fetch_assoc();

// 2. Traer variantes (tallas, colores, precio)
$sql_variantes = "
SELECT v.id_variante, v.precio, t.id_talla, t.nombre_talla, c.id_color,c.nombre_color
FROM variantesProducto v
JOIN tallas t ON v.id_talla = t.id_talla
JOIN colores c ON v.id_color = c.id_color
WHERE v.id_producto = $id_producto
ORDER BY v.id_variante
";
$res_variantes = $conn->query($sql_variantes);

$tallas = [];
$colores = [];
$variantes = []; // id_variante => info
while($row = $res_variantes->fetch_assoc()) {
    $tallas[$row['id_talla']] = $row['nombre_talla'];
    $colores[$row['id_color']] = [ 'nombre' => $row['nombre_color']];
    $variantes[$row['id_variante']] = $row; // guardamos cada variante
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producto - FaDa Sports</title>
    <link rel="stylesheet" href="producto.css">
</head>
<body>

<header>
    <div class="logo-container">
        <img src="../img/logo.jpg" alt="Logo FaDa Sports" class="logo-img">
        <span class="logo-text">FaDa Sports</span>
    </div>
    <nav class="menu">
        <a href="../index.html">Inicio</a>
        <a href="../catalogo.html">Catálogo</a>
        <a href="../contacto.html">Contacto</a>
    </nav>
</header>
<div class="detalle-container">
    <div class="producto-imagen">
        <img src="../<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
    </div>

    <div class="producto-info">
        <h2><?= htmlspecialchars($producto['nombre']) ?></h2>
        <p><?= htmlspecialchars($producto['descripcion']) ?></p>
        <p class="precio">Precio desde: $<?= number_format(min(array_column($variantes, 'precio')), 2) ?> MXN</p>

        <ul class="detalle-producto">
            <li><strong>Material:</strong> <?= htmlspecialchars($producto['material']) ?></li>
        </ul>

        <!-- Formulario para enviar al carrito -->
        <form action="../php/anadir_carrito.php" method="POST">
            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">

            <!-- Selección de Talla -->
            <div class="seleccion-talla">
                <label for="talla">Elige tu talla:</label>
                <select id="talla" name="talla">
                    <?php foreach ($tallas as $id_talla => $nombre_talla): ?>
                        <option value="<?= $id_talla ?>"><?= htmlspecialchars($nombre_talla) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Selección de Color -->
            <div class="seleccion-talla">
                <label for="color">Elige tu color:</label>
                <select id="color" name="color">
                    <?php foreach ($colores as $id_color => $color): ?>
                        <option value="<?= $id_color ?>"><?= htmlspecialchars($color['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">Agregar al carrito</button>
        </form>

    </div>
</div>


<footer>
    &copy; 2025 FaDa Sports. Todos los derechos reservados.
</footer>

</body>
</html>
