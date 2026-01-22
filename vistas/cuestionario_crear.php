<?php
// UQ Lead Dev: vistas/cuestionario_crear.php
// Objetivo: Crear cuestionario con diseño limpio y opciones simplificadas.

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Cuestionario | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo">
            <a href="dashboard.php"><img src="../assets/LogoUQ-w&b.png" alt="Logo" class="logo-image"></a>
        </div>
        <div class="user-nav">
            <a href="dashboard.php" class="btn btn-secondary">← Cancelar y Volver</a>
        </div>
    </header>
    
    <main class="dashboard-content" style="max-width: 800px;">
        
        <div class="form-card">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: var(--primary); margin-bottom: 10px;">Crear Nuevo Cuestionario</h2>
                <p style="color: var(--text-light);">
                    Configura los detalles básicos. En el siguiente paso podrás añadir las preguntas.
                </p>
            </div>

            <form action="../controladores/cuestionario_crear.php" method="POST">
                
                <div class="form-group">
                    <label for="titulo">Título del Cuestionario *</label>
                    <input type="text" id="titulo" name="titulo" 
                           placeholder="Ej: Matemáticas I - Tema 4" required autofocus>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (Opcional)</label>
                    <textarea id="descripcion" name="descripcion" rows="4" 
                              placeholder="Breve explicación del contenido del test..."></textarea>
                </div>

                <div class="form-group">
                    <label style="display:block; margin-bottom:10px;">Visibilidad</label>
                    <div class="radio-group-container">
                        
                        <div class="radio-option">
                            <input type="radio" id="vis_publica" name="visibilidad" value="publica" checked>
                            <label for="vis_publica">
                                <span style="font-size: 1.8rem; display: block; margin-bottom: 5px;">🌍</span>
                                Público
                                <span style="display: block; font-size: 0.75rem; font-weight: 400; text-transform: none; margin-top: 5px; color: #888;">
                                    Visible para toda la comunidad
                                </span>
                            </label>
                        </div>

                        <div class="radio-option">
                            <input type="radio" id="vis_privada" name="visibilidad" value="privada">
                            <label for="vis_privada">
                                <span style="font-size: 1.8rem; display: block; margin-bottom: 5px;">🔒</span>
                                Privado
                                <span style="display: block; font-size: 0.75rem; font-weight: 400; text-transform: none; margin-top: 5px; color: #888;">
                                    Solo tú podrás verlo y editarlo
                                </span>
                            </label>
                        </div>

                    </div>
                </div>

                <div style="margin-top: 40px;">
                    <button type="submit" class="btn btn-primary btn-full-width">
                        Siguiente: Añadir Preguntas →
                    </button>
                </div>

            </form>
        </div>

    </main>

    <footer class="main-footer"><p>&copy; 2026 UniQuiz.</p></footer>

</body>
</html>