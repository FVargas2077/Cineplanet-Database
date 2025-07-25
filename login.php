<?php
// Página para que los usuarios (clientes y admin) inicien sesión.
require_once 'config/database.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $sql = "SELECT DNI, nombre, password, es_admin FROM Cliente WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            $_SESSION['user_dni'] = $user['DNI'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['es_admin'] = (bool)$user['es_admin'];
            if ($_SESSION['es_admin']) {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();

        } else {
            $error_message = "La contraseña es incorrecta. Por favor, inténtalo de nuevo.";
        }
    } else {
        $error_message = "No se encontró ningún usuario con ese correo electrónico.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Cineplanet</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #e9ebee;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h1 {
            color: #00529b;
            margin-bottom: 20px;
        }
        .login-container .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .login-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .login-container .btn {
            width: 100%;
            padding: 12px;
            background-color: #00529b;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .login-container .btn:hover {
            background-color: #003d73;
        }
        .error-msg {
            color: #d9534f;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .register-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .register-link a {
            color: #00529b;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Bienvenido a Cineplanet</h1>
        
        <?php if (!empty($error_message)): ?>
            <p class="error-msg"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        <p class="register-link">
            ¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a>
        </p>
    </div>
</body>
</html>
