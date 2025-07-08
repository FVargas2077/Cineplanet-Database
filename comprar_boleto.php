<?php
// Archivo: comprar_boleto.php
// Página para seleccionar asientos y procesar la compra.

require_once 'includes/public_header.php';

// --- 1. SEGURIDAD Y VALIDACIÓN INICIAL ---

// Seguridad: Redirigir si el usuario no ha iniciado sesión.
if (!isset($_SESSION['user_dni'])) {
    // Guardamos la página a la que quería ir para redirigirlo después del login.
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Validar que se haya proporcionado un ID de función válido.
if (!isset($_GET['id_funcion']) || !is_numeric($_GET['id_funcion'])) {
    echo "<div class='container message error'>Función no válida.</div>";
    require_once 'includes/footer.php';
    exit();
}
$id_funcion = (int)$_GET['id_funcion'];
$dni_cliente = $_SESSION['user_dni'];

// --- 2. PROCESAMIENTO DE LA COMPRA (CUANDO SE ENVÍA EL FORMULARIO) ---

$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_seats'])) {
    $selected_seats_json = $_POST['selected_seats'];
    $selected_seats = json_decode($selected_seats_json, true);

    if (empty($selected_seats) || !is_array($selected_seats)) {
        $error_message = "Error: No has seleccionado ningún asiento.";
    } else {
        // Iniciar una transacción para asegurar la integridad de la compra.
        $conn->begin_transaction();
        try {
            // Obtener el precio de la función para el cálculo del total.
            $sql_precio = "SELECT precio_base FROM Funcion WHERE ID_funcion = ?";
            $stmt_precio = $conn->prepare($sql_precio);
            $stmt_precio->bind_param("i", $id_funcion);
            $stmt_precio->execute();
            $precio_unitario = $stmt_precio->get_result()->fetch_assoc()['precio_base'];
            $total_compra = count($selected_seats) * $precio_unitario;

            // a) Insertar en la tabla Compra.
            $sql_compra = "INSERT INTO Compra (DNI_cliente, total, metodo_pago) VALUES (?, ?, 'Tarjeta')";
            $stmt_compra = $conn->prepare($sql_compra);
            $stmt_compra->bind_param("sd", $dni_cliente, $total_compra);
            $stmt_compra->execute();
            $id_compra_nueva = $conn->insert_id; // Obtener el ID de la compra recién creada.

            // b) Insertar cada boleto en la tabla Boleto.
            $sql_boleto = "INSERT INTO Boleto (ID_compra, ID_funcion, fila, numero_asiento, precio_pagado) VALUES (?, ?, ?, ?, ?)";
            $stmt_boleto = $conn->prepare($sql_boleto);

            foreach ($selected_seats as $seat) {
                // El formato del asiento es 'F-N' (ej. 'A-5').
                list($fila, $numero) = explode('-', $seat);
                $stmt_boleto->bind_param("iisid", $id_compra_nueva, $id_funcion, $fila, $numero, $precio_unitario);
                $stmt_boleto->execute();
            }

            // Si todo fue bien, confirmar la transacción.
            $conn->commit();

            // Redirigir a la página de éxito.
            header("Location: compra_exitosa.php?id_compra=" . $id_compra_nueva);
            exit();

        } catch (mysqli_sql_exception $exception) {
            // Si algo falla (ej. asiento ya ocupado), revertir todo.
            $conn->rollback();
            // El error 1062 es para entradas duplicadas (nuestra restricción de asiento único).
            if ($conn->errno == 1062) {
                $error_message = "Lo sentimos, uno de los asientos que seleccionaste acaba de ser ocupado por otra persona. Por favor, elige otros asientos.";
            } else {
                $error_message = "Ha ocurrido un error al procesar tu compra. Por favor, inténtalo de nuevo. Error: " . $exception->getMessage();
            }
        }
    }
}


// --- 3. OBTENER DATOS PARA MOSTRAR LA PÁGINA ---

// Consultar detalles de la función y la película.
$sql_info = "SELECT p.titulo, f.fecha_hora, s.nombre AS nombre_sede, sa.numero_sala, sa.capacidad
             FROM Funcion f
             JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula
             JOIN Sala sa ON f.ID_sala = sa.ID_sala
             JOIN Sede s ON sa.ID_sede = s.ID_sede
             WHERE f.ID_funcion = ?";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $id_funcion);
$stmt_info->execute();
$result_info = $stmt_info->get_result();
if ($result_info->num_rows == 0) {
    echo "<div class='container message error'>Función no encontrada.</div>";
    require_once 'includes/footer.php';
    exit();
}
$funcion_info = $result_info->fetch_assoc();
$capacidad_sala = $funcion_info['capacidad'];

// Consultar los asientos ya ocupados para esta función.
$sql_ocupados = "SELECT fila, numero_asiento FROM Boleto WHERE ID_funcion = ?";
$stmt_ocupados = $conn->prepare($sql_ocupados);
$stmt_ocupados->bind_param("i", $id_funcion);
$stmt_ocupados->execute();
$result_ocupados = $stmt_ocupados->get_result();
$asientos_ocupados = [];
while ($row = $result_ocupados->fetch_assoc()) {
    $asientos_ocupados[] = $row['fila'] . '-' . $row['numero_asiento'];
}

?>

<div class="seat-selection-container">
    <div class="movie-summary">
        <h2><?php echo htmlspecialchars($funcion_info['titulo']); ?></h2>
        <p>
            <strong>Sede:</strong> <?php echo htmlspecialchars($funcion_info['nombre_sede']); ?> | 
            <strong>Sala:</strong> <?php echo $funcion_info['numero_sala']; ?>
        </p>
        <p>
            <strong>Fecha y Hora:</strong> <?php echo date("d/m/Y h:i A", strtotime($funcion_info['fecha_hora'])); ?>
        </p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="seat-map-container">
        <div class="screen">PANTALLA</div>
        <div class="seat-map">
            <?php
            // Lógica simple para generar el mapa de asientos (10 asientos por fila).
            $asientos_por_fila = 10;
            $total_filas = ceil($capacidad_sala / $asientos_por_fila);
            $asiento_actual = 0;

            for ($i = 0; $i < $total_filas; $i++) {
                $fila_letra = chr(65 + $i); // A, B, C, ...
                echo "<div class='seat-row'>";
                for ($j = 1; $j <= $asientos_por_fila; $j++) {
                    if (++$asiento_actual > $capacidad_sala) break;

                    $id_asiento = $fila_letra . '-' . $j;
                    $clase_asiento = 'seat';
                    if (in_array($id_asiento, $asientos_ocupados)) {
                        $clase_asiento .= ' occupied';
                    }
                    echo "<div class='$clase_asiento' data-seat-id='$id_asiento'>$j</div>";
                }
                echo "<div class='row-label'>$fila_letra</div>";
                echo "</div>";
            }
            ?>
        </div>
        <div class="seat-legend">
            <div class="legend-item"><div class="seat"></div> Disponible</div>
            <div class="legend-item"><div class="seat selected"></div> Seleccionado</div>
            <div class="legend-item"><div class="seat occupied"></div> Ocupado</div>
        </div>
    </div>

    <div class="purchase-summary">
        <h3>Resumen de tu Compra</h3>
        <p><strong>Asientos seleccionados:</strong> <span id="seats-list">Ninguno</span></p>
        <p><strong>Total a pagar:</strong> S/ <span id="total-price">0.00</span></p>
        <form action="comprar_boleto.php?id_funcion=<?php echo $id_funcion; ?>" method="POST" id="purchase-form">
            <input type="hidden" name="selected_seats" id="selected-seats-input">
            <button type="submit" class="btn" id="btn-comprar" disabled>Completar Compra</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatMap = document.querySelector('.seat-map');
    const seatsListSpan = document.getElementById('seats-list');
    const totalPriceSpan = document.getElementById('total-price');
    const selectedSeatsInput = document.getElementById('selected-seats-input');
    const purchaseButton = document.getElementById('btn-comprar');
    
    const precioPorBoleto = <?php echo $funcion_info['precio_base'] ?? 0; ?>;
    let asientosSeleccionados = [];

    seatMap.addEventListener('click', function(e) {
        const seat = e.target;
        if (seat.classList.contains('seat') && !seat.classList.contains('occupied')) {
            const seatId = seat.dataset.seatId;
            
            // Alternar selección
            seat.classList.toggle('selected');
            
            if (seat.classList.contains('selected')) {
                // Añadir a la lista
                asientosSeleccionados.push(seatId);
            } else {
                // Quitar de la lista
                asientosSeleccionados = asientosSeleccionados.filter(s => s !== seatId);
            }
            
            actualizarResumen();
        }
    });

    function actualizarResumen() {
        // Ordenar asientos para una mejor visualización (ej. A-1, A-2, B-5)
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
        
        const total = asientosSeleccionados.length * precioPorBoleto;
        totalPriceSpan.textContent = total.toFixed(2);
        
        // Actualizar el input oculto del formulario
        selectedSeatsInput.value = JSON.stringify(asientosSeleccionados);
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
