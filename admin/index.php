<?php
// Archivo: admin/index.php
// Página principal del panel de administración.

// Incluimos el header que ya contiene la conexión a la BD y la lógica de sesión.
require_once '../includes/header.php';

// Verificamos si el usuario es administrador.
// Si no lo es, la función check_admin() lo redirigirá al login.
// Por ahora, comentaremos esta línea para poder acceder sin un login funcional.
// check_admin(); 
?>

<div class="container">
    <h2>Bienvenido al Panel de Administración</h2>
    <p>
        Desde aquí puedes gestionar el contenido del sitio web de Cineplanet.
    </p>
    <p>
        Utiliza el menú de navegación de arriba para empezar a gestionar las <strong>películas</strong> y los productos de <strong>dulcería</strong>.
    </p>
    
    <!-- Puedes agregar aquí estadísticas rápidas en el futuro -->
    <div class="stats-container" style="margin-top: 40px;">
        <h3>Estadísticas Rápidas</h3>
        <?php
        // Consulta para contar el número total de películas
        $result_peliculas = $conn->query("SELECT COUNT(*) as total FROM Pelicula");
        $total_peliculas = $result_peliculas->fetch_assoc()['total'];

        // Consulta para contar el número total de productos de dulcería
        $result_dulceria = $conn->query("SELECT COUNT(*) as total FROM Dulceria");
        $total_dulceria = $result_dulceria->fetch_assoc()['total'];
        ?>
        <ul>
            <li>Total de Películas en Cartelera: <strong><?php echo $total_peliculas; ?></strong></li>
            <li>Total de Productos en Dulcería: <strong><?php echo $total_dulceria; ?></strong></li>
        </ul>
    </div>
</div>

<?php
// Incluimos el pie de página
require_once '../includes/footer.php';
?>
