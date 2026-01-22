<?php
// UQ Lead Dev: index.php (LANDING REDISEÑADA)
session_start();
// Redirigir si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header("Location: vistas/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a UniQuiz</title>
    <link rel="stylesheet" href="estilos/estilos.css">
    <link rel="icon" href="assets/LogoUQ.png" type="image/png">
</head>
<body class="login-body"> <main class="login-container" style="max-width: 1000px;">
        
        <div class="hero">
            <div style="margin-bottom: 20px;">
                <img src="assets/LogoUQ-w&b.png" alt="UniQuiz Logo" style="height: 80px; filter: invert(0); opacity: 0.8;"> </div>

            <h1 style="font-size: 2.5rem; margin-bottom: 15px; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                Domina tus Asignaturas
            </h1>
            
            <p style="font-size: 1.1rem; color: #718096; max-width: 600px; margin: 0 auto 30px;">
                La plataforma inteligente para crear, compartir y practicar exámenes universitarios con un diseño que te encantará.
            </p>

            <div style="display: flex; gap: 15px; justify-content: center; margin-bottom: 50px;">
                <a href="vistas/registro.php" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">🚀 Empezar Gratis</a>
                <a href="vistas/login.php" class="btn btn-secondary" style="padding: 15px 40px; font-size: 1.1rem;">Iniciar Sesión</a>
            </div>

            <div class="features-grid">
                <div class="feature-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">⚡</div>
                    <h3>Rápido</h3>
                    <p>Crea tests en segundos con nuestra interfaz intuitiva y moderna.</p>
                </div>
                <div class="feature-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🌍</div>
                    <h3>Comunidad</h3>
                    <p>Accede a cuestionarios públicos creados por otros estudiantes.</p>
                </div>
                <div class="feature-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">📈</div>
                    <h3>Progreso</h3>
                    <p>Visualiza tus resultados y mejora nota asignatura a asignatura.</p>
                </div>
            </div>
        </div>

    </main>
    
    <footer style="text-align: center; color: rgba(255,255,255,0.8); padding: 20px; font-size: 0.85rem;">
        &copy; 2026 UniQuiz. Diseñado para el éxito.
    </footer>

</body>
</html>