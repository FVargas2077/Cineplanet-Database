<?php
// Archivo: admin/funciones.php
// Página para gestionar (Añadir/Eliminar) Funciones.

require_once '../includes/header.php';
// check_admin(); // Asegúrate de que el admin haya iniciado sesión

// --- LÓGICA PARA PROCESAR EL FORMULARIO DE AÑADIR FUNCIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_funcion'])) {
    // Recogemos los datos del formulario
    $id_pelicula = (int)$_POST['id_pelicula'];
    $id_sala = (int)$_POST['id_sala'];
    $fecha_hora = $_POST['fecha_hora'];
    $precio = (float)$_POST['precio'];

    // Preparamos la consulta SQL para insertar la nueva función
    $sql = "INSERT INTO Funcion (ID_pelicula, ID_sala, fecha_hora, precio_base) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisd", $id_pelicula, $id_sala, $fecha_hora, $precio);

    if ($stmt->execute()) {
        echo "<p class='message success'>Función añadida correctamente.</p>";
    } else {
        echo "<p class='message error'>Error al añadir la función: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- LÓGICA PARA ELIMINAR UNA FUNCIÓN ---
if (isset($_GET['delete_id'])) {
    $id_a_eliminar = (int)$_GET['delete_id'];
    $sql = "DELETE FROM Funcion WHERE ID_funcion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_a_eliminar);
    if ($stmt->execute()) {
        echo "<p class='message success'>Función eliminada correctamente.</p>";
    } else {
        echo "<p class='message error'>Error al eliminar la función: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- Consultas para llenar los dropdowns del formulario ---
$peliculas = $conn->query("SELECT ID_pelicula, titulo FROM Pelicula ORDER BY titulo");
$sedes = $conn->query("SELECT ID_sede, nombre FROM Sede ORDER BY nombre");
?>

<!-- Formulario para Añadir Nueva Función -->
<div class="form-container">
    <h2>Programar Nueva Función</h2>
    <form action="funciones.php" method="POST">
        <div class="form-group">
            <label for="id_pelicula">Película:</label>
            <select id="id_pelicula" name="id_pelicula" required>
                <option value="">Seleccione una película</option>
                <?php while ($p = $peliculas->fetch_assoc()): ?>
                    <option value="<?php echo $p['ID_pelicula']; ?>"><?php echo htmlspecialchars($p['titulo']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_sede">Sede:</label>
            <select id="id_sede" name="id_sede" required>
                <option value="">Seleccione una sede</option>
                <?php while ($s = $sedes->fetch_assoc()): ?>
                    <option value="<?php echo $s['ID_sede']; ?>"><?php echo htmlspecialchars($s['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_sala">Sala:</label>
            <select id="id_sala" name="id_sala" required>
                <option value="">Seleccione una sede primero</option>
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_hora">Fecha y Hora:</label>
            <input type="datetime-local" id="fecha_hora" name="fecha_hora" required>
        </div>

        <div class="form-group">
            <label for="precio">Precio (S/):</label>
            <input type="number" step="0.10" id="precio" name="precio" required>
        </div>

        <button type="submit" name="add_funcion" class="btn">Añadir Función</button>
    </form>
</div>

<!-- Tabla de Funciones Programadas -->
<div>
    <h2>Funciones Programadas</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Película</th>
                <th>Sede</th>
                <th>Sala</th>
                <th>Fecha y Hora</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT f.ID_funcion, p.titulo, s.nombre AS sede, sa.numero_sala, f.fecha_hora, f.precio_base
                    FROM Funcion f
                    JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula
                    JOIN Sala sa ON f.ID_sala = sa.ID_sala
                    JOIN Sede s ON sa.ID_sede = s.ID_sede
                    ORDER BY f.fecha_hora DESC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["titulo"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["sede"]) . "</td>";
                    echo "<td>Sala " . $row["numero_sala"] . "</td>";
                    echo "<td>" . date("d/m/Y h:i A", strtotime($row["fecha_hora"])) . "</td>";
                    echo "<td>S/ " . number_format($row["precio_base"], 2) . "</td>";
                    echo "<td><a href='funciones.php?delete_id=" . $row["ID_funcion"] . "' class='delete-btn' onclick='return confirm(\"¿Estás seguro?\");'>Eliminar</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No hay funciones programadas.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
// Script para cargar las salas dinámicamente según la sede seleccionada
document.getElementById('id_sede').addEventListener('change', function() {
    const sedeId = this.value;
    const salaSelect = document.getElementById('id_sala');
    salaSelect.innerHTML = '<option value="">Cargando...</option>';

    if (!sedeId) {
        salaSelect.innerHTML = '<option value="">Seleccione una sede primero</option>';
        return;
    }

    fetch('ajax_get_salas.php?sede_id=' + sedeId)
        .then(response => response.json())
        .then(data => {
            salaSelect.innerHTML = '<option value="">Seleccione una sala</option>';
            data.forEach(sala => {
                salaSelect.innerHTML += `<option value="${sala.ID_sala}">Sala ${sala.numero_sala} (${sala.tipo_sala})</option>`;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            salaSelect.innerHTML = '<option value="">Error al cargar salas</option>';
        });
});
</script>

<?php
require_once '../includes/footer.php';
?>
