<?php
// Inicia la sesión para poder usar variables $_SESSION en todo el proyecto.
session_start();


$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'CineDB';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Error de Conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

function check_admin() {
    if (!isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
        header("Location: ../login.php");
        exit();
    }
}
?>
