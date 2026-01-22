<?php
// UQ Lead Dev: login.php
// Objetivo: Manejar el formulario de inicio de sesión y enlazar al registro.

// Incluimos funciones esenciales (aunque no se usan para la maquetación)
include '../include/funciones.php';
// Nota: session_start() se incluiría aquí si estuviéramos procesando el login.

$mensaje_error = ''; // Variable para mostrar errores al usuario, si los hubiera.

// Aquí iría la lógica PHP para procesar el formulario (POST)
// ...
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión en UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/x-icon">
</head>
<body class="login-body">

    <header class="main-header small-header">
        <div class="logo">
            <a href="../index.php" class="logo-link">UniQuiz (UQ)</a>
        </div>
    </header>

    <main class="login-container">
        <div class="login-box">
            <h2>Inicio de Sesión</h2>

            <?php if (!empty($mensaje_error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($mensaje_error); ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                
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