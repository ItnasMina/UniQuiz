<?php
// UQ Lead Dev: dashboard.php
// Objetivo: Panel principal. Muestra cuestionarios REALES de la base de datos.

session_start();

// 1. Seguridad: Verificar acceso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../controladores/conexion.php';

// Datos del usuario logueado
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$tab_activo = $_GET['tab'] ?? 'mis_cuestionarios';

// Variables para la vista
$cuestionarios = [];
$titulo_seccion = "";

try {
    if ($tab_activo == 'mis_cuestionarios') {
        $titulo_seccion = "Mis Cuestionarios Creados";
        
        // CONSULTA A: Cuestionarios del usuario actual
        $sql = "SELECT * FROM cuestionarios 
                WHERE usuario_id = :uid 
                ORDER BY fecha_creacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $usuario_id]);
        $cuestionarios = $stmt->fetchAll();

    } elseif ($tab_activo == 'ver_cuestionarios') {
        $titulo_seccion = "Explorar Cuestionarios Públicos";
        
        // CONSULTA B: Cuestionarios públicos (JOIN para saber el autor)
        // Excluimos los propios para que no salgan duplicados si también son públicos
        $sql = "SELECT c.*, u.nombre as autor 
                FROM cuestionarios c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.es_publico = 1 AND c.usuario_id != :uid
                ORDER BY c.fecha_creacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $usuario_id]);
        $cuestionarios = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error_db = "Error al cargar cuestionarios: " . $e->getMessage();
}

// Mensajes Flash (Feedback de acciones de borrar/crear)
$mensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UniQuiz</title>
    
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
        
        <nav class="main-tabs-container">
            <a href="dashboard.php?tab=mis_cuestionarios" 
               class="tab-item <?php echo ($tab_activo == 'mis_cuestionarios') ? 'active' : ''; ?>">
                Mis Cuestionarios
            </a>
            <a href="dashboard.php?tab=ver_cuestionarios" 
               class="tab-item <?php echo ($tab_activo == 'ver_cuestionarios') ? 'active' : ''; ?>">
                Comunidad (Públicos)
            </a>
        </nav>

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
            
            <a href="../controladores/usuario_logout.php" class="nav-icon btn-logout" title="Cerrar Sesión">
                <img src="../assets/IconoSalir.png" alt="Cerrar Sesión" class="icon-img user-icon">
                Salir 
            </a>
        </nav>
    </header>
    
    <main class="dashboard-content">
        
        <?php if (!empty($mensaje)): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_db)): ?>
            <div style="color: red;"><?php echo $error_db; ?></div>
        <?php endif; ?>

        <div class="header-listado">
            <h2><?php echo $titulo_seccion; ?></h2>
            
            <?php if ($tab_activo == 'mis_cuestionarios'): ?>
                <a href="cuestionario_crear.php" class="btn btn-create">
                    + Crear Nuevo
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($cuestionarios)): ?>
            <p class="empty-list-message">
                <?php echo ($tab_activo == 'mis_cuestionarios') 
                    ? "Aún no has creado ningún cuestionario. ¡Empieza ahora!" 
                    : "No hay cuestionarios públicos disponibles de otros usuarios."; ?>
            </p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <?php if ($tab_activo == 'ver_cuestionarios'): ?>
                            <th>Autor</th>
                        <?php endif; ?>
                        <th>Creado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cuestionarios as $cuestionario): ?>
                        <tr>
                            <td>
                                <?php if ($tab_activo == 'mis_cuestionarios'): ?>
                                    <a href="cuestionario_editar.php?id=<?php echo $cuestionario['id']; ?>">
                                        <strong><?php echo htmlspecialchars($cuestionario['titulo']); ?></strong>
                                    </a>
                                <?php else: ?>
                                    <a href="cuestionario_realizar.php?id=<?php echo $cuestionario['id']; ?>">
                                        <?php echo htmlspecialchars($cuestionario['titulo']); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <br>
                                <small style="color: #666;"><?php echo htmlspecialchars($cuestionario['descripcion']); ?></small>
                            </td>

                            <?php if ($tab_activo == 'ver_cuestionarios'): ?>
                                <td><?php echo htmlspecialchars($cuestionario['autor']); ?></td>
                            <?php endif; ?>

                            <td><?php echo date('d/m/Y', strtotime($cuestionario['fecha_creacion'])); ?></td>
                            
                            <td>
                                <?php if ($cuestionario['es_publico']): ?>
                                    <span class="status-tag publico">Público</span>
                                <?php else: ?>
                                    <span class="status-tag privado">Privado</span>
                                <?php endif; ?>
                            </td>

                            <td class="action-buttons">
                                <?php if ($tab_activo == 'mis_cuestionarios'): ?>
                                    <a href="cuestionario_editar.php?id=<?php echo $cuestionario['id']; ?>" class="btn-action" title="Editar">
                                        <img src="../assets/IconoEditar.png" alt="Editar" class="icon-img crud-icon">
                                    </a>
                                    
                                    <form action="../controladores/cuestionario_borrar.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Borrar" 
                                                onclick="return confirm('¿Estás seguro de borrar \u201C<?php echo htmlspecialchars($cuestionario['titulo']); ?>\u201D? Se borrarán sus preguntas.');">
                                            <img src="../assets/IconoPapelera.png" alt="Borrar" class="icon-img crud-icon">
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="cuestionario_realizar.php?id=<?php echo $cuestionario['id']; ?>" class="btn btn-primary btn-sm" style="padding: 5px 10px; font-size: 0.8rem;">
                                        Realizar Test
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html> 