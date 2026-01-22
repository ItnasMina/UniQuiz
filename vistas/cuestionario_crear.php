<?php
// UQ Lead Dev: cuestionario_crear.php
// Objetivo: Formulario visual para iniciar un nuevo cuestionario.

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$nombre_usuario = $_SESSION['nombre_usuario'];

// Capturar errores si venimos rebotados del controlador
$error = $_SESSION['error_crear'] ?? '';
unset($_SESSION['error_crear']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuestionario | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="dashboard-body">

    <header class="main-header private-header header-with-tabs">
        <div class="logo">
            <a href="dashboard.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo UniQuiz" class="logo-image">
            </a>
        </div>
        
        <nav class="user-nav">
            <span class="user-welcome">Hola, <?php echo htmlspecialchars($nombre_usuario); ?></span>
             <a href="perfil.php" class="nav-icon" title="Mi Perfil">
                <?php if(!empty($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] != 'default_user.png'): ?>
                    <img src="../almacen/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" class="icon-img user-icon" style="border-radius: 50%;">
                <?php else: ?>
                    <img src="../assets/IconoPerfil.png" alt="Mi Perfil" class="icon-img user-icon">
                <?php endif; ?>
                Mi Perfil 
            </a>
            <a href="../controladores/usuario_logout.php" class="nav-icon btn-logout">
                <img src="../assets/IconoSalir.png" alt="Salir" class="icon-img user-icon"> Salir
            </a>
        </nav>
    </header>
    
    <main class="dashboard-content edit-container">
        
        <div class="content-header">
            <a href="dashboard.php" class="btn btn-back">&larr; Volver al Dashboard</a>
            <h1>Crear Nuevo Cuestionario</h1>
        </div>

        <?php if ($error): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <section class="form-card">
            <h3>Detalles Básicos</h3>
            <p style="margin-bottom: 20px; color: #666;">Define la configuración general. Podrás añadir las preguntas en el siguiente paso.</p>
            
            <form action="../controladores/cuestionario_guardar.php" method="POST" class="form-standard">
                
                <div class="form-group">
                    <label for="titulo">Título del Cuestionario *</label>
                    <input type="text" id="titulo" name="titulo" required placeholder="Ej: Matemáticas I - Tema 4">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción (Opcional)</label>
                    <textarea id="descripcion" name="descripcion" rows="3" placeholder="Breve explicación de qué va el test..."></textarea>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="random" name="random">
                    <label for="random">Activar modo preguntas aleatorias</label>
                    <input type="number" id="num_random" name="num_random" placeholder="Cant." min="1" max="50" style="width: 80px !important; margin-left: 10px;">
                </div>
                
                <div class="form-group radio-group">
                    <label style="margin-right: 15px; font-weight: bold;">Visibilidad:</label>
                    
                    <input type="radio" id="publico" name="acceso" value="1" checked>
                    <label for="publico" style="margin-right: 20px;">Público (Todos lo ven)</label>
                    
                    <input type="radio" id="privado" name="acceso" value="0">
                    <label for="privado">Privado (Solo tú)</label>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 10px; font-size: 1.1rem;">
                    Crear y Añadir Preguntas &rarr;
                </button>
            </form>
        </section>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>
</body>
</html>