<?php
session_start();
include 'conexion.php';

if (!isset($_POST['id_variante']) || !isset($_POST['cantidad'])) {
    header("Location: ../carrito/carrito.php");
    exit;
}

$id_variante = (int)$_POST['id_variante'];
$nueva_cantidad = (int)$_POST['cantidad'];

// Validar stock
$sql = "SELECT stock FROM variantesProducto WHERE id_variante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_variante);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Variante no encontrada.'); window.history.back();</script>";
    exit;
}

$var = $result->fetch_assoc();

if ($nueva_cantidad > $var['stock']) {
    echo "<script>alert('Cantidad supera el stock disponible.'); window.history.back();</script>";
    exit;
}

if ($nueva_cantidad < 1) {
    echo "<script>alert('Cantidad no válida.'); window.history.back();</script>";
    exit;
}

// Validar usuario logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Debes iniciar sesión.'); window.location='../login.php';</script>";
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener id_carrito del usuario
$sql = "SELECT id_carrito FROM carrito WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$carrito = $stmt->get_result()->fetch_assoc();

if (!$carrito) {
    echo "<script>alert('Carrito no encontrado.'); window.history.back();</script>";
    exit;
}

$id_carrito = $carrito['id_carrito'];

// Actualizar cantidad
$sql = "UPDATE carrito_detalle 
        SET cantidad = ?
        WHERE id_carrito = ? AND id_variante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $nueva_cantidad, $id_carrito, $id_variante);
$stmt->execute();

header("Location: ../carrito/carrito.php");
exit;
?>
