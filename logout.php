<?php
// Archivo: logout.php (ubicado en la carpeta raíz)
// Script para cerrar la sesión del usuario.

// Siempre se debe iniciar la sesión antes de poder destruirla.
session_start();

// 1. Unset all of the session variables.
$_SESSION = array();

// 2. Destroy the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finally, destroy the session.
session_destroy();

// 4. Redirigir al usuario a la página de inicio de sesión.
header("Location: login.php");
exit();
?>
