<?php
// UQ Lead Dev: dashboard.php
// Objetivo: Página principal del área privada (Dashboard).
// Muestra la navegación principal (Tabs), accesos de usuario y el contenido dinámico.

// REGLA DE ORO DE CODIFICACIÓN: Sesiones
session_start();

// Lógica de autenticación: Si el usuario no está logueado, redirigir.
// if (!isset($_SESSION['usuario_id'])) {
//     header("Location: login.php");
//     exit;
// }

// Simulamos los datos del usuario logueado para la cabecera
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario UQ'; 

// Definimos el Tab activo por defecto (Página 5: Mis cuestionarios)
$tab_activo = $_GET['tab'] ?? 'mis_cuestionarios'; 

// Incluimos funciones esenciales (para futura lógica de base de datos)
include 'includes/funciones.php';
// include 'includes/db.php'; // Se incluiría para obtener datos reales

// Datos simulados para el listado de cuestionarios (Página 5)
$cuestionarios_mock = [
    ['id' => 1, 'titulo' => 'Bases de Datos Avanzadas', 'preguntas' => 12, 'fecha' => '2025-10-01', 'publico' => true],
    ['id' => 2, 'titulo' => 'Introducción a PHP y LAMP', 'preguntas' => 5, 'fecha' => '2025-10-15', 'publico' => false],
    ['id' => 3, 'titulo' => 'Algoritmos de Ordenamiento', 'preguntas' => 20, 'fecha' => '2025-11-20', 'publico' => true],
];
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
                <img src="../assets/LogoUQ-w&b.png" alt="Logo UniQuiz con texto" class="logo-image">
            </a>
        </div>
        
        <nav class="main-tabs-container">
            <a href="dashboard.php?tab=mis_cuestionarios" 
               class="tab-item <?php echo ($tab_activo == 'mis_cuestionarios') ? 'active' : ''; ?>">
                Mis Cuestionarios
            </a>
            <a href="dashboard.php?tab=ver_cuestionarios" 
               class="tab-item <?php echo ($tab_activo == 'ver_cuestionarios') ? 'active' : ''; ?>">
                Ver Cuestionarios Públicos
            </a>
        </nav>

        <nav class="user-nav">
            <span class="user-welcome">Hola, <?php echo htmlspecialchars($nombre_usuario); ?></span>
            
            <a href="perfil.php" class="nav-icon" title="Mi Perfil">
                <img src="../assets/IconoPerfil.png" alt="Mi Perfil" class="icon-img user-icon">
                Mi Perfil 
            </a>
            
            <a href="logout.php" class="nav-icon btn-logout" title="Cerrar Sesión">
                <img src="../assets/IconoSalir.png" alt="Cerrar Sesión" class="icon-img user-icon">
                Salir 
            </a>
        </nav>
    </header>
    
    <main class="dashboard-content">
        
        <?php if ($tab_activo == 'mis_cuestionarios'): ?>
            <section class="tab-panel active">
                <div class="header-listado">
                    <h2>Mis Cuestionarios Creados</h2>
                    <a href="cuestionario_crear.php" class="btn btn-create">
                        + Crear Nuevo
                    </a>
                </div>
                
                <?php if (empty($cuestionarios_mock)): ?>
                    <p class="empty-list-message">Aún no has creado ningún cuestionario. ¡Empieza ahora!</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th># Preguntas</th>
                                <th>Creado en</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuestionarios_mock as $cuestionario): ?>
                                <tr>
                                    <td>
                                        <a href="cuestionario_ver.php?id=<?php echo $cuestionario['id']; ?>">
                                            <?php echo htmlspecialchars($cuestionario['titulo']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo $cuestionario['preguntas']; ?></td>
                                    <td><?php echo $cuestionario['fecha']; ?></td>
                                    <td>
                                        <span class="status-tag <?php echo $cuestionario['publico'] ? 'publico' : 'privado'; ?>">
                                            <?php echo $cuestionario['publico'] ? 'Público' : 'Privado'; ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="cuestionario_editar.php?id=<?php echo $cuestionario['id']; ?>" class="btn-action" title="Editar">
                                            <img src="../assets/IconoEditar.png" alt="Editar" class="icon-img crud-icon">
                                        </a>
                                        <form action="dashboard.php" method="POST">
                                            <input type="hidden" name="accion" value="borrar">
                                            <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario['id']; ?>">
                                            <button type="submit" class="btn-action btn-delete" title="Borrar" onclick="return confirm('¿Estás seguro de que deseas borrar este cuestionario?');">
                                                <img src="../assets/IconoPapelera.png" alt="Borrar" class="icon-img crud-icon">
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

        <?php elseif ($tab_activo == 'ver_cuestionarios'): ?>
            <section class="tab-panel active">
                <h2>Explorar Cuestionarios Públicos</h2>
                <p>Aquí verás el listado de cuestionarios que han sido marcados como públicos por otros usuarios, listos para ser respondidos.</p>
                </section>
        
        <?php endif; ?>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html>