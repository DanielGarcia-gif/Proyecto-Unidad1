<?php
require 'conexion.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

echo "<pre>";
var_dump(class_exists("Dompdf\\Dompdf"));
echo "</pre>";

function generarTicketPDF($id_compra) {
    global $conn;
    
    // Obtener información de la compra y el cliente
    $sql = "SELECT c.*, 
            COALESCE(u.nombre, ct.nombre) as nombre_cliente,
            COALESCE(u.email, ct.email) as email_cliente,
            e.direccion_envio, e.ciudad, e.codigo_postal
            FROM compras c
            LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
            LEFT JOIN compradores_temporales ct ON c.id_temporal = ct.id_temporal
            JOIN envios e ON c.id_envio = e.id_envio
            WHERE c.id_compra = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_compra);
    $stmt->execute();
    $result = $stmt->get_result();
    $compra = $result->fetch_assoc();

    if (!$compra) {
        return false;
    }

    // Obtener productos de la compra
    $sql = "SELECT dc.cantidad, dc.precio_unitario,
            p.nombre as nombre_producto, p.imagen,
            c.nombre_color, t.nombre_talla
            FROM detalleCompra dc
            JOIN variantesProducto vp ON dc.id_variante = vp.id_variante
            JOIN productos p ON vp.id_producto = p.id_producto
            JOIN colores c ON vp.id_color = c.id_color
            JOIN tallas t ON vp.id_talla = t.id_talla
            WHERE dc.id_compra = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_compra);
    $stmt->execute();
    $productos = $stmt->get_result();

    // Generar HTML del ticket
    $html = '
    <html>
    <head>
       <style>
            body {
                font-family: Arial, sans-serif;
                color: #333;
                padding: 20px;
            }

            .header {
                display: flex;
                align-items: center;
                gap: 15px;
                border-bottom: 2px solid #eee;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }

            .logo {
                width: 100px;
            }

            .titulo {
                font-size: 26px;
                color: #3949ab;
                font-weight: bold;
                margin: 0;
            }

            .info-seccion {
                margin: 20px 0;
                padding: 15px;
                background: #f1f4ff;
                border-left: 4px solid #3949ab;
                border-radius: 5px;
            }

            .info-seccion h3 {
                margin-top: 0;
                color: #3949ab;
            }

            .productos-tabla {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
                font-size: 14px;
            }

            .productos-tabla th {
                background: #3949ab;
                color: white;
                padding: 8px;
                text-align: left;
            }

            .productos-tabla td {
                border-bottom: 1px solid #ddd;
                padding: 8px;
            }

            .total {
                text-align: right;
                font-size: 18px;
                font-weight: bold;
                background: #e8eaf6;
                padding: 12px;
                border-radius: 5px;
                margin-top: 20px;
            }

            .footer {
                margin-top: 30px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #ddd;
                padding-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <!--<img src="./img/logo.jpg" class="logo">-->
            <h1 class="titulo">Ticket de Compra</h1>
        </div>
        
        <div class="info-seccion">
            <h3>Información del Cliente</h3>
            <p><strong>Nombre:</strong> ' . htmlspecialchars($compra['nombre_cliente']) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($compra['email_cliente']) . '</p>
            <p><strong>Número de Pedido:</strong> ' . $compra['id_compra'] . '</p>
            <p><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($compra['fecha_compra'])) . '</p>
        </div>

        <div class="info-seccion">
            <h3>Dirección de Envío</h3>
            <p>' . htmlspecialchars($compra['direccion_envio']) . '</p>
            <p>' . htmlspecialchars($compra['ciudad']) . ', CP: ' . htmlspecialchars($compra['codigo_postal']) . '</p>
        </div>

        <h3>Productos</h3>
        <table class="productos-tabla">
            <tr>
                <th>Producto</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>';
    $total = 0;
    while ($producto = $productos->fetch_assoc()) {
        $subtotal = $producto['cantidad'] * $producto['precio_unitario'];
        $total += $subtotal;
        
        $html .= '<tr>
                <td>' . htmlspecialchars($producto['nombre_producto']) . '</td>
                <td>' . htmlspecialchars($producto['nombre_color']) . '</td>
                <td>' . htmlspecialchars($producto['nombre_talla']) . '</td>
                <td>' . $producto['cantidad'] . '</td>
                <td>$' . number_format($producto['precio_unitario'], 2) . '</td>
                <td>$' . number_format($subtotal, 2) . '</td>
            </tr>';
    }

    $html .= '</table>
        
        <div class="total">
            <strong>Total:</strong> $' . number_format($compra['total_compra'], 2) . '
        </div>

        <div class="footer">
            <p>¡Gracias por tu compra!</p>
            <p>Fada Sports - Tu tienda de confianza</p>
        </div>
    </body>
    </html>';

    // Configurar Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4');
    $dompdf->render();


    $pdfContent = $dompdf->output();


    return $pdfContent; // devuelve el binario del PDF
}

/**
 * Genera el PDF y lo envía al navegador (inline) sin guardarlo en disco.
 * Termina la ejecución del script tras enviar el PDF.
 */
function generarTicketPDFStream($id_compra) {
    $pdfContent = generarTicketPDF($id_compra);
    if (!$pdfContent) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo 'Ticket no encontrado';
        exit;
    }

    $filename = 'ticket_' . $id_compra . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));
    echo $pdfContent;
    exit;
}
?>