<?php
require 'conexion.php';
session_start();

// 1) Buscar el correo con el número más alto
$sqlMax = "SELECT email 
           FROM usuarios 
           WHERE email REGEXP '^usuario_[0-9]+@fada\\.com$'
           ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING(email, 9), '@', 1) AS UNSIGNED) DESC
           LIMIT 1";

$resMax = $conn->query($sqlMax);

if ($resMax && $resMax->num_rows > 0) {
    $row = $resMax->fetch_assoc();

    // Extraer número exacto
    preg_match('/usuario_(\d+)@fada\.com/', $row['email'], $match);
    $num = isset($match[1]) ? intval($match[1]) + 1 : 1;

} else {
    // No hay usuarios invitados aún
    $num = 1;
}

// Datos del nuevo usuario invitado
$nombre = "Usuario {$num}";
$email = "usuario_{$num}@fada.com";
$password = password_hash("Invitado{$num}", PASSWORD_DEFAULT);

// Rol normal (cliente).
$id_rol = 2; 

// 2) Insertar usuario invitado
$sqlInsert = "INSERT INTO usuarios (nombre, email, contrasena, id_rol) 
              VALUES (?, ?, ?, ?)";

$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("sssi", $nombre, $email, $password, $id_rol);
$stmtInsert->execute();

$id_usuario = $conn->insert_id;

// 3) Crear carrito
$sqlCarrito = "INSERT INTO carrito (id_usuario) VALUES (?)";
$stmtCar = $conn->prepare($sqlCarrito);
$stmtCar->bind_param("i", $id_usuario);
$stmtCar->execute();

$id_carrito = $conn->insert_id;

// 4) Sesión
$_SESSION['id_usuario'] = $id_usuario;
$_SESSION['id_rol'] = $id_rol;
$_SESSION['nombre'] = $nombre;
$_SESSION['rol_nombre'] = 'Invitado';
$_SESSION['id_carrito'] = $id_carrito;

// 5) Redirigir
header("Location: ../index.php");
exit;
