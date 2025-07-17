<?php
// Archivo: actualizar_perfil.php
// Página para que el usuario actualice sus datos personales.

require_once 'includes/public_header.php';

// Seguridad: Redirigir si el usuario no ha iniciado sesión.
if (!isset($_SESSION['user_dni'])) {
    header("Location: login.php");
    exit();
}

$dni_cliente = $_SESSION['user_dni'];
$error_message = '';
$success_message = '';

// --- LÓGICA PARA PROCESAR LA ACTUALIZACIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Construir la consulta de actualización
    $sql_parts = [];
    $params = [];
    $types = "";

    // Añadir campos básicos a la consulta
    $sql_parts[] = "nombre = ?";
    $params[] = $nombre;
    $types .= "s";

    $sql_parts[] = "apellidos = ?";
    $params[] = $apellidos;
    $types .= "s";

    $sql_parts[] = "email = ?";
    $params[] = $email;
    $types .= "s";

    // Validar y añadir la contraseña si se ha proporcionado
    if (!empty($password)) {
        if ($password === $password_confirm) {
            $sql_parts[] = "password = ?";
            // En un proyecto real, aquí se usaría password_hash()
            $params[] = $password;
            $types .= "s";
        } else {
            $error_message = "Las contraseñas no coinciden.";
        }
    }

    // Solo proceder si no hay errores
    if (empty($error_message)) {
        try {
            $sql = "UPDATE Cliente SET " . implode(", ", $sql_parts) . " WHERE DNI = ?";
            $types .= "s";
            $params[] = $dni_cliente;

            $stmt = $conn->prepare($sql);
            // Vincular los parámetros dinámicamente
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $success_message = "¡Tus datos han sido actualizados correctamente!";
                // Actualizar el nombre en la sesión para que se refleje en el header
                $_SESSION['user_nombre'] = $nombre;
            } else {
                $error_message = "Error al actualizar los datos. Es posible que el email ya esté en uso.";
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
             // Error 1062 es para entradas duplicadas (email único)
            if($e->getCode() == 1062){
                $error_message = "El correo electrónico ingresado ya pertenece a otra cuenta.";
            } else {
                $error_message = "Error de base de datos: " . $e->getMessage();
            }
        }
    }
}

// --- OBTENER DATOS ACTUALES DEL USUARIO PARA MOSTRAR EN EL FORMULARIO ---
$sql_user = "SELECT nombre, apellidos, email FROM Cliente WHERE DNI = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $dni_cliente);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

?>
<div class="form-page-container">
    <h2>Actualizar mis Datos</h2>
    <p>Modifica tu información personal. Tu DNI no puede ser cambiado.</p>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form class="styled-form" action="actualizar_perfil.php" method="POST">
        <div class="form-group">
            <label for="dni">DNI (No se puede cambiar):</label>
            <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($dni_cliente); ?>" readonly disabled>
        </div>
        <div class="form-group">
            <label for="nombre">Nombres:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user_data['nombre']); ?>" required>
        </div>
        <div class="form-group">
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($user_data['apellidos']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
        </div>

        <hr>
        <p><strong>Cambiar Contraseña (opcional)</strong><br>Deja estos campos en blanco si no deseas cambiar tu contraseña.</p>

        <div class="form-group">
            <label for="password">Nueva Contraseña:</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar Nueva Contraseña:</label>
            <input type="password" id="password_confirm" name="password_confirm">
        </div>

        <button type="submit" class="btn">Guardar Cambios</button>
        <a href="perfil_socio.php" class="btn-secondary">Volver al Perfil</a>
    </form>
</div>

<?php
require_once 'includes/footer.php';
?>
