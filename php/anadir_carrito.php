<?php
session_start();
include 'conexion.php';

$id_producto = $_POST['id_producto'];
$id_talla = $_POST['talla'];
$id_color = $_POST['color'];

// Buscamos la variante exacta
$sql = "SELECT 
            v.id_variante, 
            v.precio, 
            v.stock, 
            p.nombre, 
            p.imagen,
            t.nombre_talla,
            c.nombre_color
        FROM variantesproducto v
        INNER JOIN productos p ON v.id_producto = p.id_producto
        INNER JOIN tallas t ON v.id_talla = t.id_talla
        INNER JOIN colores c ON v.id_color = c.id_color
        WHERE v.id_producto = ? AND v.id_talla = ? AND v.id_color = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id_producto, $id_talla, $id_color);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('No existe esa combinación de talla y color.'); window.history.back();</script>";
    exit;
}

$variante = $result->fetch_assoc();
$id_variante = $variante['id_variante'];

// Verificamos stock disponible
if ($variante['stock'] <= 0) {
    echo "<script>alert('Lo sentimos, este producto está agotado.'); window.history.back();</script>";
    exit;
}
// Si no existe el carrito en sesión, lo creamos
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Si ya existe el producto en el carrito, aumentamos la cantidad
if (isset($_SESSION['carrito'][$id_variante])) {
    $_SESSION['carrito'][$id_variante]['cantidad']++;
} else {
    // Si no existe, lo agregamos
    $_SESSION['carrito'][$id_variante] = [
        'id_variante' => $id_variante,
        'id_producto' => $id_producto,
        'nombre' => $variante['nombre'],
        'precio' => $variante['precio'],
        'color' => $variante['nombre_color'],
        'talla' => $variante['nombre_talla'],
        'cantidad' => 1,
        'stock' => $variante['stock'],
        'imagen' => $variante['imagen']
    ];
}

header("Location: ../carrito/carrito.php");
exit;
?>
