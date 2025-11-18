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

                <div class="producto-carrito" data-id-variante="<?= $item['id_variante'] ?>" data-precio="<?= $item['precio'] ?>" data-stock="<?= $item['stock'] ?>">
                    <img src="../<?= htmlspecialchars($item['imagen']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
                    <div class="info-producto">
                        <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                        <p class="precio">$<?= number_format($item['precio'], 2) ?> MXN</p>
                        <p><strong>Talla:</strong> <?= htmlspecialchars($item['talla']) ?></p>
                        <p><strong>Color:</strong> <?= htmlspecialchars($item['color']) ?></p>
                        <p class="stock-disponible">Stock disponible: <span class="stock-valor"><?= $item['stock'] ?></span></p>

                        <!-- Cantidad: formulario que se enviará automáticamente al cambiar el valor -->
                        <div class="cantidad">
                            <form action="../php/actualizar_carrito.php" method="POST" class="form-cantidad">
                                <input type="hidden" name="id_variante" value="<?= $item['id_variante'] ?>">
                                <label for="cantidad-<?= $item['id_variante'] ?>"><strong>Cantidad:</strong></label>
                                <input type="number" class="input-cantidad" id="cantidad-<?= $item['id_variante'] ?>" name="cantidad" value="<?= $item['cantidad'] ?>" min="1" max="<?= $item['stock'] ?>">
                                <button type="submit" style="display:none">Actualizar</button>
                            </form>
                        </div>
                        <!-- subtotal removed: calculation handled server-side after submit -->
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

<script>
// Actualizar totales cuando cambie alguna cantidad
function formatMoney(n){
    return Number(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function recalcularTotal(){
    let total = 0;
    document.querySelectorAll('.producto-carrito').forEach(function(card){
        const precio = parseFloat(card.dataset.precio);
        const cantidad = parseInt(card.querySelector('.input-cantidad').value) || 0;
        const subtotal = precio * cantidad;
        total += subtotal;
    });
    document.querySelector('.carrito-total h2 span').textContent = '$' + formatMoney(total) + ' MXN';
}

document.querySelectorAll('.input-cantidad').forEach(function(input){
    // almacenar valor previo
    input.dataset.prev = input.value;
    input.addEventListener('change', function(e){
        const card = input.closest('.producto-carrito');
        const max = parseInt(card.dataset.stock) || 0;
        let val = parseInt(input.value) || 0;
        if (val < 1) val = 1;
        if (val > max) {
            alert('La cantidad supera el stock disponible.');
            input.value = input.dataset.prev;
            return;
        }
        // actualizar prev
        input.dataset.prev = val;
        // recalcular localmente
        recalcularTotal();
        // enviar el formulario que envía a actualizar_carrito.php (sin AJAX)
        const form = input.closest('form.form-cantidad');
        if (form) form.submit();
    });
});

// Inicializar total/formato
recalcularTotal();
</script>

</body>
</html>
