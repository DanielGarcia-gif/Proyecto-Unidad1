<?php
require_once 'auth.php';
require_once 'conexion.php';

// Sólo admin
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/productos.php');
    exit;
}

$action = $_POST['action'] ?? '';
$startedTx = false;

try {
    if ($action === 'add') {
        // Datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $material = trim($_POST['material'] ?? '');
        $id_color = isset($_POST['id_color']) && $_POST['id_color'] !== '' ? (int)$_POST['id_color'] : null;
        $id_talla = isset($_POST['id_talla']) && $_POST['id_talla'] !== '' ? (int)$_POST['id_talla'] : null;
        $precio = (float)($_POST['precio'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);

        if (!$nombre || $precio <= 0) {
            throw new Exception('Nombre y precio son obligatorios y precio debe ser positivo');
        }

        $conn->begin_transaction();
        $startedTx = true;

        // Buscar producto por nombre
        $sql = "SELECT id_producto FROM productos WHERE nombre = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $nombre);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $id_producto = $row['id_producto'];
            // actualizar datos básicos
            $up = $conn->prepare("UPDATE productos SET descripcion = ?, material = ?, categoria = ? WHERE id_producto = ?");
            $up->bind_param('sssi', $descripcion, $material, $categoria, $id_producto);
            $up->execute();
        } else {
            $ins = $conn->prepare("INSERT INTO productos (nombre, descripcion, material, categoria) VALUES (?, ?, ?, ?)");
            $ins->bind_param('ssss', $nombre, $descripcion, $material, $categoria);
            $ins->execute();
            $id_producto = $ins->insert_id;
        }

        // Insertar variante
        $sqlv = "INSERT INTO variantesProducto (id_producto, id_color, id_talla, precio, stock) VALUES (?, ?, ?, ?, ?)";
        $stmtv = $conn->prepare($sqlv);
        // bind types: i i i d i (note: id_color/id_talla may be null)
        $bind_id_color = $id_color;
        $bind_id_talla = $id_talla;
        $stmtv->bind_param('iiidi', $id_producto, $bind_id_color, $bind_id_talla, $precio, $stock);
        $stmtv->execute();

        $conn->commit();
        $startedTx = false;

        header('Location: ../admin/productos.php');
        exit;
    }

    if ($action === 'delete') {
        $id_variante = isset($_POST['id_variante']) ? (int)$_POST['id_variante'] : 0;
        if ($id_variante <= 0) {
            throw new Exception('ID de variante inválido');
        }

        $conn->begin_transaction();
        $startedTx = true;

        // 1. Verificar que la variante existe y obtener su id_producto
        $stmt = $conn->prepare("SELECT vp.id_variante, vp.id_producto, 
                                    (SELECT COUNT(*) FROM detalleCompra dc WHERE dc.id_variante = vp.id_variante) as num_compras,
                                    (SELECT COUNT(*) FROM variantesProducto vp2 WHERE vp2.id_producto = vp.id_producto) as num_variantes
                            FROM variantesProducto vp 
                            WHERE vp.id_variante = ?");
        $stmt->bind_param('i', $id_variante);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res || $res->num_rows === 0) {
            throw new Exception('Variante no encontrada');
        }

        $row = $res->fetch_assoc();
        $id_producto = $row['id_producto'];
        $num_compras = (int)$row['num_compras'];
        $num_variantes = (int)$row['num_variantes'];

        // 2. Si tiene compras asociadas, marcar como inactiva en lugar de borrar
        if ($num_compras > 0) {
            $stmt = $conn->prepare("UPDATE variantesProducto SET activo = 0, stock = 0 WHERE id_variante = ?");
            $stmt->bind_param('i', $id_variante);
            $stmt->execute();
        } else {
            // No tiene compras: borrar la variante
            $stmt = $conn->prepare("DELETE FROM variantesProducto WHERE id_variante = ?");
            $stmt->bind_param('i', $id_variante);
            $stmt->execute();
        }

        // 3. Si era la última variante del producto, borrar el producto también
        if ($num_variantes === 1) {
            $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->bind_param('i', $id_producto);
            $stmt->execute();
        }

        $conn->commit();
        $startedTx = false;

        header('Location: ../admin/productos.php');
        exit;
    }

    if ($action === 'edit') {
        $id_variante = isset($_POST['id_variante']) ? (int)$_POST['id_variante'] : 0;
        $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : null;
        $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : null;
        if ($id_variante <= 0) throw new Exception('ID inválido');
        $sets = [];
        $types = '';
        $params = [];
        if ($precio !== null) { $sets[] = 'precio = ?'; $types .= 'd'; $params[] = $precio; }
        if ($stock !== null) { $sets[] = 'stock = ?'; $types .= 'i'; $params[] = $stock; }
        if (count($sets) === 0) throw new Exception('Nada que actualizar');
        $sql = 'UPDATE variantesProducto SET ' . implode(', ', $sets) . ' WHERE id_variante = ?';
        $types .= 'i';
        $params[] = $id_variante;
        $stmt = $conn->prepare($sql);
        // bind dynamically
        $bind_names[] = $types;
        for ($i=0;$i<count($params);$i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        $stmt->execute();
        header('Location: ../admin/productos.php');
        exit;
    }

    // other actions (edit) can be implemented similarly

} catch (Exception $e) {
    if ($startedTx) {
        $conn->rollback();
    }
    error_log('admin_productos_action error: ' . $e->getMessage());
    header('Location: ../admin/productos.php');
    exit;
}

// Fallback redirect
header('Location: ../admin/productos.php');
exit;
