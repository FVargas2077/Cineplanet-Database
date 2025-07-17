<?php
require_once '../config/database.php';
require_once 'header_admin.php';

$sql = "SELECT c.*, u.nombre AS usuario, p.titulo AS pelicula 
        FROM compras c
        JOIN usuarios u ON c.usuario_id = u.id
        JOIN peliculas p ON c.pelicula_id = p.id
        ORDER BY c.fecha_compra DESC";

$result = $conn->query($sql);
?>

<h2>Historial de Compras</h2>
<table border="1">
    <tr><th>Usuario</th><th>Película</th><th>Cantidad</th><th>Total</th><th>Fecha</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['usuario'] ?></td>
        <td><?= $row['pelicula'] ?></td>
        <td><?= $row['cantidad'] ?></td>
        <td>S/ <?= number_format($row['total'], 2) ?></td>
        <td><?= $row['fecha_compra'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
