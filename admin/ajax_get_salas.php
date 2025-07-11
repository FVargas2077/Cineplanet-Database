<?php
// Archivo: admin/ajax_get_salas.php
// Devuelve las salas de una sede específica en formato JSON.
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['sede_id'])) {
    echo json_encode([]);
    exit;
}

$sede_id = (int)$_GET['sede_id'];

$sql = "SELECT ID_sala, numero_sala, tipo_sala FROM Sala WHERE ID_sede = ? ORDER BY numero_sala";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sede_id);
$stmt->execute();
$result = $stmt->get_result();

$salas = [];
while ($row = $result->fetch_assoc()) {
    $salas[] = $row;
}

echo json_encode($salas);
?>
