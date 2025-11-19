<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

/* ===============================
   VALIDAR CAMPOS OBLIGATORIOS
   =============================== */
if (
    !isset($_POST['titular']) || trim($_POST['titular']) === "" ||
    !isset($_POST['numero_tarjeta']) || trim($_POST['numero_tarjeta']) === "" ||
    !isset($_POST['expiracion']) || trim($_POST['expiracion']) === ""
) {
    header("Location: ../perfil.php?error=faltan_campos");
    exit;
}

$titular = trim($_POST['titular']);
$numeroTarjeta = str_replace(" ", "", $_POST['numero_tarjeta']); // Quitar espacios
$exp = trim($_POST['expiracion']);

/* ===============================
   VALIDAR FECHA MM/AA
   =============================== */
if (!preg_match("/^\d{2}\/\d{2}$/", $exp)) {
    header("Location: ../perfil.php?error=fecha_invalida");
    exit;
}

list($mes, $anioCorto) = explode("/", $exp);
$anioCompleto = intval("20" . $anioCorto);

/* ===============================
   DETECTAR MARCA DE TARJETA
   =============================== */
$marca = "desconocida";

// VISA
if (preg_match("/^4/", $numeroTarjeta)) {
    $marca = "visa";
}
// MASTERCARD
else if (preg_match("/^5[1-5]/", $numeroTarjeta)) {
    $marca = "mastercard";
}
// AMEX
else if (preg_match("/^3[47]/", $numeroTarjeta)) {
    $marca = "amex";
}

/* ===============================
   ENMASCARAR TARJETA
   =============================== */
$ultimos4 = substr($numeroTarjeta, -4);
$numeroEnmascarado = "**** **** **** " . $ultimos4;

/* ===============================
   HASH PARA DETECTAR DUPLICADOS
   =============================== */
$hashTarjeta = hash("sha256", $numeroTarjeta);

/* ===============================
   VERIFICAR DUPLICADO
   =============================== */
$sqlCheck = "SELECT id_tarjeta FROM tarjetasUsuario 
             WHERE id_usuario = ? AND hash_tarjeta = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("is", $idUsuario, $hashTarjeta);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    header("Location: ../perfil.php?error=duplicada");
    exit;
}

/* ===============================
   INSERTAR TARJETA
   =============================== */
$sql = "INSERT INTO tarjetasUsuario 
        (id_usuario, titular, numero_tarjeta, hash_tarjeta, mes_expiracion, anio_expiracion, marca) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssiss",
    $idUsuario,
    $titular,
    $numeroEnmascarado,
    $hashTarjeta,
    $mes,
    $anioCompleto,
    $marca
);

if ($stmt->execute()) {
    header("Location: ../perfil.php");
    exit;
} else {
    header("Location: ../perfil.php?error=insertar");
    exit;
}
