<?php
// UQ Lead Dev: registro.php
// Objetivo: Vista del formulario de registro. Conecta con el controlador para crear cuenta.

session_start();

// Si ya está logueado, lo mandamos al dashboard directamente
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Gestión de errores (Flash Messages)
$mensaje_error = '';
if (isset($_SESSION['error_registro'])) {
    $mensaje_error = $_SESSION['error_registro'];
    unset($_SESSION['error_registro']); // Limpiamos el error para que no salga siempre
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | UniQuiz</title>
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
            <h2>Crear Cuenta Nueva</h2>

            <?php if (!empty($mensaje_error)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb; text-align: left;">
                    <strong>¡Error!</strong> <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <form action="../controladores/usuario_registro.php" method="POST" class="login-form">
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" required 
                           placeholder="Tu nombre y apellido"
                           autocomplete="name">
                </div>

                <div class="form-group">
                    <label for="email">Email Universitario</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="ejemplo@universidad.edu"
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Mínimo 8 caracteres"
                           autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" required 
                           placeholder="Repite la contraseña">
                </div>
                
                <button type="submit" class="btn btn-full-width btn-secondary">
                    Registrarme
                </button>
            </form>

            <div class="separator"></div>

            <p class="new-user-text">¿Ya tienes una cuenta?</p>
            <a href="login.php" class="btn btn-full-width btn-primary">
                Volver a Iniciar Sesión
            </a>
            
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html>