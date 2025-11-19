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
                line-height: 1.6;
                color: #333;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #ddd;
                padding-bottom: 20px;
            }
            .logo {
                max-width: 200px;
                margin-bottom: 15px;
            }
            .titulo {
                font-size: 24px;
                color: #2c3e50;
                margin: 10px 0;
            }
            .info-seccion {
                margin: 20px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 5px;
            }
            .productos-tabla {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .productos-tabla th, .productos-tabla td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .productos-tabla th {
                background-color: #f4f4f4;
            }
            .total {
                text-align: right;
                font-size: 18px;
                margin-top: 20px;
                padding: 10px;
                background: #e9ecef;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <!--<img src="../img/logo.png" class="logo">-->
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