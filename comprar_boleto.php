<?php
// Archivo: comprar_boleto.php
// Versión mejorada con descuento automático del 15% para socios.

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

// --- MEJORA: VERIFICAR SI EL USUARIO ES SOCIO ---
$es_socio = false;
$sql_check_socio = "SELECT DNI FROM Socio WHERE DNI = ?";
$stmt_check_socio = $conn->prepare($sql_check_socio);
$stmt_check_socio->bind_param("s", $dni_cliente);
$stmt_check_socio->execute();
if ($stmt_check_socio->get_result()->num_rows > 0) {
    $es_socio = true;
}
$stmt_check_socio->close();


// --- 2. PROCESAMIENTO DE LA COMPRA (ACTUALIZADO) ---
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_seats'])) {
    $selected_seats = json_decode($_POST['selected_seats'], true);
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
            
            // MEJORA: Calcular subtotal, descuento y total
            $subtotal = count($selected_seats) * $precio_unitario;
            $descuento_aplicado = 0;
            if ($es_socio) {
                $descuento_aplicado = $subtotal * 0.15;
            }
            $total_compra = $subtotal - $descuento_aplicado;

            // MEJORA: Se inserta el descuento y el total final en la compra
            $sql_compra = "INSERT INTO Compra (DNI_cliente, total, metodo_pago, descuento_aplicado) VALUES (?, ?, ?, ?)";
            $stmt_compra = $conn->prepare($sql_compra);
            $stmt_compra->bind_param("sdsd", $dni_cliente, $total_compra, $metodo_pago, $descuento_aplicado);
            $stmt_compra->execute();
            $id_compra_nueva = $conn->insert_id;

            // La inserción de boletos no cambia
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
$sql_info = "SELECT p.titulo, f.fecha_hora, f.precio_base, s.nombre AS nombre_sede, sa.numero_sala FROM Funcion f JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula JOIN Sala sa ON f.ID_sala = sa.ID_sala JOIN Sede s ON sa.ID_sede = s.ID_sede WHERE f.ID_funcion = ?";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $id_funcion);
$stmt_info->execute();
$result_info = $stmt_info->get_result();
$funcion_info = $result_info->fetch_assoc();

$sql_ocupados = "SELECT fila, numero_asiento FROM Boleto WHERE ID_funcion = ?";
$stmt_ocupados = $conn->prepare($sql_ocupados);
$stmt_ocupados->bind_param("i", $id_funcion);
$stmt_ocupados->execute();
$result_ocupados = $stmt_ocupados->get_result();
$asientos_ocupados = [];
while ($row = $result_ocupados->fetch_assoc()) { $asientos_ocupados[] = $row['fila'] . '-' . $row['numero_asiento']; }
?>

<div class="seat-selection-container">
    <div class="movie-summary">
        <h2><?php echo htmlspecialchars($funcion_info['titulo']); ?></h2>
        <p><strong>Sede:</strong> <?php echo htmlspecialchars($funcion_info['nombre_sede']); ?> | <strong>Sala:</strong> <?php echo $funcion_info['numero_sala']; ?></p>
        <p><strong>Fecha y Hora:</strong> <?php echo date("d/m/Y h:i A", strtotime($funcion_info['fecha_hora'])); ?></p>
    </div>
    <?php if (!empty($error_message)): ?><div class="message error"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="seat-map-container">
        <div class="screen">PANTALLA</div>
        <div class="seat-map">
            <?php
            $filas = range('A', 'J');
            $columnas = range(1, 15);

            foreach ($filas as $fila) {
                echo "<div class='seat-row'>";
                echo "<div class='row-label'>$fila</div>";
                foreach ($columnas as $columna) {
                    $id_asiento = "$fila-$columna";
                    $esta_ocupado = in_array($id_asiento, $asientos_ocupados);
                    
                    echo "<div class='seat-checkbox-wrapper'>";
                    echo "<input type='checkbox' class='seat-checkbox' id='seat-$id_asiento' data-seat-id='$id_asiento' " . ($esta_ocupado ? 'disabled' : '') . ">";
                    echo "<label for='seat-$id_asiento' class='seat-label'>$columna</label>";
                    echo "</div>";
                }
                echo "</div>";
            }
            ?>
        </div>
        <div class="seat-legend">
            <div class="legend-item"><div class="seat-label"></div> Disponible</div>
            <div class="legend-item"><div class="seat-label selected"></div> Seleccionado</div>
            <div class="legend-item"><div class="seat-label occupied"></div> Ocupado</div>
        </div>
    </div>

    <div class="purchase-summary">
        <h3>Resumen de tu Compra</h3>
        <form action="comprar_boleto.php?id_funcion=<?php echo $id_funcion; ?>" method="POST" id="purchase-form">
            <p><strong>Asientos seleccionados:</strong> <span id="seats-list">Ninguno</span></p>
            
            <!-- MEJORA: Mostrar subtotal y descuento -->
            <p><strong>Subtotal:</strong> S/ <span id="subtotal-price">0.00</span></p>
            <?php if ($es_socio): ?>
                <p class="discount-info"><strong>Descuento de Socio (15%):</strong> - S/ <span id="discount-amount">0.00</span></p>
            <?php endif; ?>
            <hr>
            
            <div class="form-group">
                <label for="metodo_pago">Método de Pago:</label>
                <select name="metodo_pago" id="metodo_pago">
                    <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                    <option value="Yape">Yape</option>
                    <option value="Efectivo">Efectivo (en boletería)</option>
                </select>
            </div>

            <p class="final-total"><strong>TOTAL A PAGAR:</strong> S/ <span id="total-price">0.00</span></p>
            <input type="hidden" name="selected_seats" id="selected-seats-input">
            <button type="submit" class="btn" id="btn-comprar" disabled>Completar Compra</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatMap = document.querySelector('.seat-map');
    const seatsListSpan = document.getElementById('seats-list');
    const subtotalPriceSpan = document.getElementById('subtotal-price');
    const discountAmountSpan = document.getElementById('discount-amount');
    const totalPriceSpan = document.getElementById('total-price');
    const selectedSeatsInput = document.getElementById('selected-seats-input');
    const purchaseButton = document.getElementById('btn-comprar');
    
    // MEJORA: Pasamos el estado de socio y el precio a JavaScript
    const esSocio = <?php echo json_encode($es_socio); ?>;
    const precioPorBoleto = <?php echo $funcion_info['precio_base'] ?? 0; ?>;
    
    seatMap.addEventListener('change', function(e) {
        if (e.target.classList.contains('seat-checkbox')) {
            actualizarResumen();
        }
    });

    function actualizarResumen() {
        const asientosSeleccionados = [];
        const checkedSeats = seatMap.querySelectorAll('.seat-checkbox:checked');
        
        checkedSeats.forEach(checkbox => {
            asientosSeleccionados.push(checkbox.dataset.seatId);
        });

        asientosSeleccionados.sort((a, b) => {
            const [filaA, numA] = a.split('-');
            const [filaB, numB] = b.split('-');
            if (filaA < filaB) return -1;
            if (filaA > filaB) return 1;
            return parseInt(numA) - parseInt(numB);
        });

        if (asientosSeleccionados.length === 0) {
            seatsListSpan.textContent = 'Ninguno';
            purchaseButton.disabled = true;
        } else {
            seatsListSpan.textContent = asientosSeleccionados.join(', ');
            purchaseButton.disabled = false;
        }
        
        // MEJORA: Lógica de cálculo de precios en JavaScript
        const subtotal = asientosSeleccionados.length * precioPorBoleto;
        let descuento = 0;
        if (esSocio) {
            descuento = subtotal * 0.15;
        }
        const total = subtotal - descuento;

        subtotalPriceSpan.textContent = subtotal.toFixed(2);
        if (esSocio && discountAmountSpan) {
            discountAmountSpan.textContent = descuento.toFixed(2);
        }
        totalPriceSpan.textContent = total.toFixed(2);
        
        selectedSeatsInput.value = JSON.stringify(asientosSeleccionados);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
