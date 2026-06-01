<?php if(session_status()===PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>VeraMedica</title><link rel="stylesheet" href="assets/css/estilos.css"></head><body><div class="page">
<header class="topbar"><div class="wrap nav"><a class="logo" href="index.php">
    <img src="assets/img/logo1.png" alt="VeraMedica">
</a><nav class="nav-links"><a href="index.php">Inicio</a><a href="catalogo.php">Catálogo</a><a href="servicios.php">Servicios</a><a href="citas.php">Citas</a><a href="contacto.php">Contacto</a><?php if(isset($_SESSION['id_usuario'])): ?><a class="btn-dark" href="logout.php">Salir</a><?php else: ?><a href="login.php">Iniciar Sesión</a><a class="btn-dark" href="registro.php">Registrarse</a><?php endif; ?></nav></div></header>
