<?php
// Archivo: index.php (en la raíz del proyecto)
// Página principal que muestra la cartelera de películas.

// Incluimos la cabecera pública.
require_once 'includes/public_header.php';
?>

<div class="cartelera-container">
    <h2>Nuestra Cartelera</h2>
    <p>Descubre las películas que tenemos para ti.</p>

    <div class="movie-grid">
        <?php
        $sql = "SELECT ID_pelicula, titulo, genero, duracion_minutos, clasificacion, sinopsis FROM Pelicula ORDER BY titulo";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($pelicula = $result->fetch_assoc()) {
        ?>
            <div class="movie-card">
                <h3><?php echo htmlspecialchars($pelicula['titulo']); ?></h3>
                <div class="movie-details">
                    <span><strong>Género:</strong> <?php echo htmlspecialchars($pelicula['genero']); ?></span>
                    <span><strong>Duración:</strong> <?php echo $pelicula['duracion_minutos']; ?> min.</span>
                    <span><strong>Clasificación:</strong> <?php echo htmlspecialchars($pelicula['clasificacion']); ?></span>
                </div>
                <p class="sinopsis"><?php echo htmlspecialchars($pelicula['sinopsis']); ?></p>
                
                <a href="pelicula_detalle.php?id=<?php echo $pelicula['ID_pelicula']; ?>" class="btn-comprar">Ver Horarios y Comprar</a>
            </div>
        <?php
            }
        } else {
            echo "<p>No hay películas en cartelera en este momento.</p>";
        }
        ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
