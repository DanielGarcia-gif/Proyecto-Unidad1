<?php
session_start();
require 'php/conexion.php';

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

// Como el usuario no está registrado:
$id_usuario = NULL;
$id_temporal = NULL;

// Crear comprador temporal
if (!isset($_SESSION['usuario'])) {
    $sqlTemp = "INSERT INTO compradores_temporales (nombre, email, telefono, direccion, ciudad, codigo_postal)
                VALUES ('Invitado', 'invitado@fada.com', '', ?, ?, ?)";
    $stmtTemp = $conn->prepare($sqlTemp);
    $stmtTemp->bind_param("sss", $direccion, $ciudad, $codigo_postal);
    $stmtTemp->execute();
    $id_temporal = $stmtTemp->insert_id;
}

// Registrar compra (Pendiente)
$sqlCompra = "INSERT INTO compras (id_usuario, id_temporal, id_envio, total_compra, metodo_pago, estado)
              VALUES (?, ?, ?, ?, ?, 'Pendiente')";
$stmtCompra = $conn->prepare($sqlCompra);
$stmtCompra->bind_param("iiids", $id_usuario, $id_temporal, $id_envio, $totalFinal, $metodo_pago);
$stmtCompra->execute();
$id_compra = $stmtCompra->insert_id;


// Si se eligió PayPal, redirige al flujo PayPal
if ($metodo_pago === 'paypal') {
    header("Location: paypal/pago_paypal.php?id_compra=$id_compra");
    exit;
}

// Si es pago con tarjeta simulada
if ($metodo_pago === 'tarjeta') {
    header("Location: pago_tarjeta.php?id_compra=$id_compra");
    exit;
}
?>
