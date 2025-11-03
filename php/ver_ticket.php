<?php
// Endpoint para ver el ticket en el navegador sin guardarlo.
require 'generar_ticket.php';

$id_compra = $_GET['id_compra'] ?? null;
if (!$id_compra) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    echo 'Parámetros inválidos';
    exit;
}

require_once 'conexion.php';

// Verificar que la compra exista y esté pagada
$stmt = $conn->prepare('SELECT estado, id_usuario FROM compras WHERE id_compra = ? LIMIT 1');
$stmt->bind_param('i', $id_compra);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo 'Compra no encontrada';
    exit;
}
$compra = $res->fetch_assoc();
if ($compra['estado'] !== 'Pagada') {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    echo 'El ticket solo está disponible para compras pagadas.';
    exit;
}

// Si todo bien, generar el PDF en memoria y mostrarlo
generarTicketPDFStream((int)$id_compra);
