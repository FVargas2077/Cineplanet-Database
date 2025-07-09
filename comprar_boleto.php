<?php
// Archivo: comprar_boleto.php (Actualizado con selección de pago)
require_once 'includes/public_header.php';

// --- 1. SEGURIDAD Y VALIDACIÓN INICIAL ---
if (!isset($_SESSION['user_dni'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id_funcion']) || !is_numeric($_GET['id_funcion'])) {
    echo "<div class='container message error'>Función no válida.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_funcion = (int)$_GET['id_funcion'];
$dni_cliente = $_SESSION['user_dni'];

// --- 2. PROCESAMIENTO DE LA COMPRA ---
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_seats'])) {
    $selected_seats = json_decode($_POST['selected_seats'], true);
    // MEJORA: Obtener el método de pago del formulario
    $metodo_pago = $_POST['metodo_pago'];

    if (empty($selected_seats) || !is_array($selected_seats)) {
        $error_message = "Error: No has seleccionado ningún asiento.";
    } else {
        $conn->begin_transaction();
        try {
            $sql_precio = "SELECT precio_base FROM Funcion WHERE ID_funcion = ?";
            $stmt_precio = $conn->prepare($sql_precio);
            $stmt_precio->bind_param("i", $id_funcion);
            $stmt_precio->execute();
            $precio_unitario = $stmt_precio->get_result()->fetch_assoc()['precio_base'];
            $total_compra = count($selected_seats) * $precio_unitario;

            // MEJORA: Se incluye el método de pago en la inserción.
            $sql_compra = "INSERT INTO Compra (DNI_cliente, total, metodo_pago) VALUES (?, ?, ?)";
            $stmt_compra = $conn->prepare($sql_compra);
            $stmt_compra->bind_param("sds", $dni_cliente, $total_compra, $metodo_pago);
            $stmt_compra->execute();
            $id_compra_nueva = $conn->insert_id;

            $sql_boleto = "INSERT INTO Boleto (ID_compra, ID_funcion, fila, numero_asiento, precio_pagado) VALUES (?, ?, ?, ?, ?)";
            $stmt_boleto = $conn->prepare($sql_boleto);
            foreach ($selected_seats as $seat) {
                list($fila, $numero) = explode('-', $seat);
                $stmt_boleto->bind_param("iisid", $id_compra_nueva, $id_funcion, $fila, $numero, $precio_unitario);
                $stmt_boleto->execute();
            }

            $conn->commit();
            header("Location: compra_exitosa.php?id_compra=" . $id_compra_nueva);
            exit();

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            if ($conn->errno == 1062) {
                $error_message = "Lo sentimos, uno de los asientos que seleccionaste acaba de ser ocupado. Por favor, elige otros asientos.";
            } else {
                $error_message = "Ha ocurrido un error al procesar tu compra: " . $exception->getMessage();
            }
        }
    }
}

// --- 3. OBTENER DATOS PARA MOSTRAR LA PÁGINA (sin cambios) ---
$sql_info = "SELECT p.titulo, f.fecha_hora, f.precio_base, s.nombre AS nombre_sede, sa.numero_sala, sa.capacidad FROM Funcion f JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula JOIN Sala sa ON f.ID_sala = sa.ID_sala JOIN Sede s ON sa.ID_sede = s.ID_sede WHERE f.ID_funcion = ?";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $id_funcion);
$stmt_info->execute();
$result_info = $stmt_info->get_result();
$funcion_info = $result_info->fetch_assoc();
$capacidad_sala = $funcion_info['capacidad'];
$sql_ocupados = "SELECT fila, numero_asiento FROM Boleto WHERE ID_funcion = ?";
$stmt_ocupados = $conn->prepare($sql_ocupados);
$stmt_ocupados->bind_param("i", $id_funcion);
$stmt_ocupados->execute();
$result_ocupados = $stmt_ocupados->get_result();
$asientos_ocupados = [];
while ($row = $result_ocupados->fetch_assoc()) { $asientos_ocupados[] = $row['fila'] . '-' . $row['numero_asiento']; }
?>

<!-- HTML y JavaScript (con la adición del select de método de pago) -->
<div class="seat-selection-container">
    <div class="movie-summary">
        <h2><?php echo htmlspecialchars($funcion_info['titulo']); ?></h2>
        <p><strong>Sede:</strong> <?php echo htmlspecialchars($funcion_info['nombre_sede']); ?> | <strong>Sala:</strong> <?php echo $funcion_info['numero_sala']; ?></p>
        <p><strong>Fecha y Hora:</strong> <?php echo date("d/m/Y h:i A", strtotime($funcion_info['fecha_hora'])); ?></p>
    </div>
    <?php if (!empty($error_message)): ?><div class="message error"><?php echo $error_message; ?></div><?php endif; ?>
    <div class="seat-map-container">
        <!-- ... (código del mapa de asientos sin cambios) ... -->
    </div>
    <div class="purchase-summary">
        <h3>Resumen de tu Compra</h3>
        <form action="comprar_boleto.php?id_funcion=<?php echo $id_funcion; ?>" method="POST" id="purchase-form">
            <p><strong>Asientos seleccionados:</strong> <span id="seats-list">Ninguno</span></p>
            
            <!-- MEJORA: Selección de método de pago -->
            <div class="form-group">
                <label for="metodo_pago">Método de Pago:</label>
                <select name="metodo_pago" id="metodo_pago">
                    <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                    <option value="Yape">Yape</option>
                    <option value="Efectivo">Efectivo (en boletería)</option>
                </select>
            </div>

            <p><strong>Total a pagar:</strong> S/ <span id="total-price">0.00</span></p>
            <input type="hidden" name="selected_seats" id="selected-seats-input">
            <button type="submit" class="btn" id="btn-comprar" disabled>Completar Compra</button>
        </form>
    </div>
</div>
<!-- ... (código JavaScript sin cambios) ... -->
<?php require_once 'includes/footer.php'; ?>
