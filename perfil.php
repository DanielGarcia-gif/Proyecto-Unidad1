<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: php/login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

require_once "php/conexion.php";

/* ==== 1. Obtener datos del usuario ==== */
$sql_user = "SELECT nombre, email, telefono FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_user = $stmt->get_result()->fetch_assoc();

/* ==== 2. Obtener historial de compras (CORREGIDO) ==== */
$sql_compras = "
    SELECT
        c.id_compra,
        c.fecha_compra,
        c.total_compra,
        c.estado,

        GROUP_CONCAT(
            CONCAT(
                p.nombre,
                ' - ', col.nombre_color,
                ' - ', t.nombre_talla,
                ' (x', d.cantidad, ') — $', d.subtotal
            )
            SEPARATOR '<br>'
        ) AS productos

    FROM compras c
    JOIN detalleCompra d ON c.id_compra = d.id_compra
    JOIN variantesProducto vp ON d.id_variante = vp.id_variante
    JOIN productos p ON vp.id_producto = p.id_producto
    JOIN colores col ON vp.id_color = col.id_color
    JOIN tallas t ON vp.id_talla = t.id_talla

    WHERE c.id_usuario = ?

    GROUP BY c.id_compra
    ORDER BY c.fecha_compra DESC
";
$stmt2 = $conn->prepare($sql_compras);
$stmt2->bind_param("i", $id_usuario);
$stmt2->execute();
$compras = $stmt2->get_result();

/* ==== 3. Obtener direcciones ==== */
$sql_dir = "SELECT * FROM direccionesUsuario WHERE id_usuario = ?";
$stmt3 = $conn->prepare($sql_dir);
$stmt3->bind_param("i", $id_usuario);
$stmt3->execute();
$direcciones = $stmt3->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario - FaDa Sports</title>
    <link rel="icon" type="img/logo.jpg" href="img/logo.jpg">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">FaDa Sports</div>
    <nav class="menu">
        <a style="padding-top: 10px" href="index.php">Inicio</a>
        <a style="padding-top: 10px" href="catalogo.php">Catálogo</a>
        <a style="padding-top: 10px" href="perfil.php">Mi Perfil</a>
        <a style="padding-top: 10px" href="contacto.html">Contacto</a>
        <a href="php/logout.php" class="cerrar-sesion">Cerrar Sesión</a>
    </nav>
</header>

<section class="perfil section">

<h1>Mi Perfil</h1>

<!-- DATOS DEL USUARIO -->
<div class="perfil-card">
    <h2>Datos del Usuario</h2>

    <form action="update_usuario.php" method="POST" class="perfil-form">

        <p><strong>Nombre:</strong> <?= $result_user['nombre'] ?></p>

        <div class="field-row">
            <label>Email:</label>
            <input type="email" name="email" value="<?= $result_user['email'] ?>" required>
        </div>

        <div class="field-row">
            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?= $result_user['telefono'] ?>">
        </div>

        <button class="btn">Actualizar Información</button>

    </form>
</div>


<!-- HISTORIAL DE COMPRAS -->
<div class="perfil-card">
    <h2>Historial de Compras</h2>

    <div class="tabla-container">
        <table class="tabla-compras">
            <thead>
                <tr>
                    <th>Productos</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $compras->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['productos'] ?></td>
                    <td><?= $row['fecha_compra'] ?></td>
                    <td>$<?= $row['total_compra'] ?></td>
                    <td><?= $row['estado'] ?></td>

                    <td>
                        <?php if ($row['estado'] === "Pendiente") { ?>
                            <a href="cancelar_compra.php?id=<?= $row['id_compra'] ?>" class="btn btn-danger">Cancelar</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DIRECCIONES -->
<div class="perfil-card">
    <h2>Direcciones de Envío</h2>

    <ul class="lista-direcciones">
    <?php while ($d = $direcciones->fetch_assoc()) { ?>
        <li class="direccion-item">
            <span>
                <strong><?= $d['ciudad'] ?>:</strong>
                <?= $d['direccion'] ?>, CP <?= $d['codigo_postal'] ?>
            </span>

            <a href="eliminar_direccion.php?id=<?= $d['id_direccion'] ?>"
               class="btn-eliminar"
               onclick="return confirm('¿Eliminar esta dirección?');">
               Eliminar
            </a>
        </li>
    <?php } ?>
</ul>

    <button class="btn" onclick="document.getElementById('form-dir').style.display='block'">
        Agregar Nueva Dirección
    </button>

    <div id="form-dir" style="display:none; margin-top:15px;">
        <form action="agregar_direccion.php" method="POST">
            <input type="text" name="direccion" placeholder="Dirección completa" required>
            <input type="text" name="ciudad" placeholder="Ciudad" required>
            <input type="text" name="codigo_postal" placeholder="Código Postal" required>
            <button class="btn">Guardar Dirección</button>
        </form>
    </div>
</div>

</section>

<footer>
    <p>&copy; 2025 FaDa Sports. Todos los derechos reservados.</p>
</footer>

</body>
</html>
