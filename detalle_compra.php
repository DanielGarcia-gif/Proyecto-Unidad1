<?php
session_start();
require 'php/conexion.php';

$idUsuario = $_SESSION['id_usuario'] ?? null;
$id_carrito = $_SESSION['id_carrito'] ?? null;
$direcciones = [];
$detalleCarrito = [];
$totalProductos = 0;
$costoEnvio = 80;

/* ============================================================
   1) OBTENER DIRECCIONES DEL USUARIO (SIN CAMBIOS)
   ============================================================ */
if ($idUsuario) {
    $sqlDir = "SELECT id_direccion, direccion, ciudad, codigo_postal, es_predeterminada
               FROM direccionesUsuario
               WHERE id_usuario = ?";
    if ($stmtDir = $conn->prepare($sqlDir)) {
        $stmtDir->bind_param("i", $idUsuario);
        $stmtDir->execute();
        $resDir = $stmtDir->get_result();
        while ($d = $resDir->fetch_assoc()) {
            $direcciones[] = $d;
        }
        $stmtDir->close();
    }
}

/* ============================================================
   2) SI EL USUARIO ESTÁ LOGUEADO PERO NO TIENE id_carrito EN SESIÓN,
      LO BUSCAMOS AUTOMÁTICAMENTE.
   ============================================================ */
if ($idUsuario && !$id_carrito) {
    $sqlBuscar = "SELECT id_carrito FROM carrito WHERE id_usuario = ? ORDER BY id_carrito DESC LIMIT 1";
    if ($stmt = $conn->prepare($sqlBuscar)) {
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $id_carrito = $row['id_carrito'];
            $_SESSION['id_carrito'] = $id_carrito; // 👈 se guarda
        }
        $stmt->close();
    }
}

/* ============================================================
   3) CARGAR CARRITO DESDE BD (SI HAY LOGIN)
   ============================================================ */
if ($idUsuario && $id_carrito) {

    $sql = "SELECT cd.id_variante, cd.cantidad, v.precio, v.stock, 
                   p.nombre AS nombre_producto, p.imagen,
                   c.nombre_color, t.nombre_talla
            FROM carrito_detalle cd
            INNER JOIN variantesProducto v ON cd.id_variante = v.id_variante
            INNER JOIN productos p ON v.id_producto = p.id_producto
            INNER JOIN colores c ON v.id_color = c.id_color
            INNER JOIN tallas t ON v.id_talla = t.id_talla
            WHERE cd.id_carrito = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_carrito);
        $stmt->execute();
        $res = $stmt->get_result();

        // Si el carrito en BD tiene productos ←✔
        while ($row = $res->fetch_assoc()) {
            $row['subtotal'] = $row['cantidad'] * $row['precio'];
            $totalProductos += $row['subtotal'];
            $detalleCarrito[] = $row;
        }

        $stmt->close();
    }
}

/* ============================================================
   4) Fallback: carrito en sesión (solo para invitados)
   ============================================================ */
if (empty($detalleCarrito) && isset($_SESSION['carrito'])) {

    foreach ($_SESSION['carrito'] as $item) {

        $id_variante = (int)$item['id_variante'];

        $sql = "SELECT v.precio, v.stock, p.nombre AS nombre_producto,
                       p.imagen, c.nombre_color, t.nombre_talla
                FROM variantesProducto v
                JOIN productos p ON v.id_producto = p.id_producto
                JOIN colores c ON v.id_color = c.id_color
                JOIN tallas t ON v.id_talla = t.id_talla
                WHERE v.id_variante = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id_variante);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $row['cantidad'] = (int)$item['cantidad'];
                $row['id_variante'] = $id_variante;
                $row['subtotal'] = $row['cantidad'] * $row['precio'];
                $totalProductos += $row['subtotal'];
                $detalleCarrito[] = $row;
            }

            $stmt->close();
        }
    }
}

/* ============================================================
   5) CALCULAR TOTAL
   ============================================================ */
$totalFinal = $totalProductos + $costoEnvio;

/* ============================================================
   6) OBTENER TARJETAS DEL USUARIO (SIN CAMBIOS)
   ============================================================ */
$tarjetasUsuario = [];
if ($idUsuario) {
    $sqlTar = "SELECT id_tarjeta, numero_tarjeta, marca
               FROM tarjetasUsuario
               WHERE id_usuario = ?";

    if ($stmtTar = $conn->prepare($sqlTar)) {
        $stmtTar->bind_param("i", $idUsuario);
        $stmtTar->execute();
        $resTar = $stmtTar->get_result();

        while ($t = $resTar->fetch_assoc()) {
            $t['numero_tarjeta'] = '**** **** **** ' . substr($t['numero_tarjeta'], -4);
            $tarjetasUsuario[] = $t;
        }

        $stmtTar->close();
    }
}
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
        <a href="carrito/carrito.php">Carrito</a>
    </nav>
</header>

<section class="detalle-compra">
    <h2>Resumen de tu pedido</h2>

    <?php if (empty($detalleCarrito)): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>

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
                <td><?= (int)$item['cantidad'] ?></td>
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
                <p style="color:red;">No tienes direcciones guardadas. Puedes agregar una en tu perfil.</p>

                <label>Dirección:</label>
                <input type="text" name="direccion" required>

                <label>Ciudad:</label>
                <input type="text" name="ciudad" required>

                <label>Código Postal:</label>
                <input type="text" name="codigo_postal" required>
            <?php endif; ?>

            <!-- Si existe id_carrito lo enviamos para que procesar_pago lo use; si no, enviamos arrays id_variante[] y cantidad[] -->
            <?php if ($id_carrito): ?>
                <input type="hidden" name="id_carrito" value="<?= (int)$id_carrito ?>">
            <?php else: ?>
                <?php foreach($detalleCarrito as $item): ?>
                    <input type="hidden" name="id_variante[]" value="<?= (int)$item['id_variante'] ?>">
                    <input type="hidden" name="cantidad[]" value="<?= (int)$item['cantidad'] ?>">
                <?php endforeach; ?>
            <?php endif; ?>

            <input type="hidden" name="total" value="<?= number_format($totalFinal, 2, '.', '') ?>">
            <input type="hidden" name="costo_envio" value="<?= number_format($costoEnvio, 2, '.', '') ?>">

            <p><strong>Total de Productos:</strong> $<?= number_format($totalProductos,2) ?></p>
            <p><strong>Costo de Envío:</strong> $<?= number_format($costoEnvio,2) ?></p>
            <p><strong>Total Final:</strong> $<?= number_format($totalFinal,2) ?></p>

            <button type="button" class="btn-confirmar" id="btnPagarTarjeta">Pagar con Tarjeta</button>
            <button type="submit" name="metodo_pago" value="paypal" class="btn-confirmar">Pagar con PayPal</button>
        </form>

        <!-- ================= MODAL TARJETAS ================= -->
        <div id="modalTarjetas" class="modal" style="display:none;">
            <div class="modal-content">

                <?php if (!empty($tarjetasUsuario)): ?>
                <!-- FORMULARIO SELECCIONAR TARJETA -->
                <div id="formSeleccionTarjeta">
                    <h4>Elegir tarjeta guardada</h4>
                    <form action="php/procesar_pago.php" method="POST">
                        <!-- Si hay id_carrito lo enviamos también -->
                        <?php if ($id_carrito): ?>
                            <input type="hidden" name="id_carrito" value="<?= (int)$id_carrito ?>">
                        <?php else: ?>
                            <?php foreach($detalleCarrito as $item): ?>
                                <input type="hidden" name="id_variante[]" value="<?= (int)$item['id_variante'] ?>">
                                <input type="hidden" name="cantidad[]" value="<?= (int)$item['cantidad'] ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <select name="id_tarjeta" required>
                            <?php foreach ($tarjetasUsuario as $tar): ?>
                                <option value="<?= $tar['id_tarjeta'] ?>">
                                    <?= htmlspecialchars($tar['marca']) ?> - <?= $tar['numero_tarjeta'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>CVV:</label>
                        <input type="text" name="cvv" maxlength="4" required>
                        <button type="submit" name="metodo_pago" value="tarjeta" class="btn-confirmar">Pagar</button>
                    </form>
                </div>

                <!-- FORMULARIO AGREGAR TARJETA TEMPORAL -->
                <div id="formAgregarTarjetaModal" style="display:none;">
                    <h4>Pagar con nueva tarjeta</h4>
                    <form action="php/procesar_pago.php" method="POST">
                        <?php if ($id_carrito): ?>
                            <input type="hidden" name="id_carrito" value="<?= (int)$id_carrito ?>">
                        <?php else: ?>
                            <?php foreach($detalleCarrito as $item): ?>
                                <input type="hidden" name="id_variante[]" value="<?= (int)$item['id_variante'] ?>">
                                <input type="hidden" name="cantidad[]" value="<?= (int)$item['cantidad'] ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <input type="text" name="titular" placeholder="Titular de la tarjeta" required>
                        <input type="text" name="numero_tarjeta" placeholder="Número de tarjeta" maxlength="19" required>
                        <input type="text" name="expiracion" placeholder="MM/AA" maxlength="5" required>
                        <input type="text" name="cvv" placeholder="CVV" maxlength="4" minlength="3" required>
                        <button type="submit" name="metodo_pago" value="tarjeta" class="btn-confirmar">Pagar</button>
                    </form>
                    <button class="btn-confirmar" id="btnVolverSeleccionTarjeta">Elegir tarjeta guardada</button>
                </div>

                <?php else: ?>
                <!-- SI NO HAY TARJETAS GUARDADAS -->
                <div id="formAgregarTarjetaModal">
                    <h4>Pagar con nueva tarjeta</h4>
                    <form action="php/procesar_pago.php" method="POST">
                        <?php if ($id_carrito): ?>
                            <input type="hidden" name="id_carrito" value="<?= (int)$id_carrito ?>">
                        <?php else: ?>
                            <?php foreach($detalleCarrito as $item): ?>
                                <input type="hidden" name="id_variante[]" value="<?= (int)$item['id_variante'] ?>">
                                <input type="hidden" name="cantidad[]" value="<?= (int)$item['cantidad'] ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <input type="text" name="titular" placeholder="Titular de la tarjeta" required>
                        <input type="text" name="numero_tarjeta" placeholder="Número de tarjeta" maxlength="19" required>
                        <input type="text" name="expiracion" placeholder="MM/AA" maxlength="5" required>
                        <input type="text" name="cvv" placeholder="CVV" maxlength="4" required>
                        <button type="submit" name="metodo_pago" value="tarjeta" class="btn-confirmar">Pagar</button>
                    </form>
                </div>
                <?php endif; ?>

                <button class="btn-confirmar" id="cerrarModalTarjetas">Cerrar</button>
            </div>
        </div>

    </div>
    <?php endif; // detalleCarrito empty check ?>
</section>

<footer>
<p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

<script src="js/modal_tarjeta_direccion.js"></script>
<script src="js/modal_tarjetas.js"></script>

</body>
</html>
