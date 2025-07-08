<?php
// Archivo: includes/header.php
// Incluye la configuración de la base de datos para que esté disponible en todas las páginas.
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Cineplanet</title>
    <!-- Enlace a la hoja de estilos CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="admin-header">
    <div class="container">
        <h1>Panel de Administración de Cineplanet</h1>
        <nav>
            <ul>
                <!-- Menú de navegación para el administrador -->
                <li><a href="index.php">Inicio Admin</a></li>
                <li><a href="peliculas.php">Gestionar Películas</a></li>
                <li><a href="dulceria.php">Gestionar Dulcería</a></li>
                <!-- Este enlace debería llevar al logout.php que crearemos después -->
                <li><a href="../logout.php" class="logout-btn">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="container">
    <!-- El contenido principal de cada página irá aquí -->
