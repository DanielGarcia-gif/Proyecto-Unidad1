<?php
require_once __DIR__ . '/../php/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="stylesA.css">
</head>
<body>
    <header>
        <div class="logo">FaDa Sports - Admin</div>
        <nav class="menu">
            <a href="productos.php">Productos</a>
            <a href="#">Usuarios</a>
            <a href="#">Pedidos</a>
            <a href="../index.php">Salir</a>
        </nav>
    </header>

    <section class="admin-panel">
        <h2>Panel de administración</h2>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?>.</p>

        <div class="admin-cards">
            <a class="card" href="productos.php">Gestionar Productos</a>
            <a class="card" href="#">Gestionar Usuarios</a>
            <a class="card" href="#">Ver Pedidos</a>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 FaDa Sports. Panel de administración.</p>
    </footer>
</body>
</html>
