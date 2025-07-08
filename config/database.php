<?php
// Archivo: config/database.php
// Inicia la sesión para poder usar variables $_SESSION en todo el proyecto.
session_start();

// --- Configuración de la Conexión a la Base de Datos ---
$db_host = 'localhost'; // Generalmente es 'localhost' en XAMPP
$db_user = 'root';      // Usuario por defecto de MySQL en XAMPP
$db_pass = '';          // Contraseña por defecto de MySQL en XAMPP es vacía
$db_name = 'CineDB';    // El nombre de tu base de datos

// --- Crear la Conexión ---
// Se utiliza mysqli para la conexión.
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Verificar Conexión ---
// Si hay un error de conexión, el script se detiene y muestra el error.
if ($conn->connect_error) {
    // die() detiene la ejecución del script.
    die("Error de Conexión: " . $conn->connect_error);
}

// --- Establecer el juego de caracteres a UTF-8 ---
// Esto es importante para evitar problemas con tildes y caracteres especiales (ñ).
$conn->set_charset("utf8");

// --- Función de seguridad para verificar si el admin ha iniciado sesión ---
// Esta función se usará en todas las páginas del panel de administrador.
function check_admin() {
    // Verifica si la variable de sesión 'user_id' existe Y si 'es_admin' es verdadero.
    // Estas variables se deberían crear en el archivo login.php (que haremos más adelante).
    if (!isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
        // Si el usuario no es admin, lo redirige a la página de login.
        header("Location: ../login.php");
        exit(); // Detiene la ejecución del script para seguridad.
    }
}
?>
