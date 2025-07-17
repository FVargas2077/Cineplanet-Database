<?php
// Archivo: admin/funciones.php
require_once '../includes/header.php';
// check_admin(); // Descomenta si ya usas control de sesión para admin

// --- LÓGICA PARA AÑADIR NUEVA FUNCIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_funcion'])) {
    $id_pelicula = (int)$_POST['id_pelicula'];
    $id_sala = (int)$_POST['id_sala'];
    $fecha_hora = $_POST['fecha_hora'];
    $precio = (float)$_POST['precio'];

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

// --- LÓGICA PARA ACTUALIZAR UNA FUNCIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_funcion'])) {
    $id_funcion = (int)$_POST['id_funcion'];
    $fecha_hora = $_POST['fecha_hora'];
    $precio = (float)$_POST['precio'];

    $sql = "UPDATE Funcion SET fecha_hora = ?, precio_base = ? WHERE ID_funcion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $fecha_hora, $precio, $id_funcion);
    if ($stmt->execute()) {
        echo "<p class='message success'>Función actualizada correctamente.</p>";
    } else {
        echo "<p class='message error'>Error al actualizar la función: " . $stmt->error . "</p>";
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

// --- Cargar películas y sedes para el formulario ---
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
                    <option value="<?= $p['ID_pelicula'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_sede">Sede:</label>
            <select id="id_sede" name="id_sede" required>
                <option value="">Seleccione una sede</option>
                <?php while ($s = $sedes->fetch_assoc()): ?>
                    <option value="<?= $s['ID_sede'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
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
            $sql = "SELECT f.ID_funcion, f.ID_pelicula, f.ID_sala, f.fecha_hora, f.precio_base,
                        p.titulo, sa.numero_sala, sa.ID_sede, s.nombre AS sede
                    FROM Funcion f
                    JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula
                    JOIN Sala sa ON f.ID_sala = sa.ID_sala
                    JOIN Sede s ON sa.ID_sede = s.ID_sede
                    ORDER BY f.fecha_hora DESC";
            $result = $conn->query($sql);

            // Obtener todas las sedes y salas
            $all_sedes = $conn->query("SELECT ID_sede, nombre FROM Sede");
            $sedes_arr = [];
            while ($row = $all_sedes->fetch_assoc()) {
                $sedes_arr[$row['ID_sede']] = $row['nombre'];
            }

            $all_salas = $conn->query("SELECT ID_sala, numero_sala, tipo_sala, ID_sede FROM Sala");
            $salas_arr = [];
            while ($row = $all_salas->fetch_assoc()) {
                $salas_arr[$row['ID_sede']][] = $row;
            }

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <form method="POST">
                        <td><?= htmlspecialchars($row['titulo']) ?></td>
                        <td>
                            <select name="id_sede" class="sede-select" required onchange="actualizarSalas(this)">
                                <?php foreach ($sedes_arr as $id => $nombre): ?>
                                    <option value="<?= $id ?>" <?= $id == $row['ID_sede'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select name="id_sala" class="sala-select" required data-sede="<?= $row['ID_sede'] ?>">
                                <?php
                                foreach ($salas_arr[$row['ID_sede']] as $sala):
                                    $selected = $sala['ID_sala'] == $row['ID_sala'] ? 'selected' : '';
                                    echo "<option value='{$sala['ID_sala']}' $selected>Sala {$sala['numero_sala']} ({$sala['tipo_sala']})</option>";
                                endforeach;
                                ?>
                            </select>
                        </td>
                        <td>
                            <input type="datetime-local" name="fecha_hora" value="<?= date("Y-m-d\TH:i", strtotime($row["fecha_hora"])) ?>" required>
                        </td>
                        <td>
                            <input type="number" name="precio" step="0.10" value="<?= $row["precio_base"] ?>" required style="width:80px;">
                        </td>
                        <td>
                            <input type="hidden" name="id_funcion" value="<?= $row["ID_funcion"] ?>">
                            <div style="display: flex; gap: 5px; margin-top: 10px;">
                                <button type="submit" name="update_funcion" 
                                        style="flex: 1; background-color: #3498db; color: white; border: none; padding: 6px 0; border-radius: 4px; cursor: pointer;font-size: 12px;">
                                    Actualizar
                                </button>
                                <a href="funciones.php?delete_id=<?= $row["ID_funcion"] ?>" 
                                    onclick="return confirm('¿Estás seguro de eliminar esta función?');"
                                    style="flex: 1; background-color: #e74c3c; color: white; text-align: center; padding: 6px 0; text-decoration: none; border-radius: 4px;font-size: 12px;">
                                    Eliminar
                                </a>
                            </div>
                        </td>
                    </form>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6">No hay funciones programadas.</td></tr>
            <?php endif; ?>
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

<script>
const salasPorSede = <?= json_encode($salas_arr) ?>;

function actualizarSalas(selectSede) {
    const sedeId = selectSede.value;
    const row = selectSede.closest("tr");
    const salaSelect = row.querySelector(".sala-select");

    salaSelect.innerHTML = "";

    if (!salasPorSede[sedeId]) {
        salaSelect.innerHTML = "<option value=''>No hay salas</option>";
        return;
    }

    salasPorSede[sedeId].forEach(sala => {
        const option = document.createElement("option");
        option.value = sala.ID_sala;
        option.text = `Sala ${sala.numero_sala} (${sala.tipo_sala})`;
        salaSelect.appendChild(option);
    });
}
</script>


<?php
require_once '../includes/footer.php';
?>