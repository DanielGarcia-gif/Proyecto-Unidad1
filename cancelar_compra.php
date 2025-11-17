<?php
require_once "php/conexion.php";

$id_compra = $_GET['id'];

$sql = "UPDATE compras SET estado = 'Cancelado' WHERE id_compra = ? AND estado = 'Pendiente'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_compra);
$stmt->execute();

header("Location: perfil.php");
?>
