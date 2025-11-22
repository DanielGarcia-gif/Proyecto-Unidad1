<?php
session_start();
require_once "php/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['id_usuario'];
$email = trim($_POST['email']);
$telefono = trim($_POST['telefono']);


// 1. Verificar si el correo ya existe para otro usuario
$sql_check = "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("si", $email, $id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    // Correo duplicado
    header("Location: perfil.php?error=email_duplicado");
    exit;
}


// 2. Actualizar datos
$sql = "UPDATE usuarios SET email = ?, telefono = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $email, $telefono, $id);

if ($stmt->execute()) {
    header("Location: perfil.php?msg=ok");
} else {
    header("Location: perfil.php?error=update_fail");
}
?>
