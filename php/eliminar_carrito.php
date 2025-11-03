<?php
session_start();

if (isset($_POST['id_variante']) && isset($_SESSION['carrito'][$_POST['id_variante']])) {
    unset($_SESSION['carrito'][$_POST['id_variante']]);
}

header("Location: ../carrito/carrito.php");
exit;
?>
