<?php
session_start();
require 'conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_carrito = $_SESSION['id_carrito'] ?? null;
$id_variante = $_POST['id_variante'] ?? null;

if (!$id_usuario || !$id_variante) {
    header("Location: ../carrito/carrito.php");
    exit;
}

/* ============================================================
   1) OBTENER id_carrito SI NO ESTÁ EN SESIÓN
   ============================================================ */
if (!$id_carrito) {
    $sqlBuscar = "SELECT id_carrito FROM carrito WHERE id_usuario = ? ORDER BY id_carrito DESC LIMIT 1";
    $stmt = $conn->prepare($sqlBuscar);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $id_carrito = $row['id_carrito'];
        $_SESSION['id_carrito'] = $id_carrito;
    } else {
        // No tiene carrito en BD → nada que eliminar
        header("Location: ../carrito/carrito.php");
        exit;
    }
}

/* ============================================================
   2) ELIMINAR PRODUCTO DEL CARRITO EN BD
   ============================================================ */
$sqlDelete = "DELETE FROM carrito_detalle WHERE id_carrito = ? AND id_variante = ?";
$stmtDel = $conn->prepare($sqlDelete);
$stmtDel->bind_param("ii", $id_carrito, $id_variante);
$stmtDel->execute();

header("Location: ../carrito/carrito.php");
exit;
?>
