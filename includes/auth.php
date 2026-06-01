<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function requiereRol($rol) {
    if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== $rol) {
        header('Location: ../login.php');
        exit;
    }
}
function usuarioActual() {
    return $_SESSION['nombre'] ?? '';
}
?>
