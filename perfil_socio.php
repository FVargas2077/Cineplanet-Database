<?php
// Archivo: perfil_socio.php
// Muestra la información del perfil del usuario logueado y su historial de compras.

require_once 'includes/public_header.php';

// --- Seguridad: Verificar si el usuario ha iniciado sesión ---
if (!isset($_SESSION['user_dni'])) {
    header("Location: login.php");
    exit();
}

$user_dni = $_SESSION['user_dni'];

// --- Consultar la información del cliente y del socio (si existe) ---
$sql = "SELECT c.DNI, c.nombre, c.apellidos, c.email, c.fecha_registro,
               s.numero_socio, s.puntos_acumulados
        FROM Cliente c
        LEFT JOIN Socio s ON c.DNI = s.DNI
        WHERE c.DNI = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_dni);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}
$stmt->close();

// --- COMPRAS DE ENTRADAS ---
$sql_boletos = "
    SELECT c.ID_compra, c.fecha_compra, c.total, c.metodo_pago,
           p.titulo AS pelicula, sede.nombre AS sede, f.fecha_hora,
           GROUP_CONCAT(CONCAT(b.fila, '-', b.numero_asiento) ORDER BY b.fila, b.numero_asiento SEPARATOR ', ') AS asientos,
           sa.numero_sala AS sala,
           c.total AS monto_total
    FROM Compra c
    JOIN Boleto b ON c.ID_compra = b.ID_compra
    JOIN Funcion f ON b.ID_funcion = f.ID_funcion
    JOIN Pelicula p ON f.ID_pelicula = p.ID_pelicula
    JOIN Sala sa ON f.ID_sala = sa.ID_sala
    JOIN Sede sede ON sa.ID_sede = sede.ID_sede
    WHERE c.DNI_cliente = ?
    GROUP BY c.ID_compra
    ORDER BY c.fecha_compra DESC
";

$stmt_boletos = $conn->prepare($sql_boletos);
$stmt_boletos->bind_param("s", $user_dni);
$stmt_boletos->execute();
$res_boletos = $stmt_boletos->get_result();

// --- COMPRAS EN DULCERÍA ---
$sql_dulceria = "
    SELECT c.ID_compra, c.fecha_compra, c.total, c.metodo_pago
    FROM Compra c
    WHERE c.DNI_cliente = ? AND c.ID_compra NOT IN (
        SELECT DISTINCT ID_compra FROM Boleto
    )
    ORDER BY c.fecha_compra DESC
";
$stmt_dulceria = $conn->prepare($sql_dulceria);
$stmt_dulceria->bind_param("s", $user_dni);
$stmt_dulceria->execute();
$res_dulceria = $stmt_dulceria->get_result();
?>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .profile-card {
        background-color: #ffffff;
        padding: 25px;
        margin-bottom: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
    }
    
    .socio-card {
        border-left-color: #28a745;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .socio-prompt {
        border-left-color: #ffc107;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    }
    
    .profile-actions {
        margin-top: 20px;
    }
    
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    
    .btn:hover {
        background-color: #0056b3;
    }
    
    .puntos {
        font-size: 18px;
        color: #28a745;
        font-weight: bold;
    }
    
    .section {
        background-color: #ffffff;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 8px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    table th, table td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    
    table th {
        background-color: #007bff;
        color: white;
    }
    
    .btn-detalle {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    
    .btn-detalle:hover {
        background-color: #218838;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
    }
    
    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 20px;
        width: 90%;
        max-width: 600px;
        border-radius: 8px;
        position: relative;
        color: black;
    }
    
    .modal-close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
    }
    
    .no-data {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 20px;
    }
</style>

<div class="profile-container">
    <h2>Mi Perfil</h2>
    
    <!-- Información Personal -->
    <div class="profile-card">
        <h3>Información Personal</h3>
        <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellidos']); ?></p>
        <p><strong>DNI:</strong> <?php echo htmlspecialchars($user_data['DNI']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
        <p><strong>Miembro desde:</strong> <?php echo date("d/m/Y", strtotime($user_data['fecha_registro'])); ?></p>
        
        <div class="profile-actions">
            <a href="actualizar_perfil.php" class="btn">Actualizar mis Datos</a>
        </div>
    </div>

    <!-- Estado de Socio -->
    <?php if ($user_data['numero_socio']): ?>
        <div class="profile-card socio-card">
            <h3>Beneficios de Socio Cineplanet</h3>
            <p><strong>Número de Socio:</strong> <?php echo htmlspecialchars($user_data['numero_socio']); ?></p>
            <p class="puntos"><strong>Puntos Acumulados:</strong> <?php echo $user_data['puntos_acumulados']; ?> puntos</p>
            <p>¡Sigue comprando para acumular más puntos y canjearlos por entradas y productos!</p>
        </div>
    <?php else: ?>
        <div class="profile-card socio-prompt">
            <h3>¡Conviértete en Socio Cineplanet!</h3>
            <p>Aún no eres socio. ¡Únete al programa para acumular puntos y acceder a promociones exclusivas!</p>
            <a href="#" class="btn">Quiero ser Socio</a>
        </div>
    <?php endif; ?>

    <!-- Historial de Compras de Entradas -->
    <div class="section">
        <h2>Compras de Entradas</h2>
        <?php if ($res_boletos->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Película</th>
                        <th>Sede</th>
                        <th>Fecha Función</th>
                        <th>Fecha Compra</th>
                        <th>Método de Pago</th>
                        <th>Total</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res_boletos->fetch_assoc()): 
                        $datos = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['pelicula']) ?></td>
                            <td><?= htmlspecialchars($row['sede']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_hora']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_compra']) ?></td>
                            <td><?= htmlspecialchars($row['metodo_pago']) ?></td>
                            <td>S/ <?= number_format($row['total'], 2) ?></td>
                            <td><button class="btn-detalle" onclick="mostrarDetalles('<?= $datos ?>')">Más detalles</button></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>No has comprado entradas aún.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Historial de Compras en Dulcería -->
    <div class="section">
        <h2>Compras en Dulcería</h2>
        <?php if ($res_dulceria->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha Compra</th>
                        <th>Método de Pago</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res_dulceria->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['fecha_compra']) ?></td>
                            <td><?= htmlspecialchars($row['metodo_pago']) ?></td>
                            <td>S/ <?= number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>No has realizado compras en dulcería.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para detalles de compra -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="cerrarModal()">&times;</span>
        <h2>Detalles de la compra</h2>
        <div id="modal-body">
            <p><strong>Película:</strong> <span id="detallePelicula"></span></p>
            <p><strong>Sede:</strong> <span id="detalleSede"></span></p>
            <p><strong>Fecha y Hora:</strong> <span id="detalleFechaHora"></span></p>
            <p><strong>Sala:</strong> <span id="detalleSala"></span></p>
            <p><strong>Asientos:</strong> <span id="detalleAsientos"></span></p>
            <p><strong>Monto Total:</strong> S/ <span id="detalleMontoTotal"></span></p>
            <p><strong>ID de Compra:</strong> #<span id="detalleID"></span></p>
        </div>
    </div>
</div>

<script>
function mostrarDetalles(datosStr) {
    const data = JSON.parse(datosStr);

    document.getElementById('detallePelicula').textContent = data.pelicula;
    document.getElementById('detalleSede').textContent = data.sede;
    document.getElementById('detalleFechaHora').textContent = data.fecha_hora;
    document.getElementById('detalleSala').textContent = data.sala;
    document.getElementById('detalleAsientos').textContent = data.asientos;
    document.getElementById('detalleMontoTotal').textContent = parseFloat(data.monto_total).toFixed(2);
    document.getElementById('detalleID').textContent = data.ID_compra;

    document.getElementById('modal').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('modal');
    if (event.target === modal) {
        cerrarModal();
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>