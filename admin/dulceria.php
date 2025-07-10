<?php
// Página para gestionar (Añadir/Eliminar) productos de dulcería.

require_once '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_producto'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);

    $sql = "INSERT INTO Dulceria (nombre, categoria, precio_unitario, stock) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $nombre, $categoria, $precio, $stock);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Producto añadido correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error al añadir el producto: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

if (isset($_GET['delete_id'])) {
    $id_a_eliminar = (int)$_GET['delete_id'];

    $sql = "DELETE FROM Dulceria WHERE ID_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_a_eliminar);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Producto eliminado correctamente.</p>";
    } else {
        echo "<p style='color: red;'>Error al eliminar el producto: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

?>

<div class="form-container">
    <h2>Añadir Nuevo Producto de Dulcería</h2>
    <form action="dulceria.php" method="POST">
        <label for="nombre">Nombre del Producto:</label>
        <input type="text" id="nombre" name="nombre" required>

        <label for="categoria">Categoría:</label>
        <select id="categoria" name="categoria" required>
            <option value="Dulces">Dulces</option>
            <option value="Bebidas">Bebidas</option>
            <option value="Salado">Salado</option>
            <option value="Combos">Combos</option>
        </select>

        <label for="precio">Precio (S/):</label>
        <input type="number" step="0.01" id="precio" name="precio" required>

        <label for="stock">Stock Inicial:</label>
        <input type="number" id="stock" name="stock" required>

        <button type="submit" name="add_producto" class="btn">Añadir Producto</button>
    </form>
</div>

<div>
    <h2>Productos Actuales en Dulcería</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT ID_producto, nombre, categoria, precio_unitario, stock FROM Dulceria ORDER BY nombre";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["ID_producto"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["categoria"]) . "</td>";
                    echo "<td>S/ " . number_format($row["precio_unitario"], 2) . "</td>";
                    echo "<td>" . $row["stock"] . "</td>";
                    echo "<td><a href='dulceria.php?delete_id=" . $row["ID_producto"] . "' class='delete-btn' onclick='return confirm(\"¿Estás seguro de que quieres eliminar este producto?\");'>Eliminar</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No hay productos de dulcería registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>
