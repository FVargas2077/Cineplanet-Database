<?php
// Archivo: registro.php (en la raíz del proyecto)
// Página para que nuevos usuarios se registren como clientes.

require_once 'includes/public_header.php'; // Usamos el header público

$error_message = '';
$success_message = '';

// Verificamos si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recogemos y limpiamos los datos del formulario
    $dni = $conn->real_escape_string($_POST['dni']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; // La contraseña
    $password_confirm = $_POST['password_confirm'];

    // --- Validaciones ---
    if ($password !== $password_confirm) {
        $error_message = "Las contraseñas no coinciden.";
    } elseif (strlen($dni) != 8 || !ctype_digit($dni)) {
        $error_message = "El DNI debe tener 8 dígitos numéricos.";
    } else {
        // Verificar si el DNI o el Email ya existen en la base de datos
        $sql_check = "SELECT DNI FROM Cliente WHERE DNI = ? OR email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ss", $dni, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = "El DNI o el correo electrónico ya están registrados.";
        } else {
            // Todo correcto, procedemos a insertar el nuevo cliente
            // NOTA: En un proyecto real, la contraseña debe ser hasheada.
            // Ejemplo: $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO Cliente (DNI, nombre, apellidos, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            // Para un proyecto real, reemplazar $password por $hashed_password
            $stmt_insert->bind_param("sssss", $dni, $nombre, $apellidos, $email, $password);

            if ($stmt_insert->execute()) {
                $success_message = "¡Registro exitoso! Ahora puedes <a href='login.php'>iniciar sesión</a>.";
            } else {
                $error_message = "Error al registrar el usuario. Por favor, inténtalo de nuevo.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<div class="form-page-container">
    <h2>Crea tu Cuenta</h2>
    <p>Regístrate para disfrutar de todos los beneficios de Cineplanet.</p>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php else: // Ocultar el formulario si el registro fue exitoso ?>
    <form class="styled-form" action="registro.php" method="POST">
        <div class="form-group">
            <label for="dni">DNI:</label>
            <input type="text" id="dni" name="dni" required maxlength="8">
        </div>
        <div class="form-group">
            <label for="nombre">Nombres:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required>
        </div>
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar Contraseña:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn">Registrarme</button>
    </form>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
