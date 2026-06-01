<?php require '../config/conexion.php'; require '../includes/auth.php'; requiereRol('mostrador'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrador VeraMedica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">Vera<span>Medica</span></div>
        <div class="sidebar-role">Mostrador</div>
        <div class="sidebar-user"><?=htmlspecialchars(usuarioActual())?></div>

        <nav class="sidebar-nav">
            <a href="dashboard.php">Inicio</a>
            <a href="citas.php">Citas</a>
            <a href="productos.php">Productos</a>
            <a href="servicios.php">Servicios</a>
            <a href="personal.php">Personal</a>
            <a href="horarios.php">Horarios</a>
            <a href="promociones.php">Promociones</a>
            <a href="../logout.php">Salir</a>
        </nav>
    </aside>
    <main class="admin-main">
