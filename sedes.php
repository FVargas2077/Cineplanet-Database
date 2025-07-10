<?php // Archivo: sedes.php - Página de Sedes
require_once 'includes/public_header.php';
?>
<div class="container">
    <h2>Nuestras Sedes</h2>
    <p>Encuentra tu Cineplanet más cercano.</p>
    
    <div class="info-grid">
        <?php
        $sql = "SELECT nombre, ciudad FROM Sede ORDER BY ciudad, nombre";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($sede = $result->fetch_assoc()) {
        ?>
            <div class="info-card">
                <h3><?php echo htmlspecialchars($sede['nombre']); ?></h3>
                <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($sede['ciudad']); ?></p>
                <a href="index.php?sede_id=<?php echo $sede['ID_sede'] ?? ''; ?>" class="btn">Ver Cartelera</a>
            </div>
        <?php
            }
        } else {
            echo "<p>No hay sedes registradas en este momento.</p>";
        }
        ?>
    </div>
</div>
<?php
require_once 'includes/footer.php';
?>