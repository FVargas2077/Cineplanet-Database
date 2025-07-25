<?php
// Muestra la confirmación de una compra (boletos o dulcería).
require_once 'includes/public_header.php';

if (!isset($_SESSION['user_dni'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id_compra']) || !is_numeric($_GET['id_compra'])) {
    echo "<div class='container message error'>Compra no encontrada.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_compra = (int)$_GET['id_compra'];
$sql_compra = "SELECT ID_compra, fecha_compra, total FROM Compra WHERE ID_compra = ? AND DNI_cliente = ?";
$stmt_compra = $conn->prepare($sql_compra);
$stmt_compra->bind_param("is", $id_compra, $_SESSION['user_dni']);
$stmt_compra->execute();
$result_compra = $stmt_compra->get_result();
if ($result_compra->num_rows == 0) {
    echo "<div class='container message error'>No tienes permiso para ver esta compra.</div>";
    require_once 'includes/footer.php';
    exit();
}
$compra_info = $result_compra->fetch_assoc();
$es_compra_boletos = false;
$boletos = [];
$sql_boletos = "SELECT p.titulo, f.fecha_hora, s.nombre AS nombre_sede, b.fila, b.numero_asiento FROM Boleto b JOIN Funcion f ON b.ID_funcion = f.ID_funcion JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula JOIN Sala sa ON f.ID_sala = sa.ID_sala JOIN Sede s ON sa.ID_sede = s.ID_sede WHERE b.ID_compra = ?";
$stmt_boletos = $conn->prepare($sql_boletos);
$stmt_boletos->bind_param("i", $id_compra);
$stmt_boletos->execute();
$result_boletos = $stmt_boletos->get_result();
if ($result_boletos->num_rows > 0) {
    $es_compra_boletos = true;
    while($row = $result_boletos->fetch_assoc()) { $boletos[] = $row; }
}

$es_compra_dulceria = false;
$dulceria_items = [];
$sql_dulceria = "SELECT d.nombre, dt.cantidad, dt.precio_unitario FROM Detalle_Compra_Dulceria dt JOIN Dulceria d ON dt.ID_producto = d.ID_producto WHERE dt.ID_compra = ?";
$stmt_dulceria = $conn->prepare($sql_dulceria);
$stmt_dulceria->bind_param("i", $id_compra);
$stmt_dulceria->execute();
$result_dulceria = $stmt_dulceria->get_result();
if ($result_dulceria->num_rows > 0) {
    $es_compra_dulceria = true;
    while($row = $result_dulceria->fetch_assoc()) { $dulceria_items[] = $row; }
}
?>

<div class="confirmation-container">
    <div class="confirmation-box">
        <div class="icon-success">&#10004;</div>
        <h1>¡Gracias por tu compra!</h1>
        <p>Tu transacción ha sido completada exitosamente.</p>
        
        <div class="purchase-details">
            <?php if ($es_compra_boletos): ?>
                <h3>Detalles de tu Entrada</h3>
                <p><strong>Película:</strong> <?php echo htmlspecialchars($boletos[0]['titulo']); ?></p>
                <p><strong>Sede:</strong> <?php echo htmlspecialchars($boletos[0]['nombre_sede']); ?></p>
                <p><strong>Fecha y Hora:</strong> <?php echo date("d/m/Y h:i A", strtotime($boletos[0]['fecha_hora'])); ?></p>
                <p><strong>Asientos:</strong> <?php echo implode(', ', array_map(fn($b) => $b['fila'].'-'.$b['numero_asiento'], $boletos)); ?></p>
            <?php endif; ?>
            
            <?php if ($es_compra_dulceria): ?>
                <h3>Detalles de tu Pedido de Dulcería</h3>
                <ul>
                    <?php foreach ($dulceria_items as $item): ?>
                        <li><?php echo $item['cantidad']; ?> x <?php echo htmlspecialchars($item['nombre']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <p><strong>Total Pagado:</strong> S/ <?php echo number_format($compra_info['total'], 2); ?></p>
            <p><strong>ID de Compra:</strong> #<?php echo $compra_info['ID_compra']; ?></p>
        </div>
        
        <a href="index.php" class="btn">Volver a la Cartelera</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
