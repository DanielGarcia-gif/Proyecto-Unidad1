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
$numeroTarjeta = str_replace(" ", "", $_POST['numero_tarjeta']);
$exp = trim($_POST['expiracion']);

/* ===============================
   VALIDAR FECHA
   =============================== */
if (!preg_match("/^\d{2}\/\d{2}$/", $exp)) {
    header("Location: ../perfil.php?error=fecha_invalida");
    exit;
}

list($mes, $anioCorto) = explode("/", $exp);
$anioCompleto = intval("20" . $anioCorto);

/* ===============================
   ENMASCARAR TARJETA
   =============================== */
$ultimos4 = substr($numeroTarjeta, -4);
$numeroEnmascarado = "**** **** **** " . $ultimos4;

/* ===============================
   HASH (DETECTAR DUPLICADOS)
   =============================== */
$hashTarjeta = hash("sha256", $numeroTarjeta);

/* ===============================
   VERIFICAR TARJETA DUPLICADA
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
        (id_usuario, titular, numero_tarjeta, hash_tarjeta, mes_expiracion, anio_expiracion) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssis",
    $idUsuario,
    $titular,
    $numeroEnmascarado,
    $hashTarjeta,
    $mes,
    $anioCompleto
);

if ($stmt->execute()) {
    header("Location: ../perfil.php"); 
    exit;
} else {
    header("Location: ../perfil.php?error=insertar");
    exit;
}
