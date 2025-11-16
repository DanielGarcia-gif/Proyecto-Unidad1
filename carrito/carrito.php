<?php
session_start();
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - FaDa Sports</title>
    <link rel="icon" type="../img/logo.jpg" href="../img/logo.jpg">
    <link rel="stylesheet" href="carrito.css">
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div>
    <nav class="menu">
        <a href="../index.php">Inicio</a>
        <a href="../catalogo.php">Catálogo</a>
        <a href="../contacto.html">Contacto</a>
    </nav>
</header>

<main class="carrito-container">
    <h1>Tu Carrito</h1>

    <div class="carrito-productos">
        <?php if (empty($carrito)): ?>
            <p>No tienes productos en tu carrito.</p>
        <?php else: ?>
            <?php foreach ($carrito as $item): ?>
                <?php $subtotal = $item['precio'] * $item['cantidad']; ?>
                <?php $total += $subtotal; ?>

                <div class="producto-carrito">
                    <img src="../<?= htmlspecialchars($item['imagen']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
                    <div class="info-producto">
                        <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                        <p class="precio">$<?= number_format($item['precio'], 2) ?> MXN</p>
                        <p><strong>Talla:</strong> <?= htmlspecialchars($item['talla']) ?></p>
                        <p><strong>Color:</strong> <?= htmlspecialchars($item['color']) ?></p>
                        <!-- Actualizar cantidad -->
                        <form action="../php/actualizar_carrito.php" method="POST" class="cantidad">
                            <input type="hidden" name="id_variante" value="<?= $item['id_variante'] ?>">
                            <label for="cantidad"><strong>Cantidad:</strong></label>
                            <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="1" max="<?= $item['stock'] ?>">
                            <button type="submit" class="btn-actualizar">Actualizar</button>
                        </form>
                    </div>
                    <form action="../php/eliminar_carrito.php" method="POST">
                        <input type="hidden" name="id_variante" value="<?= $item['id_variante'] ?>">
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="carrito-total">
        <h2>Total: <span>$<?= number_format($total, 2) ?> MXN</span></h2>
        <?php if ($total > 0): ?>
            <form action="../detalle_compra.php" method="POST">
                <button type="submit" class="btn-pagar">Proceder al pago</button>
            </form>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>© 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

</body>
</html>
