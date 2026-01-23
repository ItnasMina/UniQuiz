<?php
// UQ Lead Dev: vistas/dashboard.php
// Objetivo: Panel principal con foto de perfil en carpeta 'perfiles'.

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
// Foto de perfil desde sesión (se actualiza al subir nueva foto)
$foto_perfil = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : 'default_user.png';

$tab_activo = $_GET['tab'] ?? 'mis_cuestionarios';

// Variables para la vista
$cuestionarios = [];
$titulo_seccion = "";

try {
    if ($tab_activo == 'mis_cuestionarios') {
        $titulo_seccion = "Mis Cuestionarios Creados";
        
        // CONSULTA A: Mis cuestionarios
        $sql = "SELECT * FROM cuestionarios 
                WHERE usuario_id = :uid 
                ORDER BY fecha_creacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uid' => $usuario_id]);
        $cuestionarios = $stmt->fetchAll();

    } elseif ($tab_activo == 'ver_cuestionarios') {
        $titulo_seccion = "Explorar Cuestionarios de la Comunidad";
        
        // CONSULTA B: Cuestionarios públicos de otros (JOIN para saber autor)
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
    $error_db = "Error al cargar datos: " . $e->getMessage();
}

// Mensajes Flash
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
                Comunidad
            </a>
        </nav>

        <nav class="user-nav">
            <span class="user-welcome">Hola, <?php echo htmlspecialchars($nombre_usuario); ?></span>
            
            <a href="perfil.php" class="nav-icon" title="Editar mi Perfil">
                <?php if (!empty($foto_perfil) && $foto_perfil != 'default_user.png'): ?>
                    <img src="../perfiles/<?php echo htmlspecialchars($foto_perfil); ?>" class="icon-img user-icon" style="object-fit: cover;">
                <?php else: ?>
                    <span class="user-icon" style="background:#ddd; display:inline-block; text-align:center; line-height:35px;">👤</span>
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
            <div class="alert">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_db)): ?>
            <div style="color: red; margin-bottom: 20px;"><?php echo $error_db; ?></div>
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
            <div class="form-card" style="text-align: center; color: #666;">
                <p>
                    <?php echo ($tab_activo == 'mis_cuestionarios') 
                        ? "Aún no has creado ningún cuestionario. ¡Empieza ahora!" 
                        : "No hay cuestionarios públicos disponibles de otros usuarios en este momento."; ?>
                </p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Título y Descripción</th>
                        <?php if ($tab_activo == 'ver_cuestionarios'): ?>
                            <th>Autor</th>
                        <?php endif; ?>
                        <th>Fecha Creación</th>
                        <?php if ($tab_activo == 'mis_cuestionarios'): ?>
                            <th>Estado</th>
                        <?php endif; ?>
                        <th>Acciones</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cuestionarios as $cuestionario): ?>
                        <tr>
                            <td>
                                <?php if ($tab_activo == 'mis_cuestionarios'): ?>
                                    <a href="cuestionario_editar.php?id=<?php echo $cuestionario['id']; ?>" style="font-weight: bold; font-size: 1.05rem;">
                                        <?php echo htmlspecialchars($cuestionario['titulo']); ?>
                                    </a>
                                <?php else: ?>
                                    <a href="cuestionario_realizar.php?id=<?php echo $cuestionario['id']; ?>" style="font-weight: bold; font-size: 1.05rem;">
                                        <?php echo htmlspecialchars($cuestionario['titulo']); ?>
                                    </a>
                                <?php endif; ?>
                                <br>
                                <small style="color: #7f8c8d;"><?php echo htmlspecialchars($cuestionario['descripcion']); ?></small>
                            </td>

                            <?php if ($tab_activo == 'ver_cuestionarios'): ?>
                                <td>
                                    <span style="font-weight: 500; color: var(--secondary);">
                                        <?php echo htmlspecialchars($cuestionario['autor']); ?>
                                    </span>
                                </td>
                            <?php endif; ?>

                            <td><?php echo date('d/m/Y', strtotime($cuestionario['fecha_creacion'])); ?></td>
                            
                            <?php if ($tab_activo == 'mis_cuestionarios'): ?>
                                <td>
                                    <?php if ($cuestionario['es_publico']): ?>
                                        <span class="status-tag publico">Público</span>
                                    <?php else: ?>
                                        <span class="status-tag privado">Privado</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>

                            <td>
                                <div class="actions-wrapper">
                                    <?php if ($tab_activo == 'mis_cuestionarios'): ?>
                                        <a href="cuestionario_realizar.php?id=<?php echo $cuestionario['id']; ?>" class="btn-action btn-sm-text btn-info" title="Probar">
                                            Ver
                                        </a>
                                        <a href="cuestionario_editar.php?id=<?php echo $cuestionario['id']; ?>" class="btn-action btn-sm-text btn-edit" title="Editar">
                                            Editar
                                        </a>
                                        <form action="../controladores/cuestionario_borrar.php" method="POST">
                                            <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario['id']; ?>">
                                            <button type="submit" class="btn-action btn-sm-text btn-del" title="Borrar" 
                                                    onclick="return confirm('¿Estás seguro de borrar \u201C<?php echo htmlspecialchars($cuestionario['titulo']); ?>\u201D?');">
                                                Borrar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="cuestionario_realizar.php?id=<?php echo $cuestionario['id']; ?>" class="btn btn-primary btn-sm" style="font-size: 0.85rem; padding: 5px 10px;">
                                            Realizar
                                        </a>
                                    <?php endif; ?>
                                </div>
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