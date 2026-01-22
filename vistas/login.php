<?php
// UQ Lead Dev: login.php
// Objetivo: Formulario de inicio de sesión. Muestra errores si el controlador los devuelve.

session_start();

// Si ya está logueado, mandar al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Recoger mensajes de error flash y borrarlos después de leerlos
$mensaje_error = '';
if (isset($_SESSION['error_login'])) {
    $mensaje_error = $_SESSION['error_login'];
    unset($_SESSION['error_login']);
}

// No necesitamos incluir funciones.php para la maqueta, pero sí para el header si lo usa dinámico
// include '../include/funciones.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/x-icon">
</head>
<body class="login-body">

    <header class="main-header small-header">
        <div class="logo">
            <a href="../index.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo UniQuiz" class="logo-image">
            </a>
        </div>
    </header>

    <main class="login-container">
        <div class="login-box">
            <h2>Inicio de Sesión</h2>

            <?php if (!empty($mensaje_error)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <form action="../controladores/usuario_login.php" method="POST" class="login-form">
                
                <div class="form-group">
                    <label for="email">Email Universitario</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="ejemplo@universidad.edu"
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Ingresa tu contraseña"
                           autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn btn-full-width btn-primary">
                    Acceder a UniQuiz
                </button>
            </form>

            <div class="separator"></div>

            <p class="new-user-text">¿Eres nuevo en UniQuiz?</p>
            <a href="registro.php" class="btn btn-full-width btn-secondary">
                Crear Usuario Nuevo
            </a>
            
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html>