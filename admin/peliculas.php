<?php
// Página para gestionar (Añadir/Eliminar) películas.
require_once '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_pelicula'])) {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $genero = $conn->real_escape_string($_POST['genero']);
    $duracion = (int)$_POST['duracion']; // Convertimos a entero
    $clasificacion = $conn->real_escape_string($_POST['clasificacion']);
    $sinopsis = $conn->real_escape_string($_POST['sinopsis']);
    $sql = "INSERT INTO Pelicula (titulo, genero, duracion_minutos, clasificacion, sinopsis) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $titulo, $genero, $duracion, $clasificacion, $sinopsis);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Película añadida correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error al añadir la película: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

if (isset($_GET['delete_id'])) {
    $id_a_eliminar = (int)$_GET['delete_id'];
    $sql = "DELETE FROM Pelicula WHERE ID_pelicula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_a_eliminar);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Película eliminada correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error al eliminar la película: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

?>

<div class="form-container">
    <h2>Añadir Nueva Película</h2>
    <form action="peliculas.php" method="POST">
        <label for="titulo">Título:</label>
        <input type="text" id="titulo" name="titulo" required>

        <label for="genero">Género:</label>
        <input type="text" id="genero" name="genero" required>

        <label for="duracion">Duración (minutos):</label>
        <input type="number" id="duracion" name="duracion" required>

        <label for="clasificacion">Clasificación:</label>
        <select id="clasificacion" name="clasificacion" required>
            <option value="ATP">ATP (Apta para Todo Público)</option>
            <option value="+13">+13</option>
            <option value="+16">+16</option>
            <option value="+18">+18</option>
        </select>

        <label for="sinopsis">Sinopsis:</label>
        <textarea id="sinopsis" name="sinopsis" rows="4" required></textarea>

        <button type="submit" name="add_pelicula" class="btn">Añadir Película</button>
    </form>
</div>

<div>
    <h2>Películas Actuales</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Género</th>
                <th>Duración</th>
                <th>Clasificación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT ID_pelicula, titulo, genero, duracion_minutos, clasificacion FROM Pelicula ORDER BY titulo";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["ID_pelicula"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["titulo"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["genero"]) . "</td>";
                    echo "<td>" . $row["duracion_minutos"] . " min</td>";
                    echo "<td>" . htmlspecialchars($row["clasificacion"]) . "</td>";
                    echo "<td><a href='peliculas.php?delete_id=" . $row["ID_pelicula"] . "' class='delete-btn' onclick='return confirm(\"¿Estás seguro de que quieres eliminar esta película?\");'>Eliminar</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No hay películas registradas.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>
