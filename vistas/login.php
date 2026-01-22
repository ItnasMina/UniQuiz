<?php
// UQ Lead Dev: login.php (DISEÑO BLUE IDENTITY - FINAL)
// Objetivo: Diseño unificado con registro.php, logo integrado y botones jerarquizados.

session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Recoger mensajes de error si los hay (y limpiarlos después)
$error = $_SESSION['error_login'] ?? '';
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="login-body">

    <main class="login-container">
        
        <div class="login-box">
            
            <div style="text-align: center; margin-bottom: 30px;">
                <a href="../index.php">
                    <img src="../assets/LogoUQ-w&b.png" alt="UniQuiz" style="height: 60px;">
                </a>
                <h2 style="margin-top: 15px; color: #386DBD; font-weight: 700;">Inicio de Sesión</h2>
                <p style="color: #6c757d; font-size: 0.9rem;">Accede a tu cuenta universitaria</p>
            </div>

            <?php if ($error): ?>
                <div style="background-color: #ffe3e3; color: #c92a2a; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; border: 1px solid #ffc9c9;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="../controladores/usuario_login.php" method="POST">
                
                <div class="form-group">
                    <label for="email">Email Universitario</label>
                    <input type="email" id="email" name="email" placeholder="ejemplo@universidad.edu" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full-width">
                    Acceder a UniQuiz
                </button>

                <div style="text-align: center; margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px;">
                    <p style="font-size: 0.9rem; margin-bottom: 10px; color: #666;">¿Eres nuevo en UniQuiz?</p>
                    <a href="registro.php" class="btn btn-secondary btn-full-width">
                        Crear Usuario Nuevo
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