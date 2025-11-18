<?php
session_start();
require 'php/conexion.php';

// OBTENER ID DEL USUARIO
$idUsuario = $_SESSION['id_usuario'] ?? null;
$direcciones = [];

// OBTENER DIRECCIONES SI ESTA LOGUEADO
if ($idUsuario) {
    $sqlDir = "SELECT id_direccion, direccion, ciudad, codigo_postal, es_predeterminada
               FROM direccionesUsuario
               WHERE id_usuario = $idUsuario";
    $resDir = $conn->query($sqlDir);

    if ($resDir && $resDir->num_rows > 0) {
        while ($d = $resDir->fetch_assoc()) {
            $direcciones[] = $d;
        }
    }
}

// CARRITO VACÍO
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "Tu carrito está vacío.";
    exit;
}

// PROCESAR CARRITO
$carrito = $_SESSION['carrito'];
$totalProductos = 0;
$detalleCarrito = [];

foreach ($carrito as $item) {
    $id_variante = $item['id_variante'];
    $sql = "SELECT v.*, p.nombre AS nombre_producto, c.nombre_color, t.nombre_talla
            FROM variantesProducto v
            JOIN productos p ON v.id_producto = p.id_producto
            JOIN colores c ON v.id_color = c.id_color
            JOIN tallas t ON v.id_talla = t.id_talla
            WHERE v.id_variante = $id_variante";

    $res = $conn->query($sql);
    if ($res->num_rows) {
        $row = $res->fetch_assoc();
        $row['cantidad'] = $item['cantidad'];
        $row['subtotal'] = $row['cantidad'] * $row['precio'];
        $totalProductos += $row['subtotal'];
        $detalleCarrito[] = $row;
    }
}

$costoEnvio = 80;
$totalFinal = $totalProductos + $costoEnvio;

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle de Compra - FaDa Sports</title>
<link rel="icon" type="image/jpg" href="img/logo.jpg">
<link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div>
    <nav class="menu">
        <a href="index.php">Inicio</a>
        <a href="catalogo.php">Catálogo</a>
        <a href="../carrito/carrito.php">Carrito</a>
    </nav>
</header>

<section class="detalle-compra">
    <h2>Resumen de tu pedido</h2>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Talla</th>
                <th>Color</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($detalleCarrito as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nombre_producto']) ?></td>
                <td><?= htmlspecialchars($item['nombre_talla']) ?></td>
                <td><?= htmlspecialchars($item['nombre_color']) ?></td>
                <td><?= $item['cantidad'] ?></td>
                <td>$<?= number_format($item['precio'],2) ?></td>
                <td>$<?= number_format($item['subtotal'],2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="resumen-final">
        <form action="php/procesar_pago.php" method="POST">
            <h3>Información de Envío</h3>

            <?php if (!empty($direcciones)): ?>
                <label>Elige una dirección guardada:</label>
                <select name="id_direccion" required>
                    <?php foreach ($direcciones as $dir): ?>
                        <option value="<?= $dir['id_direccion'] ?>">
                            <?= htmlspecialchars($dir['direccion']) ?>, 
                            <?= htmlspecialchars($dir['ciudad']) ?>, 
                            CP <?= htmlspecialchars($dir['codigo_postal']) ?>
                            <?= $dir['es_predeterminada'] ? ' (Predeterminada)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            <?php else: ?>
                <p style="color:red;">No tienes direcciones guardadas. Agrega una a continuacion.</p>
                <p style="color:red;">Puedes agregar direcciones de envio en tu perfil.</p>

                <label>Dirección:</label>
                <input type="text" name="direccion" required>

                <label>Ciudad:</label>
                <input type="text" name="ciudad" required>

                <label>Código Postal:</label>
                <input type="text" name="codigo_postal" required>
            <?php endif; ?>

            <!--  CAMPOS OCULTOS PARA ENVIAR EL CARRITO COMPLETO -->
            <?php foreach($detalleCarrito as $item): ?>
                <input type="hidden" name="id_variante[]" value="<?= $item['id_variante'] ?>">
                <input type="hidden" name="cantidad[]" value="<?= $item['cantidad'] ?>">
            <?php endforeach; ?>

            <input type="hidden" name="total" value="<?= $totalFinal ?>">
            <input type="hidden" name="costo_envio" value="<?= $costoEnvio ?>">

            <p><strong>Total de Productos:</strong> $<?= number_format($totalProductos,2) ?></p>
            <p><strong>Costo de Envío:</strong> $<?= number_format($costoEnvio,2) ?></p>
            <p><strong>Total Final:</strong> $<?= number_format($totalFinal,2) ?></p>

            <button type="submit" name="metodo_pago" value="tarjeta" class="btn-confirmar">Pagar con Tarjeta</button>
            <button type="submit" name="metodo_pago" value="paypal" class="btn-confirmar">Pagar con PayPal</button>
        </form>
    </div>
</section>

<footer>
<p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

</body>
</html>
