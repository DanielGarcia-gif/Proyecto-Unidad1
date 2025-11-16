<?php
session_start();
require 'conexion.php';

// Configuración de la cuenta sandbox
$clientId = 'AQ5-NMQ3umSEBt0KFO18fJOZSIo4_ACpzl_jeU3R1ikWIPJPXNO3fjCbZrVmiFEeaBp4JgU_gbBpfb1y';
$clientSecret = 'EGg7xXDre-WF5qT9lq0qF-XfhVxQMS2KijaVlles7hLOXL__Cwl5fkLrIZO2AkwfKe49_jaMFzZ-fTzm';

// Obtener compra
$id_compra = $_GET['id_compra'] ?? null;
if (!$id_compra) {
    die("Compra no especificada");
}

// Obtener total de la compra
$sql = "SELECT total_compra FROM compras WHERE id_compra = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_compra);
$stmt->execute();
$result = $stmt->get_result();
$compra = $result->fetch_assoc();
$total = $compra['total_compra'] ?? 0;

// API OAuth (token)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

$response = json_decode(curl_exec($ch));
$accessToken = $response->access_token;
curl_close($ch);

// Crear orden de pago
$order = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "amount" => [
            "currency_code" => "MXN",
            "value" => number_format($total, 2, '.', '')
        ]
    ]],
    "application_context" => [
        "return_url" => "http://localhost/Proyecto-Unidad1/php/confirmacion_paypal.php?id_compra=$id_compra",
        "cancel_url" => "http://localhost/Proyecto-Unidad1/detalle_compra.php"
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v2/checkout/orders");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $accessToken"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($response['links'])) {
    foreach ($response['links'] as $link) {
        if ($link['rel'] === 'approve') {
            header("Location: " . $link['href']);
            exit;
        }
    }   
}

echo "Error al crear el pago en PayPal.";
