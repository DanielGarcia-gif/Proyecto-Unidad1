<?php
session_start();
require 'conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_carrito = $_SESSION['id_carrito'] ?? null;

$carrito = [];
$totalProductos = 0;


if (isset($_POST['id_carrito'])) {
    $id_carrito = (int)$_POST['id_carrito'];
    $_SESSION['id_carrito'] = $id_carrito;
}


if ($id_usuario && $id_carrito) {

    $sql = "SELECT cd.id_variante, cd.cantidad, v.precio
            FROM carrito_detalle cd
            JOIN variantesProducto v ON cd.id_variante = v.id_variante
            WHERE cd.id_carrito = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_carrito);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($item = $res->fetch_assoc()) {
        $carrito[] = $item;
    }
}


if (empty($carrito) && isset($_POST['id_variante'])) {

    foreach ($_POST['id_variante'] as $i => $idv) {
        $carrito[] = [
            "id_variante" => (int)$idv,
            "cantidad" => (int)$_POST['cantidad'][$i],

            // precio actual desde BD
        ];
    }

    // Obtener precios actuales de BD
    foreach ($carrito as &$item) {
        $sql = "SELECT precio FROM variantesProducto WHERE id_variante = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item['id_variante']);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        $item['precio'] = $row['precio'];
    }
}


if (empty($carrito)) {
    echo "Tu carrito está vacío.";
    exit;
}


foreach ($carrito as $item) {
    $totalProductos += $item['precio'] * $item['cantidad'];
}

$costoEnvio = 80;
$totalFinal = $totalProductos + $costoEnvio;


$metodo_pago = $_POST['metodo_pago'] ?? '';
$direccion = "";
$ciudad = "";
$codigo_postal = "";

if (!empty($_POST['id_direccion'])) {
    $id_direccion = (int)$_POST['id_direccion'];

    $sql = "SELECT direccion, ciudad, codigo_postal
            FROM direccionesUsuario
            WHERE id_direccion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_direccion);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $data = $resultado->fetch_assoc();
        $direccion = $data['direccion'];
        $ciudad = $data['ciudad'];
        $codigo_postal = $data['codigo_postal'];
    }
} else {
    $direccion = $_POST['direccion'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
}

if (empty($direccion) || empty($ciudad) || empty($codigo_postal)) {
    include 'error_direccion_incompleta.php';
    exit;
}


$sqlEnvio = "INSERT INTO envios (direccion_envio, ciudad, codigo_postal, costo_envio)
             VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sqlEnvio);
$stmt->bind_param("sssd", $direccion, $ciudad, $codigo_postal, $costoEnvio);
$stmt->execute();
$id_envio = $stmt->insert_id;


$id_temporal = null;
if (!$id_usuario) {
    $sqlTemp = "INSERT INTO compradores_temporales (nombre, email, telefono, direccion, ciudad, codigo_postal)
                VALUES ('Invitado', 'invitado@fada.com', '', ?, ?, ?)";
    $stmtTemp = $conn->prepare($sqlTemp);
    $stmtTemp->bind_param("sss", $direccion, $ciudad, $codigo_postal);
    $stmtTemp->execute();
    $id_temporal = $stmtTemp->insert_id;
}


if ($id_usuario) {
    $sqlCompra = "INSERT INTO compras (id_usuario, id_temporal, id_envio, total_compra, metodo_pago, estado)
                  VALUES (?, NULL, ?, ?, ?, 'Pendiente')";
    $stmtCompra = $conn->prepare($sqlCompra);
    $stmtCompra->bind_param("iids", $id_usuario, $id_envio, $totalFinal, $metodo_pago);
} else {
    $sqlCompra = "INSERT INTO compras (id_usuario, id_temporal, id_envio, total_compra, metodo_pago, estado)
                  VALUES (NULL, ?, ?, ?, ?, 'Pendiente')";
    $stmtCompra = $conn->prepare($sqlCompra);
    $stmtCompra->bind_param("iids", $id_temporal, $id_envio, $totalFinal, $metodo_pago);
}

$stmtCompra->execute();
$id_compra = $stmtCompra->insert_id;


$sqlDetalle = "INSERT INTO detalleCompra (id_compra, id_variante, cantidad, precio_unitario, subtotal)
               VALUES (?, ?, ?, ?, ?)";
$stmtDetalle = $conn->prepare($sqlDetalle);

foreach ($carrito as $item) {
    $sub = $item['precio'] * $item['cantidad'];
    $stmtDetalle->bind_param("iiidd", $id_compra, $item['id_variante'], $item['cantidad'], $item['precio'], $sub);
    $stmtDetalle->execute();
}


if ($id_usuario && $id_carrito) {
    $conn->query("DELETE FROM carrito_detalle WHERE id_carrito = $id_carrito");
} else {
    unset($_SESSION['carrito']);
}

$conn->close();


if ($metodo_pago === 'paypal') {
    header("Location: pago_paypal.php?id_compra=$id_compra");
    exit;
}

if ($metodo_pago === 'tarjeta') {
    header("Location: pago_tarjeta.php?id_compra=$id_compra");
    exit;
}
?>
