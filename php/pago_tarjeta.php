<?php
require 'conexion.php';
session_start();

$id_compra = $_GET['id_compra'] ?? null;

if (!$id_compra) {
    die("Parámetro id_compra inválido");
}

// Aquí asumimos que el pago fue aprobado

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

unset($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pago con Tarjeta - FaDa Sports</title>
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
</style>
</head>
<body>

<div class="ticket-container">
    <!-- Espacio para imagen -->
    <img src="../img/pago_confirmado.png" alt="Pago Aprobado">

    <h2>Pago con Tarjeta Completado</h2>
    <p>¡Tu pago ha sido procesado exitosamente!</p>
    <p>Número de pedido: <strong><?= htmlspecialchars($id_compra) ?></strong></p>

    <p>Puedes ver tu ticket completo en el siguiente enlace:</p>
    <?php
        $verUrl = 'ver_ticket.php?id_compra=' . urlencode($id_compra);
    ?>
    <a href="<?= htmlspecialchars($verUrl) ?>" target="_blank">Ver Ticket</a>

    <p><a href="../index.php">Volver al inicio</a></p>
</div>

</body>
</html>
