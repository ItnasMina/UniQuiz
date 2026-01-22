<?php
// UQ Lead Dev: controladores/usuario_logout.php
// Objetivo: Destruir la sesión segura y redirigir a la landing page.

session_start(); // Necesario para saber qué sesión destruir

// 1. Vaciar el array $_SESSION
$_SESSION = [];

// 2. Si se desea destruir la sesión completamente, borramos también la cookie de sesión.
// Esto es una buena práctica de seguridad.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión
session_destroy();

// 4. Redirigir al inicio público
header("Location: ../index.php");
exit;
?>