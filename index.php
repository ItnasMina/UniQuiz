<?php
// UQ Lead Dev: index.php
// Objetivo: Página de aterrizaje (Landing Page) para UniQuiz.
// Nota: No se requiere session_start() ya que es una página pública.
// Incluimos funciones esenciales (aunque estarán vacías por ahora)
include 'includes/funciones.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniQuiz - Plataforma de Cuestionarios para Universitarios</title>
    <link rel="stylesheet" href="estilos/estilos.css">  
    <link rel="icon" href="/assets/LogoUQ.png" type="image/x-icon">
</head>
<body>

    <header class="main-header">
        <div class="logo">
            <a href="index.php">
                <img src="assets/LogoUQ-w&b.png" alt="Logo de UniQuiz" class="logo-image">
            </a>
        </div>
        <nav class="main-nav">
            <a href="dashboard.php" class="active">Inicio</a>
            <a href="vistas/login.php" class="btn btn-primary">Iniciar Sesión</a>
            <a href="vistas/registro.php" class="btn btn-secondary">Registrarse</a>
        </nav>
    </header>

    <main class="landing-main">
        <section class="hero">
            <h2>Domina tus Asignaturas con UniQuiz</h2>
            <p class="tagline">La plataforma definitiva de creación y gestión de cuestionarios diseñada para la vida universitaria.</p>
            
            <div class="features-grid">
                <div class="feature-item">
                    <h3>💡 Crea y Personaliza</h3>
                    <p>Diseña exámenes de práctica con preguntas de opción múltiple, verdadero/falso o texto abierto.</p>
                </div>
                <div class="feature-item">
                    <h3>📚 Acceso Rápido</h3>
                    <p>Comparte tus conocimientos o accede a cuestionarios públicos creados por otros compañeros.</p>
                </div>
                <div class="feature-item">
                    <h3>📈 Haz Seguimiento</h3>
                    <p>Gestiona tus propios cuestionarios y edítalos fácilmente con nuestra interfaz intuitiva.</p>
                </div>
            </div>

            <a href="registro.php" class="btn btn-call-to-action">¡Empieza Gratis Ahora!</a>
        </section>

        <section class="how-it-works">
            <h3>¿Cómo funciona UQ?</h3>
            <p>En solo 3 pasos, estarás listo para estudiar:</p>
            <ul>
                <li>**Regístrate:** Crea tu cuenta universitaria en segundos.</li>
                <li>**Crea/Busca:** Diseña tu propio cuestionario o busca entre los disponibles públicamente.</li>
                <li>**Estudia:** Completa el quiz y prepárate para el éxito académico.</li>
            </ul>
        </section>
    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz. Todos los derechos reservados.</p>
    </footer>

</body>
</html>