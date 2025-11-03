<?php
require 'conexion.php';

session_start();

$id_compra = $_GET['id_compra'] ?? null;
$token = $_GET['token'] ?? null;

if (!$id_compra || !$token) {
    die("Parámetros inválidos");
}

//Capturar el pago en PayPal
$clientId = 'AQ5-NMQ3umSEBt0KFO18fJOZSIo4_ACpzl_jeU3R1ikWIPJPXNO3fjCbZrVmiFEeaBp4JgU_gbBpfb1y';
$clientSecret = 'EGg7xXDre-WF5qT9lq0qF-XfhVxQMS2KijaVlles7hLOXL__Cwl5fkLrIZO2AkwfKe49_jaMFzZ-fTzm';

$ch = curl_init("https://api-m.sandbox.paypal.com/v2/checkout/orders/$token/capture");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$clientSecret");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, 1);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

// verificar si el pago fue exitoso
if (isset($response['status']) && $response['status'] === 'COMPLETED') {
    // Marcar compra como pagada
    $sql = "UPDATE compras SET estado = 'Pagada' WHERE id_compra = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_compra);
    $stmt->execute();

    // Descontar stock
    $sqlDetalle = "SELECT id_variante, cantidad FROM detalleCompra WHERE id_compra = ?";
    $stmtDetalle = $conn->prepare($sqlDetalle);
    $stmtDetalle->bind_param("i", $id_compra);
    $stmtDetalle->execute();
    $result = $stmtDetalle->get_result();

    while ($row = $result->fetch_assoc()) {
        $sqlUpdate = "UPDATE variantesProducto SET stock = stock - ? WHERE id_variante = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ii", $row['cantidad'], $row['id_variante']);
        $stmtUpdate->execute();
    }

    echo "<h2>Pago completado correctamente</h2>";
    echo "<p>Tu número de pedido es: <strong>$id_compra</strong></p>";

    // Mostrar enlace para ver el ticket (se generará en memoria al abrir el enlace)
    $verUrl = 'ver_ticket.php?id_compra=' . urlencode($id_compra);
    echo "<p>Puedes ver tu ticket aquí: <a href='" . htmlspecialchars($verUrl) . "' target='_blank'>Ver Ticket</a></p>";
    echo "<p><a href='../index.php'>Volver al inicio</a></p>";
    /* Limpiar el carrito después de registrar */
    unset($_SESSION['carrito']);
} else {
    echo "<h2> Error al procesar el pago</h2>";
    var_dump($response);
}