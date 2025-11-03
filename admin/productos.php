<?php
require_once __DIR__ . '/../php/auth.php';
requireAdmin();
require_once __DIR__ . '/../php/conexion.php';

// Obtener lista de variantes con info del producto, color y talla
$sql = "SELECT vp.id_variante, vp.id_producto, p.nombre AS producto_nombre, p.categoria, p.material, vp.precio, vp.stock, c.nombre_color, t.nombre_talla
        FROM variantesProducto vp
        JOIN productos p ON vp.id_producto = p.id_producto
        LEFT JOIN colores c ON vp.id_color = c.id_color
        LEFT JOIN tallas t ON vp.id_talla = t.id_talla
        ORDER BY p.nombre";
$res = $conn->query($sql);

// Obtener colores y tallas para el formulario
$colores = $conn->query("SELECT id_color, nombre_color FROM colores ORDER BY nombre_color");
$tallas = $conn->query("SELECT id_talla, nombre_talla FROM tallas ORDER BY nombre_talla");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin - Productos</title>
    <link rel="stylesheet" href="stylesA.css">
</head>
<body>
    <header>
        <div class="logo">FaDa Sports - Admin</div>
        <nav class="menu">
            <a href="dashboard.php">Dashboard</a>
            <a href="productos.php" class="activo">Productos</a>
            <a href="#">Usuarios</a>
            <a href="#">Pedidos</a>
            <a href="../index.php">Salir</a>
        </nav>
    </header>

    <section class="admin-panel">
        <h2>Gestionar Productos</h2>

        <h3>Agregar producto / variante</h3>
        <form action="../php/admin_productos_action.php" method="post">
            <input type="hidden" name="action" value="add">
            <label>Nombre del producto</label>
            <input type="text" name="nombre" required>

            <label>Descripción</label>
            <input type="text" name="descripcion">

            <label>Categoría</label>
            <input type="text" name="categoria">

            <label>Material</label>
            <input type="text" name="material">

            <label>Color</label>
            <select name="id_color">
                <option value="">--</option>
                <?php while($row = $colores->fetch_assoc()): ?>
                    <option value="<?php echo $row['id_color']; ?>"><?php echo htmlspecialchars($row['nombre_color']); ?></option>
                <?php endwhile; ?>
            </select>

            <label>Talla</label>
            <select name="id_talla">
                <option value="">--</option>
                <?php while($row = $tallas->fetch_assoc()): ?>
                    <option value="<?php echo $row['id_talla']; ?>"><?php echo htmlspecialchars($row['nombre_talla']); ?></option>
                <?php endwhile; ?>
            </select>

            <label>Precio</label>
            <input type="number" step="0.01" name="precio" required>

            <label>Stock</label>
            <input type="number" name="stock" value="0" required>

            <button type="submit">Agregar</button>
        </form>

        <h3>Variantes existentes</h3>
        <table>
            <thead>
                <tr>
                    <th>ID Var.</th>
                    <th>Producto</th>
                    <th>Categoria</th>
                    <th>Material</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($v = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $v['id_variante']; ?></td>
                    <td><?php echo htmlspecialchars($v['producto_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($v['categoria']); ?></td>
                    <td><?php echo htmlspecialchars($v['material']); ?></td>
                    <td><?php echo htmlspecialchars($v['nombre_color']); ?></td>
                    <td><?php echo htmlspecialchars($v['nombre_talla']); ?></td>
                    <td><?php echo number_format($v['precio'],2); ?></td>
                    <td><?php echo $v['stock']; ?></td>
                    <td>
                        <form action="../php/admin_productos_action.php" method="post" style="display:inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_variante" value="<?php echo $v['id_variante']; ?>">
                            <button type="submit" onclick="return confirm('Eliminar variante?')">Eliminar</button>
                        </form>
                        
                        <form action="../php/admin_productos_action.php" method="post" style="display:inline;margin-left:8px">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id_variante" value="<?php echo $v['id_variante']; ?>">
                            <input type="number" step="0.01" name="precio" value="<?php echo number_format($v['precio'],2,'.',''); ?>" style="width:80px"> 
                            <input type="number" name="stock" value="<?php echo $v['stock']; ?>" style="width:60px"> 
                            <button type="submit">Actualizar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </section>

    <footer>
        <p>&copy; 2025 FaDa Sports. Panel de administración.</p>
    </footer>
</body>
</html>
