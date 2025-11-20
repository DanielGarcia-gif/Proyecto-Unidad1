<?php
session_start();
require 'conexion.php';

// Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "Tu carrito está vacío.";
    exit;
}

$carrito = $_SESSION['carrito'];
$metodo_pago = $_POST['metodo_pago'] ?? '';

$direccion = "";
$ciudad = "";
$codigo_postal = "";

// Caso A: usuario eligió una dirección guardada
if (!empty($_POST['id_direccion'])) {
    $id_direccion = (int)$_POST['id_direccion'];

    $sql = "SELECT direccion, ciudad, codigo_postal
            FROM direccionesUsuario
            WHERE id_direccion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_direccion);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $data = $resultado->fetch_assoc();
        $direccion = $data['direccion'];
        $ciudad = $data['ciudad'];
        $codigo_postal = $data['codigo_postal'];
    }
}
// Caso B: usuario NO tiene direcciones guardadas => usa campos del formulario
else {
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
}

// Validar que sí tenemos dirección
if (empty($direccion) || empty($ciudad) || empty($codigo_postal)) {
    echo '
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background: #f8f9fa;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #ddd;
                padding-bottom: 20px;
            }
            .logo {
                max-width: 200px;
                margin-bottom: 15px;
            }
            .titulo {
                font-size: 26px;
                color: #c0392b;
                margin: 10px 0;
            }
            .info-seccion {
                max-width: 500px;
                margin: 30px auto;
                padding: 20px;
                background: #fff3f3;
                border-left: 5px solid #e74c3c;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .mensaje {
                font-size: 18px;
                color: #c0392b;
                margin-bottom: 15px;
            }
            .btn-volver {
                display: inline-block;
                padding: 10px 20px;
                font-size: 16px;
                background: #3498db;
                color: white;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                cursor: pointer;
                margin-top: 15px;
            }
            .btn-volver:hover {
                background: #2980b9;
            }
        </style>
    </head>

    <body>
        <div class="header">

            <h1 class="titulo">Dirección Incompleta</h1>
        </div>

        <div class="info-seccion" style="text-align: center;">
            <img src="../img/zona-prohibida.png" class="logo" alt="Logo">
            <p class="mensaje"><strong>Faltan datos necesarios para procesar tu envío.</strong></p>
            <p>Por favor revisa y completa todos los campos de tu dirección antes de continuar con tu compra.</p>

            <a href="../carrito/carrito.php" class="btn-volver">Volver</a>

        </div>
    </body>
    </html>
    ';
    exit;
}




$totalProductos = 0;
foreach ($carrito as $item) {
    $totalProductos += $item['precio'] * $item['cantidad'];
}

$costoEnvio = 80;
$totalFinal = $totalProductos + $costoEnvio;

$sqlEnvio = "INSERT INTO envios (direccion_envio, ciudad, codigo_postal, costo_envio)
             VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sqlEnvio);
$stmt->bind_param("sssd", $direccion, $ciudad, $codigo_postal, $costoEnvio);
$stmt->execute();
$id_envio = $stmt->insert_id;

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_temporal = null;

if (!$id_usuario) {
    // Crear comprador temporal
    $sqlTemp = "INSERT INTO compradores_temporales (nombre, email, telefono, direccion, ciudad, codigo_postal)
                VALUES ('Invitado', 'invitado@fada.com', '', ?, ?, ?)";
    $stmtTemp = $conn->prepare($sqlTemp);
    $stmtTemp->bind_param("sss", $direccion, $ciudad, $codigo_postal);
    $stmtTemp->execute();
    $id_temporal = $stmtTemp->insert_id;
}

if ($id_usuario) {
    $sqlCompra = "INSERT INTO compras (id_usuario, id_temporal, id_envio, total_compra, metodo_pago, estado)
                  VALUES (?, NULL, ?, ?, ?, 'Pendiente')";
    $stmtCompra = $conn->prepare($sqlCompra);
    $stmtCompra->bind_param("iids", $id_usuario, $id_envio, $totalFinal, $metodo_pago);
} else {
    $sqlCompra = "INSERT INTO compras (id_usuario, id_temporal, id_envio, total_compra, metodo_pago, estado)
                  VALUES (NULL, ?, ?, ?, ?, 'Pendiente')";
    $stmtCompra = $conn->prepare($sqlCompra);
    $stmtCompra->bind_param("iids", $id_temporal, $id_envio, $totalFinal, $metodo_pago);
}

$stmtCompra->execute();
$id_compra = $stmtCompra->insert_id;

$sqlDetalle = "INSERT INTO detalleCompra (id_compra, id_variante, cantidad, precio_unitario, subtotal)
               VALUES (?, ?, ?, ?, ?)";
$stmtDetalle = $conn->prepare($sqlDetalle);

foreach ($carrito as $item) {
    $id_variante = $item['id_variante'];
    $cantidad = $item['cantidad'];
    $precio = $item['precio'];
    $subtotal = $precio * $cantidad;

    $stmtDetalle->bind_param("iiidd", $id_compra, $id_variante, $cantidad, $precio, $subtotal);
    $stmtDetalle->execute();
}

$conn->close();

if ($metodo_pago === 'paypal') {
    header("Location: pago_paypal.php?id_compra=$id_compra");
    exit;
}

if ($metodo_pago === 'tarjeta') {
    header("Location: pago_tarjeta.php?id_compra=$id_compra");
    exit;
}
?>
