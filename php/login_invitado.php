<?php
require 'conexion.php';
session_start();

// 1) Buscar el número más alto de invitado ya registrado
$sqlMax = "SELECT email FROM usuarios WHERE email LIKE 'usuario_%@fada.com' ORDER BY id_usuario DESC LIMIT 1";
$resMax = $conn->query($sqlMax);

if ($resMax && $resMax->num_rows > 0) {
    $row = $resMax->fetch_assoc();

    // Extraer número de usuario_X
    preg_match('/usuario_(\d+)@fada\.com/', $row['email'], $match);

    $num = isset($match[1]) ? intval($match[1]) + 1 : 1;
} else {
    // Si no hay invitados aún  iniciar desde 1
    $num = 1;
}

// Datos del nuevo usuario invitado
$nombre = "Usuario {$num}";
$email = "usuario_{$num}@fada.com";
$password = password_hash("Invitado{$num}", PASSWORD_DEFAULT);

// Rol normal (cliente).
$id_rol = 2; 

// 2) Insertar usuario invitado en la BD
$sqlInsert = "INSERT INTO usuarios (nombre, email, contrasena, id_rol) 
              VALUES (?, ?, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("sssi", $nombre, $email, $password, $id_rol);
$stmtInsert->execute();

$id_usuario = $conn->insert_id;

// 3) Crear carrito para este usuario invitado
$sqlCarrito = "INSERT INTO carrito (id_usuario) VALUES (?)";
$stmtCar = $conn->prepare($sqlCarrito);
$stmtCar->bind_param("i", $id_usuario);
$stmtCar->execute();

$id_carrito = $conn->insert_id;

// 4) Guardar sesión
$_SESSION['id_usuario'] = $id_usuario;
$_SESSION['id_rol'] = $id_rol;
$_SESSION['nombre'] = $nombre;
$_SESSION['rol_nombre'] = 'Invitado';
$_SESSION['id_carrito'] = $id_carrito;

// 5) Redirigir a la página principal
header("Location: ../index.php");
exit;
?>
