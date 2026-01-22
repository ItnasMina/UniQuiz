<?php
// UQ Lead Dev: registro.php
// Objetivo: Manejar el formulario de registro de nuevo usuario.

include 'includes/funciones.php';

$mensaje_error = ''; // Variable para mostrar errores al usuario, si los hubiera.

// Aquí iría la lógica PHP para procesar el formulario de registro
// 1. Sanitización y validación de inputs.
// 2. Usar password_hash() para la contraseña antes de guardar.
// 3. Insertar en la tabla 'usuarios'.
// ...
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta en UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/x-icon">
</head>
<body class="login-body">

    <header class="main-header small-header">
        <div class="logo">
            <a href="index.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo UniQuiz" class="logo-image">
            </a>
        </div>
    </header>

    <main class="login-container">
        <div class="login-box">
            <h2>Crear Cuenta Nueva</h2>

            <?php if (!empty($mensaje_error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($mensaje_error); ?></p>
            <?php endif; ?>

            <form action="registro.php" method="POST" class="login-form">
                
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