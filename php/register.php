<?php
require 'conexion.php';
session_start();

// Recibir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$telefono = trim($_POST['telefono'] ?? null);

if (!$nombre || !$email || !$password) {
    $_SESSION['register_error'] = 'Rellena todos los campos obligatorios.';
    header('Location: ../registro.php');
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Email no válido.';
    header('Location: ../registro.php');
    exit;
}

// Verificar si email ya existe
$sql = "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $_SESSION['register_error'] = 'El correo ya está registrado.';
    header('Location: ../registro.php');
    exit;
}

// Asegurar que exista el rol 'Cliente' y obtener su id
$rol = 'Cliente';
$sql = "SELECT id_rol FROM roles WHERE nombre_rol = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $rol);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $id_rol = $row['id_rol'];
} else {
    // Insertar rol
    $ins = $conn->prepare("INSERT INTO roles (nombre_rol) VALUES (?)");
    $ins->bind_param('s', $rol);
    $ins->execute();
    $id_rol = $ins->insert_id;
}

// Hashear contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar usuario
$sql = "INSERT INTO usuarios (id_rol, nombre, email, contrasena, telefono) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issss', $id_rol, $nombre, $email, $password_hash, $telefono);
if ($stmt->execute()) {
    // Login automático
    $user_id = $stmt->insert_id;
    $_SESSION['id_usuario'] = $user_id;
    $_SESSION['id_rol'] = $id_rol;
    $_SESSION['nombre'] = $nombre;
    header('Location: ../index.php');
    exit;
} else {
    $_SESSION['register_error'] = 'Error al registrar. Intenta de nuevo.';
    header('Location: ../registro.php');
    exit;
}

?>
