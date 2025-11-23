<?php
session_start();
include 'conexion.php';

// Solo usuarios logueados pueden usar carrito persistente
if (empty($_SESSION['id_usuario'])) {
    header("Location: ../registro.php?msg=Debe iniciar sesión para agregar al carrito");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'];
$id_talla = $_POST['talla'];
$id_color = $_POST['color'];

// 1. Buscar variante exacta
$sql = "SELECT 
            v.id_variante, 
            v.precio, 
            v.stock, 
            p.nombre, 
            p.imagen,
            t.nombre_talla,
            c.nombre_color
        FROM variantesProducto v
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

// Sin stock
if ($variante['stock'] <= 0) {
    echo "<script>alert('Lo sentimos, este producto está agotado.'); window.history.back();</script>";
    exit;
}

// 2. Verificar si el usuario ya tiene un carrito
$sqlCarrito = "SELECT id_carrito FROM carrito WHERE id_usuario = ?";
$stmtCarrito = $conn->prepare($sqlCarrito);
$stmtCarrito->bind_param("i", $id_usuario);
$stmtCarrito->execute();
$resCarrito = $stmtCarrito->get_result();

if ($resCarrito->num_rows == 0) {
    // Crear carrito nuevo
    $sqlNuevo = "INSERT INTO carrito (id_usuario) VALUES (?)";
    $stmtNuevo = $conn->prepare($sqlNuevo);
    $stmtNuevo->bind_param("i", $id_usuario);
    $stmtNuevo->execute();
    $id_carrito = $stmtNuevo->insert_id;
} else {
    $id_carrito = $resCarrito->fetch_assoc()['id_carrito'];
}

// 3. Verificar si ya existe la variante en el carrito
$sqlCheck = "SELECT cantidad FROM carrito_detalle WHERE id_carrito = ? AND id_variante = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $id_carrito, $id_variante);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();

// Si existe → sumar cantidad
if ($resCheck->num_rows > 0) {
    $fila = $resCheck->fetch_assoc();
    $cantidad_actual = $fila['cantidad'];

    if ($cantidad_actual + 1 > $variante['stock']) {
        echo "<script>alert('La cantidad supera el stock disponible.'); window.history.back();</script>";
        exit;
    }

    $sqlUpdate = "UPDATE carrito_detalle SET cantidad = cantidad + 1 WHERE id_carrito = ? AND id_variante = ?";
    $stmtUp = $conn->prepare($sqlUpdate);
    $stmtUp->bind_param("ii", $id_carrito, $id_variante);
    $stmtUp->execute();
} 
else {
    // Insertar nuevo
    $sqlInsert = "INSERT INTO carrito_detalle (id_carrito, id_variante, cantidad) VALUES (?, ?, 1)";
    $stmtIns = $conn->prepare($sqlInsert);
    $stmtIns->bind_param("ii", $id_carrito, $id_variante);
    $stmtIns->execute();
}

header("Location: ../carrito/carrito.php");
exit;
?>
