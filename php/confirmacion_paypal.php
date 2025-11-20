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

// ================================================================
//     DISEÑO – SOLO SE APLICA SI EL PAGO FUE COMPLETADO
// ================================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pago PayPal - FaDa Sports</title>
<link rel="icon" type="image/jpg" href="img/logo.jpg">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f2f8ff;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }
    .ticket-container {
        background-color: #fff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        max-width: 450px;
        width: 100%;
        text-align: center;
    }
    .ticket-container img {
        max-width: 120px;
        margin-bottom: 20px;
    }
    .ticket-container h2 {
        color: #2a5d9f;
        margin-bottom: 10px;
    }
    .ticket-container p {
        font-size: 16px;
        margin: 8px 0;
    }
    .ticket-container a {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background-color: #2a5d9f;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        transition: 0.3s;
    }
    .ticket-container a:hover {
        background-color: #1e4470;
    }
    .error-box {
        background: #ffe5e5;
        padding: 20px;
        border-radius: 15px;
        border: 1px solid #ff9e9e;
        max-width: 450px;
        text-align: center;
    }
</style>
</head>

<body>

<?php
// ================================================================
//               SI EL PAGO FUE EXITOSO → MOSTRAR DISEÑO
// ================================================================
if (isset($response['status']) && $response['status'] === 'COMPLETED') {

    // MARCAR PAGO COMO COMPLETADO
    $sql = "UPDATE compras SET estado = 'Pagada' WHERE id_compra = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_compra);
    $stmt->execute();

    // DESCONTAR STOCK
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

    unset($_SESSION['carrito']);

    // RUTA DEL TICKET
    $verUrl = 'ver_ticket.php?id_compra=' . urlencode($id_compra);
    ?>

        <div class="ticket-container">
            <img src="../img/pago_confirmado.png" alt="Pago Confirmado">

            <h2>Pago PayPal Completado</h2>
            <p>¡Tu pago ha sido procesado exitosamente!</p>

            <p>Número de pedido: <strong><?= htmlspecialchars($id_compra) ?></strong></p>

            <p>Puedes ver tu ticket completo en el siguiente enlace:</p>

            <a href="<?= htmlspecialchars($verUrl) ?>" target="_blank">Ver Ticket</a>

            <p><a href="../index.php">Volver al inicio</a></p>
        </div>

<?php
} else {
    // ============================================================
    //                  ERROR EN EL PAGO
    // ============================================================
    echo "<div class='error-box'>
            <h2>Error al procesar el pago</h2>
            <p>Ocurrió un problema al completar el pago.</p>
        </div>";
}
?>

</body>
</html>
