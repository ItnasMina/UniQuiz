<?php
// UQ Lead Dev: cuestionario_editar.php
// Objetivo: Interfaz para editar los ajustes y preguntas de un cuestionario existente.

session_start();

// Simulamos el ID del cuestionario y el nombre del usuario
$cuestionario_id = $_GET['id'] ?? null;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario UQ';
$cuestionario_titulo = "Título del Cuestionario #{$cuestionario_id}"; // Simulación

// Definimos el Tab interno activo
$sub_tab_activo = $_GET['subtab'] ?? 'ajustes'; 

include 'includes/funciones.php';
// if (!$cuestionario_id || !usuario_tiene_acceso($cuestionario_id)) { 
//     header("Location: dashboard.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editando: <?php echo htmlspecialchars($cuestionario_titulo); ?> | UniQuiz</title>
    
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ-w&b.png" type="image/png"> 
</head>
<body class="dashboard-body">

    <header class="main-header private-header header-with-tabs">
        <div class="logo">
            <a href="dashboard.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo UniQuiz con texto" class="logo-image">
            </a>
        </div>
        
        <nav class="user-nav">
            <span class="user-welcome">Hola, <?php echo htmlspecialchars($nombre_usuario); ?></span>
            
            <a href="usuario_perfil.php" class="nav-icon" title="Mi Perfil"> <img src="../assets/IconoPerfil.png" alt="Mi Perfil" class="icon-img user-icon">
                Mi Perfil 
            </a>
            
            <a href="logout.php" class="nav-icon btn-logout" title="Cerrar Sesión">
                <img src="../assets/IconoSalir.png" alt="Cerrar Sesión" class="icon-img user-icon">
                Salir 
            </a>
        </nav>
    </header>
    
    <main class="dashboard-content edit-container">
        
        <div class="content-header">
            <a href="dashboard.php?tab=mis_cuestionarios" class="btn btn-back">
                &larr; Volver a Mis Cuestionarios
            </a>
            <h1>Editando Cuestionario: <?php echo htmlspecialchars($cuestionario_titulo); ?></h1>
        </div>

        <nav class="sub-tab-navigation">
            <a href="cuestionario_editar.php?id=<?php echo $cuestionario_id; ?>&subtab=ajustes" 
               class="sub-tab-item <?php echo ($sub_tab_activo == 'ajustes') ? 'active' : ''; ?>">
                Ajustes
            </a>
            <a href="cuestionario_editar.php?id=<?php echo $cuestionario_id; ?>&subtab=preguntas" 
               class="sub-tab-item <?php echo ($sub_tab_activo == 'preguntas') ? 'active' : ''; ?>">
                Preguntas
            </a>
        </nav>

        <section class="sub-tab-content">
            <?php if ($sub_tab_activo == 'ajustes'): ?>
                <h3>Configuración del Cuestionario</h3>
                <form class="form-standard">
                    <div class="form-group">
                        <label for="titulo">Nombre (Título)</label>
                        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($cuestionario_titulo); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4">Breve descripción del cuestionario...</textarea>
                    </div>

                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="random" name="random">
                        <label for="random">Preguntas Aleatorias</label>
                        <input type="number" id="num_random" name="num_random" placeholder="¿Cuántas?" min="1" max="50" style="width: 100px; margin-left: 10px;">
                    </div>
                    
                    <div class="form-group radio-group">
                        <label>Nivel de Acceso:</label>
                        <input type="radio" id="publico" name="acceso" value="1" checked>
                        <label for="publico">Público</label>
                        <input type="radio" id="privado" name="acceso" value="0" style="margin-left: 15px;">
                        <label for="privado">Privado</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Ajustes</button>
                </form>

            <?php elseif ($sub_tab_activo == 'preguntas'): ?>
                <div class="header-listado">
                    <h3>Listado de Preguntas</h3>
                    <a href="pregunta_crear.php?cuestionario_id=<?php echo $cuestionario_id; ?>" class="btn btn-create"> + Añadir Pregunta
                    </a>
                </div>
                
                <ul class="question-list">
                    <li class="question-item">
                        <p class="question-text">1. ¿Cuál es el puerto estándar de MySQL?</p>
                        <div class="action-buttons">
                            <a href="pregunta_editar.php?id=1" class="btn-action" title="Editar Pregunta"> <img src="../assets/IconoEditar.png" alt="Editar" class="icon-img crud-icon">
                            </a>
                            <button class="btn-action btn-delete" title="Borrar Pregunta">
                                <img src="../assets/IconoPapelera.png" alt="Borrar" class="icon-img crud-icon">
                            </button>
                        </div>
                    </li>
                    <li class="question-item">
                        <p class="question-text">2. PHP es un lenguaje de programación compilado (V/F).</p>
                        <div class="action-buttons">
                            <a href="pregunta_editar.php?id=2" class="btn-action" title="Editar Pregunta"> <img src="../assets/IconoEditar.png" alt="Editar" class="icon-img crud-icon">
                            </a>
                            <button class="btn-action btn-delete" title="Borrar Pregunta">
                                <img src="../assets/IconoPapelera.png" alt="Borrar" class="icon-img crud-icon">
                            </button>
                        </div>
                    </li>
                </ul>
                
            <?php endif; ?>
        </section>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html>