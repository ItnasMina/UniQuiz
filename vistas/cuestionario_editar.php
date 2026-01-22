<?php
// UQ Lead Dev: cuestionario_editar.php
// Objetivo: Editar cuestionario. FIX: El contador de preguntas ahora se calcula siempre.

session_start();
require_once '../controladores/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$cuestionario_id = $_GET['id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];

if (!$cuestionario_id) {
    header("Location: dashboard.php");
    exit;
}

try {
    // 1. Obtener Cuestionario
    $stmt = $pdo->prepare("SELECT * FROM cuestionarios WHERE id = :id AND usuario_id = :uid");
    $stmt->execute(['id' => $cuestionario_id, 'uid' => $usuario_id]);
    $cuestionario = $stmt->fetch();

    if (!$cuestionario) {
        $_SESSION['mensaje'] = "No tienes permiso o no existe.";
        header("Location: dashboard.php");
        exit;
    }

    // 2. FIX: Contar preguntas SIEMPRE (independiente del tab activo)
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM preguntas WHERE cuestionario_id = :cid");
    $stmtCount->execute(['cid' => $cuestionario_id]);
    $num_preguntas = $stmtCount->fetchColumn(); 

    // 3. Cargar listado SOLO si estamos en la pestaña preguntas
    $sub_tab_activo = $_GET['subtab'] ?? 'ajustes';
    $preguntas = []; // Array vacío por defecto
    
    if ($sub_tab_activo == 'preguntas') {
        $stmtP = $pdo->prepare("SELECT * FROM preguntas WHERE cuestionario_id = :cid ORDER BY orden ASC, id ASC");
        $stmtP->execute(['cid' => $cuestionario_id]);
        $preguntas = $stmtP->fetchAll();
    }

} catch (PDOException $e) {
    die("Error sistema: " . $e->getMessage());
}

$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error_editar'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error_editar']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editando: <?php echo htmlspecialchars($cuestionario['titulo']); ?> | UniQuiz</title>
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
            <a href="dashboard.php?tab=mis_cuestionarios" class="btn btn-back">
                &larr; Volver a Mis Cuestionarios
            </a>
            <h1>Editando: <?php echo htmlspecialchars($cuestionario['titulo']); ?></h1>
        </div>

        <?php if ($mensaje): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <nav class="sub-tab-navigation">
            <a href="cuestionario_editar.php?id=<?php echo $cuestionario_id; ?>&subtab=ajustes" 
               class="sub-tab-item <?php echo ($sub_tab_activo == 'ajustes') ? 'active' : ''; ?>">
                Ajustes
            </a>
            <a href="cuestionario_editar.php?id=<?php echo $cuestionario_id; ?>&subtab=preguntas" 
               class="sub-tab-item <?php echo ($sub_tab_activo == 'preguntas') ? 'active' : ''; ?>">
                Preguntas (<?php echo $num_preguntas; ?>)
            </a>
        </nav>

        <section class="sub-tab-content">
            
            <?php if ($sub_tab_activo == 'ajustes'): ?>
                <h3>Configuración del Cuestionario</h3>
                <form action="../controladores/cuestionario_actualizar.php" method="POST" class="form-standard">
                    <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario['id']; ?>">
                    
                    <div class="form-group">
                        <label for="titulo">Nombre (Título)</label>
                        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($cuestionario['titulo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($cuestionario['descripcion']); ?></textarea>
                    </div>

                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="random" name="random" <?php echo ($cuestionario['es_aleatorio']) ? 'checked' : ''; ?>>
                        <label for="random">Preguntas Aleatorias</label>
                        <input type="number" id="num_random" name="num_random" 
                               value="<?php echo $cuestionario['num_preguntas_aleatorias']; ?>" 
                               min="1" max="50" style="width: 100px; margin-left: 10px;">
                    </div>
                    
                    <div class="form-group radio-group">
                        <label>Nivel de Acceso:</label>
                        <input type="radio" id="publico" name="acceso" value="1" <?php echo ($cuestionario['es_publico']) ? 'checked' : ''; ?>>
                        <label for="publico">Público</label>
                        <input type="radio" id="privado" name="acceso" value="0" style="margin-left: 15px;" <?php echo (!$cuestionario['es_publico']) ? 'checked' : ''; ?>>
                        <label for="privado">Privado</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Ajustes</button>
                </form>

            <?php elseif ($sub_tab_activo == 'preguntas'): ?>
                <div class="header-listado">
                    <h3>Listado de Preguntas</h3>
                    <a href="pregunta_crear.php?cuestionario_id=<?php echo $cuestionario_id; ?>" class="btn btn-create"> 
                        + Añadir Pregunta
                    </a>
                </div>
                
                <?php if (empty($preguntas)): ?>
                    <p class="empty-list-message">Este cuestionario aún no tiene preguntas. ¡Añade la primera!</p>
                <?php else: ?>
                    <ul class="question-list">
                        <?php foreach ($preguntas as $pregunta): ?>
                            <li class="question-item">
                                <div class="question-text">
                                    <strong><?php echo htmlspecialchars($pregunta['enunciado']); ?></strong>
                                    <?php if($pregunta['imagen']): ?>
                                        <span style="font-size:0.8em; color: #386DBD;">[IMG]</span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted">Tipo: <?php echo ($pregunta['tipo']=='opcion_multiple') ? 'Test' : 'V/F'; ?></small>
                                </div>
                                <div class="action-buttons">
                                    <a href="pregunta_editar.php?id=<?php echo $pregunta['id']; ?>" class="btn-action" title="Editar"> 
                                        <img src="../assets/IconoEditar.png" alt="Editar" class="icon-img crud-icon">
                                    </a>
                                    
                                    <form action="../controladores/pregunta_borrar.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="pregunta_id" value="<?php echo $pregunta['id']; ?>">
                                        <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario_id; ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Borrar" onclick="return confirm('¿Borrar esta pregunta?');">
                                            <img src="../assets/IconoPapelera.png" alt="Borrar" class="icon-img crud-icon">
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
            <?php endif; ?>
        </section>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html>