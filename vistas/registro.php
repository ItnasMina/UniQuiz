<?php
// UQ Lead Dev: registro.php (CORREGIDO: Añadido campo confirmar contraseña)
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Gestión de errores de sesión
$error = $_SESSION['error_registro'] ?? '';
unset($_SESSION['error_registro']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="login-body">

    <main class="login-container">
        
        <div class="login-box" style="max-width: 500px;"> 
            <div style="text-align: center; margin-bottom: 30px;">
                <a href="../index.php">
                    <img src="../assets/LogoUQ-w&b.png" alt="UniQuiz" style="height: 60px;">
                </a>
                <h2 style="margin-top: 15px; color: #386DBD; font-weight: 700;">Crear Cuenta Nueva</h2>
                <p style="color: #6c757d; font-size: 0.9rem;">Únete a la comunidad universitaria</p>
            </div>

            <?php if ($error): ?>
                <div class="alert" style="background-color: #ffe3e3; color: #c92a2a; border: 1px solid #ffc9c9; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="../controladores/usuario_registro.php" method="POST">
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Tu nombre y apellido" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Universitario</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@universidad.edu" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Repite tu contraseña" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full-width">
                    Registrarme
                </button>

                <div style="text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                    <p style="font-size: 0.9rem; margin-bottom: 10px; color: #666;">¿Ya tienes una cuenta?</p>
                    <a href="login.php" class="btn btn-secondary btn-full-width">
                        Volver a Iniciar Sesión
                    </a>
                </div>

            </form>
        </div>

    </main>

    <footer style="text-align: center; color: rgba(255,255,255,0.6); padding: 20px; font-size: 0.8rem;">
        &copy; 2026 UniQuiz.
    </footer>

</body>
</html>