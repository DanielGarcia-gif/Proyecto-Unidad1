<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once "php/conexion.php";

$id_usuario = $_SESSION['id_usuario'];
$id_direccion = intval($_GET['id']);

// VALIDAR QUE LA DIRECCIÓN PERTENECE AL USUARIO
$sql = "SELECT id_direccion FROM direccionesUsuario WHERE id_direccion = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_direccion, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Intento de borrar dirección que no es suya
    header("Location: ../perfil.php?error=permiso");
    exit();
}

// SI LA DIRECCIÓN ES DEL USUARIO → ELIMINAR
$sql_delete = "DELETE FROM direccionesUsuario WHERE id_direccion = ?";
$stmt2 = $conn->prepare($sql_delete);
$stmt2->bind_param("i", $id_direccion);
$stmt2->execute();

header("Location: perfil.php?mensaje=eliminada");
exit();
?>
