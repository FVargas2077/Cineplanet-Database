<?php
// Archivo: includes/public_header.php
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cineplanet - Vive la experiencia del cine</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="public-header">
    <div class="container">
        <a href="index.php" class="logo">CINEPLANET</a>
        <nav>
            <ul>
                <li><a href="index.php">Cartelera</a></li>
                <!-- CORRECCIÓN: Enlaces ahora funcionales -->
                <li><a href="sedes.php">Sedes</a></li>
                <li><a href="dulceria_public.php">Dulcería</a></li>
                <?php if (isset($_SESSION['user_dni'])): ?>
                    <li><a href="perfil_socio.php">Mi Perfil (<?php echo htmlspecialchars($_SESSION['user_nombre']); ?>)</a></li>
                    <li><a href="logout.php" class="logout-btn">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="login-btn">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="container">
