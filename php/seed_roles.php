<?php
/**
 * Script para sembrar roles por defecto y crear un admin.
 * Accede por navegador y completa el formulario para crear el Admin.
 */
require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_email = trim($_POST['email'] ?? '');
    $admin_pass = $_POST['password'] ?? '';
    $admin_name = trim($_POST['nombre'] ?? 'Admin');

    if (!$admin_email || !$admin_pass) {
        $error = 'Email y contraseña son obligatorios.';
    } else {
        // Crear roles si no existen
        $roles = ['Admin', 'Cliente'];
        foreach ($roles as $r) {
            $stmt = $conn->prepare('SELECT id_rol FROM roles WHERE nombre_rol = ? LIMIT 1');
            $stmt->bind_param('s', $r);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                $ins = $conn->prepare('INSERT INTO roles (nombre_rol) VALUES (?)');
                $ins->bind_param('s', $r);
                $ins->execute();
            }
        }

        // Obtener id rol Admin
        $stmt = $conn->prepare('SELECT id_rol FROM roles WHERE nombre_rol = ? LIMIT 1');
        $rname = 'Admin';
        $stmt->bind_param('s', $rname);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $id_admin = $row['id_rol'];

        // Crear usuario admin si no existe
        $stmt = $conn->prepare('SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $admin_email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $ins = $conn->prepare('INSERT INTO usuarios (id_rol, nombre, email, contrasena) VALUES (?, ?, ?, ?)');
            $ins->bind_param('isss', $id_admin, $admin_name, $admin_email, $hash);
            $ins->execute();
            $created = true;
        } else {
            $exists = true;
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Seed roles</title></head>
<body>
<h2>Sembrar roles y crear Admin</h2>
<?php if (!empty($error)): ?><div style="color:red"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if (!empty($created)): ?><div style="color:green">Admin creado correctamente.</div><?php endif; ?>
<?php if (!empty($exists)): ?><div style="color:orange">Ya existe un usuario con ese email.</div><?php endif; ?>
<form method="post">
    <label>Nombre admin (opcional)</label><br>
    <input name="nombre" value="Admin"><br>
    <label>Email admin</label><br>
    <input name="email" type="email" required><br>
    <label>Contraseña admin</label><br>
    <input name="password" type="password" required><br>
    <button type="submit">Crear Admin</button>
</form>
<p>Después de crear el admin, borra o protege este archivo.</p>
</body>
</html>
