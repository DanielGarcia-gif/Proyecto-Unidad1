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
$_SESSION['rol_nombre'] = $user['rol_nombre'] ?? null;



$id_usuario = $user['id_usuario'];

// 1Buscar carrito existente
$sqlCarrito = "SELECT id_carrito FROM carrito WHERE id_usuario = ? LIMIT 1";
$stmtCar = $conn->prepare($sqlCarrito);
$stmtCar->bind_param("i", $id_usuario);
$stmtCar->execute();
$resCar = $stmtCar->get_result();

if ($resCar && $resCar->num_rows > 0) {
    // Ya tenía un carrito
    $car = $resCar->fetch_assoc();
    $_SESSION['id_carrito'] = $car['id_carrito'];
} else {
    // No tenía → crear uno nuevo
    $sqlCrear = "INSERT INTO carrito (id_usuario) VALUES (?)";
    $stmtCrear = $conn->prepare($sqlCrear);
    $stmtCrear->bind_param("i", $id_usuario);
    $stmtCrear->execute();

    $_SESSION['id_carrito'] = $conn->insert_id;
}
// ---------------------------------------------


header('Location: ../index.php');
exit;

?>
