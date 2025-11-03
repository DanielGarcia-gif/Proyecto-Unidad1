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
$direccion = $_POST['direccion'];
$ciudad = $_POST['ciudad'];
$codigo_postal = $_POST['codigo_postal'];

// Calcular total
$totalProductos = 0;
foreach ($carrito as $item) {
    $totalProductos += $item['precio'] * $item['cantidad'];
}
$costoEnvio = 80;
$totalFinal = $totalProductos + $costoEnvio;

// Crear registro de envío
$sqlEnvio = "INSERT INTO envios (direccion_envio, ciudad, codigo_postal, costo_envio)
             VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sqlEnvio);
$stmt->bind_param("sssd", $direccion, $ciudad, $codigo_postal, $costoEnvio);
$stmt->execute();
$id_envio = $stmt->insert_id;

// Asociar la compra al usuario si hay sesión iniciada, si no, crear comprador temporal
$id_usuario = null;
$id_temporal = null;
if (!empty($_SESSION['id_usuario'])) {
    // El usuario está logueado: asociar compra a su ID
    $id_usuario = (int) $_SESSION['id_usuario'];
} else {
    // Usuario invitado: crear registro temporal de comprador
    $sqlTemp = "INSERT INTO compradores_temporales (nombre, email, telefono, direccion, ciudad, codigo_postal)
                VALUES ('Invitado', 'invitado@fada.com', '', ?, ?, ?)";
    $stmtTemp = $conn->prepare($sqlTemp);
    $stmtTemp->bind_param("sss", $direccion, $ciudad, $codigo_postal);
    $stmtTemp->execute();
    $id_temporal = $stmtTemp->insert_id;
}

// Registrar compra principal
// Manejar dos casos: usuario logueado (tiene id_usuario) o invitado (id_usuario NULL)
if ($id_usuario !== null) {
    $sqlCompra = "INSERT INTO compras (id_usuario, id_temporal, id_envio, total_compra, metodo_pago, estado)
                  VALUES (?, ?, ?, ?, ?, 'Pendiente')";
    $stmtCompra = $conn->prepare($sqlCompra);
    $stmtCompra->bind_param("iiids", $id_usuario, $id_temporal, $id_envio, $totalFinal, $metodo_pago);
} else {
    // id_usuario null: insertar NULL explícito para id_usuario
    $sqlCompra = "INSERT INTO compras (id_usuario, id_temporal, id_envio, total_compra, metodo_pago, estado)
                  VALUES (NULL, ?, ?, ?, ?, 'Pendiente')";
    $stmtCompra = $conn->prepare($sqlCompra);
    $stmtCompra->bind_param("iids", $id_temporal, $id_envio, $totalFinal, $metodo_pago);
}

if (!$stmtCompra) {
    error_log('Error preparando statement compras: ' . $conn->error);
    die('Error interno');
}

if (!$stmtCompra->execute()) {
    error_log('Error ejecutando insert compras: ' . $stmtCompra->error);
    die('Error al crear la compra');
}

$id_compra = $stmtCompra->insert_id;

/* Registrar los detalles de cada producto en el carrito */
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

// Cerrar conexión
$conn->close();


// Redirigir al método de pago correspondiente
if ($metodo_pago === 'paypal') {
    header("Location: pago_paypal.php?id_compra=$id_compra");
    exit;
}

if ($metodo_pago === 'tarjeta') {
    header("Location: pago_tarjeta.php?id_compra=$id_compra");
    exit;
}
?>
