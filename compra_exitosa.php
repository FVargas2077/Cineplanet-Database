<?php
// Archivo: compra_exitosa.php
// Muestra la confirmación de una compra realizada.

require_once 'includes/public_header.php';

// Seguridad: Redirigir si el usuario no ha iniciado sesión.
if (!isset($_SESSION['user_dni'])) {
    header("Location: login.php");
    exit();
}

// Validar que se haya proporcionado un ID de compra válido.
if (!isset($_GET['id_compra']) || !is_numeric($_GET['id_compra'])) {
    echo "<div class='container message error'>Compra no encontrada.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_compra = (int)$_GET['id_compra'];

// --- Consultar los datos de la compra para mostrarlos ---
$sql = "SELECT c.ID_compra, c.fecha_compra, c.total,
               p.titulo, f.fecha_hora, s.nombre AS nombre_sede
        FROM Compra c
        JOIN Boleto b ON c.ID_compra = b.ID_compra
        JOIN Funcion f ON b.ID_funcion = f.ID_funcion
        JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula
        JOIN Sala sa ON f.ID_sala = sa.ID_sala
        JOIN Sede s ON sa.ID_sede = s.ID_sede
        WHERE c.ID_compra = ? AND c.DNI_cliente = ?
        LIMIT 1"; // Solo necesitamos los datos generales una vez.

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_compra, $_SESSION['user_dni']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='container message error'>No tienes permiso para ver esta compra o no existe.</div>";
    require_once 'includes/footer.php';
    exit();
}
$compra_info = $result->fetch_assoc();

// Consultar los asientos específicos de esta compra.
$sql_boletos = "SELECT fila, numero_asiento FROM Boleto WHERE ID_compra = ?";
$stmt_boletos = $conn->prepare($sql_boletos);
$stmt_boletos->bind_param("i", $id_compra);
$stmt_boletos->execute();
$result_boletos = $stmt_boletos->get_result();
$asientos_comprados = [];
while ($boleto = $result_boletos->fetch_assoc()) {
    $asientos_comprados[] = $boleto['fila'] . '-' . $boleto['numero_asiento'];
}

?>

<div class="confirmation-container">
    <div class="confirmation-box">
        <div class="icon-success">&#10004;</div>
        <h1>¡Gracias por tu compra!</h1>
        <p>Tu transacción ha sido completada exitosamente.</p>
        
        <div class="purchase-details">
            <h3>Detalles de tu Entrada</h3>
            <p><strong>Película:</strong> <?php echo htmlspecialchars($compra_info['titulo']); ?></p>
            <p><strong>Sede:</strong> <?php echo htmlspecialchars($compra_info['nombre_sede']); ?></p>
            <p><strong>Fecha y Hora:</strong> <?php echo date("d/m/Y h:i A", strtotime($compra_info['fecha_hora'])); ?></p>
            <p><strong>Asientos:</strong> <?php echo implode(', ', $asientos_comprados); ?></p>
            <p><strong>Total Pagado:</strong> S/ <?php echo number_format($compra_info['total'], 2); ?></p>
            <p><strong>ID de Compra:</strong> #<?php echo $compra_info['ID_compra']; ?></p>
        </div>
        
        <a href="index.php" class="btn">Volver a la Cartelera</a>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
