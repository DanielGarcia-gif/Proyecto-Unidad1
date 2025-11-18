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
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <link rel="stylesheet" href="styles.css">
            <title>Acceso Denegado</title>
            <style>
                body {
                    height: 100vh;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f0f2f5;
                    font-family: Arial, sans-serif;
                }
                .denied-box {
                    text-align: center;
                    padding: 40px;
                    background: #ffffff;
                    border-radius: 15px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    max-width: 500px;
                }
                .denied-box img {
                    width: 180px;
                    margin-bottom: 20px;
                }
                .denied-box h1 {
                    font-size: 32px;
                    color: #d9534f;
                    margin-bottom: 15px;
                }
                .denied-box p {
                    font-size: 18px;
                    color: #555;
                }
            </style>
        </head>
        <body>

        <div class="denied-box">
            <img src="../img/zona-prohibida.png" alt="Acceso denegado">
            <h1>Acceso Denegado</h1>
            <p>No tienes permisos para ver esta página.</p>
        </div>

        </body>
        </html>
        <?php
        exit;
    }
}


?>
