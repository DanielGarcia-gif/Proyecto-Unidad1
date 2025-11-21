<?php
session_start();
include 'conexion.php';

if (!isset($_POST['id_variante']) || !isset($_POST['cantidad'])) {
    header("Location: ../carrito/carrito.php");
    exit;
}

$id_variante = $_POST['id_variante'];
$nueva_cantidad = (int)$_POST['cantidad'];

// Verificar stock actual en la base
$sql = "SELECT stock FROM variantesProducto WHERE id_variante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_variante);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Variante no encontrada.'); window.history.back();</script>";
    exit;
}

$variante = $result->fetch_assoc();

if ($nueva_cantidad > $variante['stock']) {
    echo "<script>alert('Cantidad supera el stock disponible.'); window.history.back();</script>";
    exit;
}

if ($nueva_cantidad < 1) {
    echo "<script>alert('Cantidad no válida.'); window.history.back();</script>";
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$sql = "UPDATE carrito_detalle 
        SET cantidad = ? 
        WHERE id_usuario = ? AND id_variante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $nueva_cantidad, $id_usuario, $id_variante);
$stmt->execute();


header("Location: ../carrito/carrito.php");
exit;
?>
