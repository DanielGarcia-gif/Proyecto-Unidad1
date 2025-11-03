<?php
session_start();
require_once 'conexion.php';

function isLoggedIn() {
    return !empty($_SESSION['id_usuario']);
}

function isAdmin() {
    global $conn;
    // Si ya tenemos el nombre del rol en sesión, usarlo
    if (!empty($_SESSION['rol_nombre'])) {
        return $_SESSION['rol_nombre'] === 'Admin';
    }

    if (empty($_SESSION['id_rol'])) {
        return false;
    }

    // Obtener nombre del rol desde la BD y cachearlo en sesión
    $stmt = $conn->prepare('SELECT nombre_rol FROM roles WHERE id_rol = ? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['id_rol']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $_SESSION['rol_nombre'] = $row['nombre_rol'];
        return $row['nombre_rol'] === 'Admin';
    }

    return false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /Proyecto-Unidad1/registro.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Acceso denegado';
        exit;
    }
}

?>
