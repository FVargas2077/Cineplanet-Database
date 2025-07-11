<?php
// Archivo: admin/transacciones.php
// Página para que el administrador vea y filtre todas las compras.

require_once '../includes/header.php';
// check_admin(); // Asegúrate de que el admin haya iniciado sesión

// --- LÓGICA DE FILTRADO Y ORDENACIÓN ---
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todas';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';

// Construcción de la consulta principal
$sql = "SELECT c.ID_compra, c.fecha_compra, c.total, c.metodo_pago, cl.nombre, cl.apellidos
        FROM Compra c
        JOIN Cliente cl ON c.DNI_cliente = cl.DNI";

// Aplicar filtro por tipo de compra
if ($filtro_tipo == 'peliculas') {
    // Muestra solo compras que tienen al menos un boleto
    $sql .= " WHERE c.ID_compra IN (SELECT DISTINCT ID_compra FROM Boleto)";
} elseif ($filtro_tipo == 'dulceria') {
    // Muestra solo compras que tienen al menos un item de dulcería
    $sql .= " WHERE c.ID_compra IN (SELECT DISTINCT ID_compra FROM Detalle_Compra_Dulceria)";
}

// Aplicar ordenación
if ($orden == 'precio_desc') {
    $sql .= " ORDER BY c.total DESC";
} else {
    $sql .= " ORDER BY c.fecha_compra DESC";
}

$compras_result = $conn->query($sql);
?>

<div class="container">
    <h2>Reporte de Transacciones</h2>
    
    <!-- Formulario de Filtros -->
    <form action="transacciones.php" method="GET" class="filter-form">
        <div class="form-group">
            <label for="tipo">Filtrar por Tipo:</label>
            <select name="tipo" id="tipo">
                <option value="todas" <?php if ($filtro_tipo == 'todas') echo 'selected'; ?>>Todas las Compras</option>
                <option value="peliculas" <?php if ($filtro_tipo == 'peliculas') echo 'selected'; ?>>Solo Películas</option>
                <option value="dulceria" <?php if ($filtro_tipo == 'dulceria') echo 'selected'; ?>>Solo Dulcería</option>
            </select>
        </div>
        <div class="form-group">
            <label for="orden">Ordenar por:</label>
            <select name="orden" id="orden">
                <option value="recientes" <?php if ($orden == 'recientes') echo 'selected'; ?>>Más Recientes</option>
                <option value="precio_desc" <?php if ($orden == 'precio_desc') echo 'selected'; ?>>Precio (Mayor a menor)</option>
            </select>
        </div>
        <button type="submit" class="btn">Aplicar Filtros</button>
    </form>

    <!-- Tabla de Transacciones -->
    <div class="transaction-list">
        <?php if ($compras_result && $compras_result->num_rows > 0): ?>
            <?php while($compra = $compras_result->fetch_assoc()): ?>
                <div class="transaction-card">
                    <div class="transaction-header">
                        <h4>Compra #<?php echo $compra['ID_compra']; ?></h4>
                        <span><strong>Cliente:</strong> <?php echo htmlspecialchars($compra['nombre'] . ' ' . $compra['apellidos']); ?></span>
                        <span><strong>Fecha:</strong> <?php echo date("d/m/Y h:i A", strtotime($compra['fecha_compra'])); ?></span>
                        <span class="total"><strong>Total: S/ <?php echo number_format($compra['total'], 2); ?></strong></span>
                    </div>
                    <div class="transaction-details">
                        <h5>Detalles:</h5>
                        <?php
                        // --- Obtener detalles de boletos para esta compra ---
                        $id_compra = $compra['ID_compra'];
                        $sql_boletos = "SELECT p.titulo, b.fila, b.numero_asiento, b.precio_pagado 
                                        FROM Boleto b 
                                        JOIN Funcion f ON b.ID_funcion = f.ID_funcion 
                                        JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula 
                                        WHERE b.ID_compra = ?";
                        $stmt_boletos = $conn->prepare($sql_boletos);
                        $stmt_boletos->bind_param("i", $id_compra);
                        $stmt_boletos->execute();
                        $boletos_result = $stmt_boletos->get_result();
                        if ($boletos_result->num_rows > 0) {
                            echo "<ul>";
                            while($boleto = $boletos_result->fetch_assoc()) {
                                echo "<li><strong>Boleto:</strong> " . htmlspecialchars($boleto['titulo']) . " - Asiento: " . $boleto['fila'] . $boleto['numero_asiento'] . " (S/ " . number_format($boleto['precio_pagado'], 2) . ")</li>";
                            }
                            echo "</ul>";
                        }

                        // --- Obtener detalles de dulcería para esta compra ---
                        $sql_dulceria = "SELECT d.nombre, dt.cantidad, dt.precio_unitario 
                                         FROM Detalle_Compra_Dulceria dt 
                                         JOIN Dulceria d ON dt.ID_producto = d.ID_producto 
                                         WHERE dt.ID_compra = ?";
                        $stmt_dulceria = $conn->prepare($sql_dulceria);
                        $stmt_dulceria->bind_param("i", $id_compra);
                        $stmt_dulceria->execute();
                        $dulceria_result = $stmt_dulceria->get_result();
                        if ($dulceria_result->num_rows > 0) {
                            echo "<ul>";
                            while($item = $dulceria_result->fetch_assoc()) {
                                echo "<li><strong>Dulcería:</strong> " . $item['cantidad'] . " x " . htmlspecialchars($item['nombre']) . " (S/ " . number_format($item['precio_unitario'], 2) . " c/u)</li>";
                            }
                            echo "</ul>";
                        }
                        ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No se encontraron transacciones con los filtros seleccionados.</p>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
