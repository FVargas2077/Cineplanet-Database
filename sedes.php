<?php
// Archivo: sedes.php - Página de Sedes
require_once 'includes/public_header.php';
?>
<div class="container">
    <h2>Nuestras Sedes</h2>
    <p>Encuentra tu Cineplanet más cercano y mira su cartelera.</p>
    
    <div class="info-grid">
        <?php
        // MEJORA: La consulta ahora solo selecciona sedes que tienen funciones programadas a futuro.
        $sql = "SELECT DISTINCT s.ID_sede, s.nombre, s.ciudad 
                FROM Sede s
                JOIN Sala sa ON s.ID_sede = sa.ID_sede
                JOIN Funcion f ON sa.ID_sala = f.ID_sala
                WHERE f.fecha_hora >= NOW()
                ORDER BY s.ciudad, s.nombre";
        
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($sede = $result->fetch_assoc()) {
        ?>
            <div class="info-card">
                <h3><?php echo htmlspecialchars($sede['nombre']); ?></h3>
                <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($sede['ciudad']); ?></p>
                <!-- El enlace pasa el ID de la sede a index.php para filtrar la cartelera -->
                <a href="index.php?sede_id=<?php echo $sede['ID_sede']; ?>" class="btn">Ver Cartelera</a>
            </div>
        <?php
            }
        } else {
            echo "<p>No hay sedes con funciones disponibles en este momento.</p>";
        }
        ?>
    </div>
</div>
<?php
require_once 'includes/footer.php';
?>
