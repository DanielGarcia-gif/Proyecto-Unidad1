<?php
session_start();
require '../php/conexion.php';

// Si no hay usuario logueado redirigir
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_carrito'])) {
    header("Location: ../registro.php");
    exit;
}

$id_carrito = $_SESSION['id_carrito'];

$sql = "SELECT 
            cd.id_variante,
            cd.cantidad,
            v.precio,
            v.stock,
            p.nombre,
            p.imagen,
            t.nombre_talla,
            c.nombre_color
        FROM carrito_detalle cd
        INNER JOIN variantesproducto v ON cd.id_variante = v.id_variante
        INNER JOIN productos p ON v.id_producto = p.id_producto
        INNER JOIN tallas t ON v.id_talla = t.id_talla
        INNER JOIN colores c ON v.id_color = c.id_color
        WHERE cd.id_carrito = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_carrito);
$stmt->execute();
$res = $stmt->get_result();

$carrito = [];
$total = 0;

while ($row = $res->fetch_assoc()) {
    $row['subtotal'] = $row['precio'] * $row['cantidad'];
    $total += $row['subtotal'];
    $carrito[] = $row;
}

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
        <?php if (!empty($_SESSION['id_usuario'])): ?>
            <a href="../perfil.php">Mi Perfil (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>

            <?php
                $esAdmin = (!empty($_SESSION['rol_nombre']) && $_SESSION['rol_nombre'] === 'Admin');
            ?>

            <?php if ($esAdmin): ?>
                <a href="admin/dashboard.php">Admin</a>
            <?php endif; ?>

        <?php else: ?>
            <a href="../registro.php">Registro / Login</a>
        <?php endif; ?>

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
                <div class="producto-carrito" 
                     data-id-variante="<?= $item['id_variante'] ?>"
                     data-precio="<?= $item['precio'] ?>"
                     data-stock="<?= $item['stock'] ?>">

                    <img src="../<?= htmlspecialchars($item['imagen']) ?>" 
                         alt="<?= htmlspecialchars($item['nombre']) ?>">

                    <div class="info-producto">
                        <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                        <p class="precio">$<?= number_format($item['precio'], 2) ?> MXN</p>
                        <p><strong>Talla:</strong> <?= htmlspecialchars($item['nombre_talla']) ?></p>
                        <p><strong>Color:</strong> <?= htmlspecialchars($item['nombre_color']) ?></p>
                        <p class="stock-disponible">
                            Stock disponible: 
                            <span class="stock-valor"><?= $item['stock'] ?></span>
                        </p>

                        <!-- Actualizar cantidad -->
                        <div class="cantidad">
                            <form action="../php/actualizar_carrito.php" method="POST" class="form-cantidad">
                                <input type="hidden" name="id_variante" value="<?= $item['id_variante'] ?>">
                                <label><strong>Cantidad:</strong></label>
                                <input type="number" 
                                       class="input-cantidad" 
                                       name="cantidad" 
                                       value="<?= $item['cantidad'] ?>" 
                                       min="1" 
                                       max="<?= $item['stock'] ?>">
                                <button type="submit" style="display:none">Actualizar</button>
                            </form>
                        </div>
                    </div>

                    <!-- Eliminar -->
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
function formatMoney(n){
    return Number(n).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
}

// Recalcular total localmente
function recalcularTotal(){
    let total = 0;
    document.querySelectorAll('.producto-carrito').forEach(card => {
        const precio = parseFloat(card.dataset.precio);
        const cantidad = parseInt(card.querySelector('.input-cantidad').value) || 0;
        total += precio * cantidad;
    });
    document.querySelector('.carrito-total h2 span').textContent = '$' + formatMoney(total) + ' MXN';
}

document.querySelectorAll('.input-cantidad').forEach(input => {
    input.dataset.prev = input.value;
    input.addEventListener('change', () => {
        const card = input.closest('.producto-carrito');
        const max = parseInt(card.dataset.stock);
        let val = parseInt(input.value) || 1;

        if (val < 1) val = 1;
        if (val > max) {
            alert('La cantidad supera el stock disponible.');
            input.value = input.dataset.prev;
            return;
        }

        input.dataset.prev = val;
        recalcularTotal();

        const form = input.closest('form.form-cantidad');
        if (form) form.submit();
    });
});

recalcularTotal();
</script>

</body>
</html>
