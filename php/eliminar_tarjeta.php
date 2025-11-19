<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    exit("No autorizado.");
}

$idTarjeta = $_GET['id'];
$idUsuario = $_SESSION['id_usuario'];

$sql = "DELETE FROM tarjetasUsuario WHERE id_tarjeta = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idTarjeta, $idUsuario);

$stmt->execute();

header("Location: ../perfil.php");
exit();
