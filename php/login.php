<?php
require 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $_SESSION['login_error'] = 'Credenciales inválidas.';
    header('Location: ../registro.php');
    exit;
}

$sql = "SELECT u.id_usuario, u.id_rol, u.nombre, u.contrasena, r.nombre_rol AS rol_nombre
    FROM usuarios u
    LEFT JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    $_SESSION['login_error'] = 'Usuario no encontrado.';
    header('Location: ../registro.php');
    exit;
}

$user = $res->fetch_assoc();

if (!password_verify($password, $user['contrasena'])) {
    $_SESSION['login_error'] = 'Contraseña incorrecta.';
    header('Location: ../registro.php');
    exit;
}

// Login exitoso
$_SESSION['id_usuario'] = $user['id_usuario'];
$_SESSION['id_rol'] = $user['id_rol'];
$_SESSION['nombre'] = $user['nombre'];
// Guardar nombre del rol para comprobaciones rápidas
$_SESSION['rol_nombre'] = $user['rol_nombre'] ?? null;

header('Location: ../index.php');
exit;

?>
