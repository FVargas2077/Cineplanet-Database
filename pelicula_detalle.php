<?php
// Muestra los detalles de una película y sus funciones disponibles.

require_once 'includes/public_header.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Película no encontrada.</p>";
    require_once 'includes/footer.php';
    exit();
}

$id_pelicula = (int)$_GET['id'];
$sql_pelicula = "SELECT titulo, genero, duracion_minutos, clasificacion, sinopsis FROM Pelicula WHERE ID_pelicula = ?";
$stmt_pelicula = $conn->prepare($sql_pelicula);
$stmt_pelicula->bind_param("i", $id_pelicula);
$stmt_pelicula->execute();
$result_pelicula = $stmt_pelicula->get_result();

if ($result_pelicula->num_rows == 0) {
    echo "<p>Película no encontrada.</p>";
    require_once 'includes/footer.php';
    exit();
}
$pelicula = $result_pelicula->fetch_assoc();
$stmt_pelicula->close();

?>

<div class="movie-detail-container">
    <div class="movie-info">
        <h1><?php echo htmlspecialchars($pelicula['titulo']); ?></h1>
        <div class="tags">
            <span class="tag"><?php echo htmlspecialchars($pelicula['genero']); ?></span>
            <span class="tag"><?php echo $pelicula['duracion_minutos']; ?> min</span>
            <span class="tag clasificacion-<?php echo strtolower($pelicula['clasificacion']); ?>">
                <?php echo htmlspecialchars($pelicula['clasificacion']); ?>
            </span>
        </div>
        <h3>Sinopsis</h3>
        <p><?php echo nl2br(htmlspecialchars($pelicula['sinopsis'])); ?></p>
    </div>

    <div class="funciones-container">
        <h2>Horarios Disponibles</h2>
        <?php
        $sql_funciones = "SELECT f.ID_funcion, f.fecha_hora, f.precio_base,
                                 s.nombre AS nombre_sede, sa.numero_sala, sa.tipo_sala
                          FROM Funcion f
                          JOIN Sala sa ON f.ID_sala = sa.ID_sala
                          JOIN Sede s ON sa.ID_sede = s.ID_sede
                          WHERE f.ID_pelicula = ? AND f.fecha_hora >= NOW()
                          ORDER BY s.nombre, f.fecha_hora";
        
        $stmt_funciones = $conn->prepare($sql_funciones);
        $stmt_funciones->bind_param("i", $id_pelicula);
        $stmt_funciones->execute();
        $result_funciones = $stmt_funciones->get_result();

        if ($result_funciones->num_rows > 0) {
            $current_sede = '';
            while ($funcion = $result_funciones->fetch_assoc()) {
                if ($funcion['nombre_sede'] !== $current_sede) {
                    if ($current_sede !== '') echo "</div>";
                    $current_sede = $funcion['nombre_sede'];
                    echo "<div class='sede-group'>";
                    echo "<h3>" . htmlspecialchars($current_sede) . "</h3>";
                }
        ?>
                <a href="comprar_boleto.php?id_funcion=<?php echo $funcion['ID_funcion']; ?>" class="funcion-item">
                    <div class="hora"><?php echo date("d/m/Y", strtotime($funcion['fecha_hora'])); ?></div>
                    <div class="hora"><?php echo date("g:i A", strtotime($funcion['fecha_hora'])); ?></div>
                    <div class="sala-tipo"><?php echo htmlspecialchars($funcion['tipo_sala']); ?></div>
                    <div class="precio">S/ <?php echo number_format($funcion['precio_base'], 2); ?></div>
                </a>
        <?php
            }
            echo "</div>";
        } else {
            echo "<p>No hay funciones programadas para esta película en el futuro.</p>";
        }
        $stmt_funciones->close();
        ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
