<?php
session_start();
require_once "php/conexion.php";

$id = $_SESSION['id_usuario'];

$sql = "INSERT INTO direccionesUsuario (id_usuario, direccion, ciudad, codigo_postal)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $id, $_POST['direccion'], $_POST['ciudad'], $_POST['codigo_postal']);

$stmt->execute();

header("Location: perfil.php");
?>
