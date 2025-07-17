<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// INSERTAR nueva película
if (isset($_POST['insertar'])) {
    $titulo = $_POST['titulo'];
    $genero = $_POST['genero'];
    $duracion = $_POST['duracion'];
    $clasificacion = $_POST['clasificacion'];
    $sinopsis = $_POST['sinopsis'];

    $stmt = $conn->prepare("INSERT INTO Pelicula (titulo, genero, duracion_minutos, clasificacion, sinopsis) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $titulo, $genero, $duracion, $clasificacion, $sinopsis);
    $stmt->execute();
    $stmt->close();
    header("Location: peliculas.php");
    exit();
}

// ACTUALIZAR película
if (isset($_POST['actualizar'])) {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $genero = $_POST['genero'];
    $duracion = $_POST['duracion'];
    $clasificacion = $_POST['clasificacion'];
    $sinopsis = $_POST['sinopsis'];

    $stmt = $conn->prepare("UPDATE Pelicula SET titulo=?, genero=?, duracion_minutos=?, clasificacion=?, sinopsis=? WHERE ID_pelicula=?");
    $stmt->bind_param("ssissi", $titulo, $genero, $duracion, $clasificacion, $sinopsis, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: peliculas.php");
    exit();
}

// ELIMINAR película
if (isset($_POST['eliminar'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM Pelicula WHERE ID_pelicula=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: peliculas.php");
    exit();
}

$peliculas = $conn->query("SELECT * FROM Pelicula");
?>

<div class="form-container">
    <h2>Agregar Nueva Película</h2>
    <form method="POST" class="form-group">
        <input type="text" name="titulo" placeholder="Título" required>
        <input type="text" name="genero" placeholder="Género" required>
        <input type="number" name="duracion" placeholder="Duración (min)" required>
        <select name="clasificacion" required>
            <option value="ATP">ATP</option>
            <option value="+13">+13</option>
            <option value="+16">+16</option>
            <option value="+18">+18</option>
        </select>
        <textarea name="sinopsis" placeholder="Sinopsis"></textarea>
        <button type="submit" name="insertar" class="btn">Insertar</button>
    </form>
</div>

<div class="data-table-container">
    <h2>Películas Existentes</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Género</th>
                <th>Duración</th>
                <th>Clasificación</th>
                <th>Sinopsis</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $peliculas->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $row['ID_pelicula'] ?>">
                    <td><input type="text" name="titulo" value="<?= htmlspecialchars($row['titulo']) ?>"></td>
                    <td><input type="text" name="genero" value="<?= htmlspecialchars($row['genero']) ?>"></td>
                    <td><input type="number" name="duracion" value="<?= $row['duracion_minutos'] ?>"></td>
                    <td>
                        <select name="clasificacion">
                            <option value="ATP" <?= $row['clasificacion'] == 'ATP' ? 'selected' : '' ?>>ATP</option>
                            <option value="+13" <?= $row['clasificacion'] == '+13' ? 'selected' : '' ?>>+13</option>
                            <option value="+16" <?= $row['clasificacion'] == '+16' ? 'selected' : '' ?>>+16</option>
                            <option value="+18" <?= $row['clasificacion'] == '+18' ? 'selected' : '' ?>>+18</option>
                        </select>
                    </td>
                    <td><textarea name="sinopsis"><?= htmlspecialchars($row['sinopsis']) ?></textarea></td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button type="submit" name="actualizar"
                                style="flex: 1; background-color: #3498db; color: white; border: none; padding: 6px 0; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                Actualizar
                            </button>
                            <button type="submit" name="eliminar" onclick="return confirm('¿Eliminar película?')"
                                style="flex: 1; background-color: #e74c3c; color: white; border: none; padding: 6px 0; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                Eliminar
                            </button>
                        </div>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
