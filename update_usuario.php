<?php
session_start();
require_once "php/conexion.php";

$id = $_SESSION['id_usuario'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];

$sql = "UPDATE usuarios SET email = ?, telefono = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $email, $telefono, $id);

if ($stmt->execute()) {
    header("Location: perfil.php?msg=ok");
} else {
    echo "Error al actualizar.";
}
?>
