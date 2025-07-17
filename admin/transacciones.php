<?php
require_once '../config/database.php';

$result = $conn->query("
    SELECT Compra.ID_compra, Cliente.nombre, Cliente.apellidos, Compra.fecha_compra, Compra.total, Compra.metodo_pago
    FROM Compra
    JOIN Cliente ON Compra.DNI_cliente = Cliente.DNI
    ORDER BY Compra.fecha_compra DESC
");

echo "<h2>Historial de Compras</h2><table border='1'>";
echo "<tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Método</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['ID_compra']}</td>
        <td>{$row['nombre']} {$row['apellidos']}</td>
        <td>{$row['fecha_compra']}</td>
        <td>S/ {$row['total']}</td>
        <td>{$row['metodo_pago']}</td>
    </tr>";
}
echo "</table>";
?>
