<?php
// Archivo: perfil_socio.php (en la raíz del proyecto)
// Muestra la información del perfil del usuario logueado.

require_once 'includes/public_header.php';

// --- Seguridad: Verificar si el usuario ha iniciado sesión ---
if (!isset($_SESSION['user_dni'])) {
    // Si no hay sesión, redirigir al login.
    header("Location: login.php");
    exit();
}

// Obtenemos el DNI del usuario desde la sesión.
$user_dni = $_SESSION['user_dni'];

// --- Consultar la información del cliente y del socio (si existe) ---
// Usamos un LEFT JOIN para obtener los datos del socio solo si el cliente es uno.
$sql = "SELECT c.DNI, c.nombre, c.apellidos, c.email, c.fecha_registro,
               s.numero_socio, s.puntos_acumulados
        FROM Cliente c
        LEFT JOIN Socio s ON c.DNI = s.DNI
        WHERE c.DNI = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_dni);
$stmt->execute();
$result = $stmt->get_result();

// Verificamos si se encontró al usuario (debería, si la sesión es válida).
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    // Si no se encuentra, algo está mal. Destruimos la sesión y redirigimos.
    session_destroy();
    header("Location: login.php");
    exit();
}
$stmt->close();
?>

<div class="profile-container">
    <h2>Mi Perfil</h2>
    <div class="profile-card">
        <h3>Información Personal</h3>
        <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($user_data['nombre'] . ' ' . $user_data['apellidos']); ?></p>
        <p><strong>DNI:</strong> <?php echo htmlspecialchars($user_data['DNI']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
        <p><strong>Miembro desde:</strong> <?php echo date("d/m/Y", strtotime($user_data['fecha_registro'])); ?></p>
    </div>

    <?php if ($user_data['numero_socio']): // Si el usuario es un socio (el campo no es NULL) ?>
        <div class="profile-card socio-card">
            <h3>Beneficios de Socio Cineplanet</h3>
            <p><strong>Número de Socio:</strong> <?php echo htmlspecialchars($user_data['numero_socio']); ?></p>
            <p class="puntos"><strong>Puntos Acumulados:</strong> <?php echo $user_data['puntos_acumulados']; ?> puntos</p>
            <p>¡Sigue comprando para acumular más puntos y canjearlos por entradas y productos!</p>
        </div>
    <?php else: // Si el usuario no es socio ?>
        <div class="profile-card socio-prompt">
            <h3>¡Conviértete en Socio Cineplanet!</h3>
            <p>Aún no eres socio. ¡Únete al programa para acumular puntos y acceder a promociones exclusivas!</p>
            <a href="#" class="btn">Quiero ser Socio</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
