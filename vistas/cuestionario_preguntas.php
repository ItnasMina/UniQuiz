<?php
// UQ Lead Dev: cuestionario_preguntas.php
// Objetivo: Listar preguntas (Enunciado Centrado)

session_start();
require_once '../controladores/conexion.php';

// 1. Seguridad básica
if (!isset($_SESSION['usuario_id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$cuestionario_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// 2. Verificar propiedad
$stmt = $pdo->prepare("SELECT * FROM cuestionarios WHERE id = :id AND usuario_id = :uid");
$stmt->execute(['id' => $cuestionario_id, 'uid' => $usuario_id]);
$cuestionario = $stmt->fetch();

if (!$cuestionario) {
    header("Location: dashboard.php");
    exit;
}

// 3. Obtener preguntas
$stmtP = $pdo->prepare("SELECT * FROM preguntas WHERE cuestionario_id = :cid ORDER BY id ASC");
$stmtP->execute(['cid' => $cuestionario_id]);
$preguntas = $stmtP->fetchAll();

$num_preguntas = count($preguntas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas: <?php echo htmlspecialchars($cuestionario['titulo']); ?> | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo">
            <a href="dashboard.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo" class="logo-image">
            </a>
        </div>
        <div class="user-nav">
             <a href="dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
        </div>
    </header>

    <main class="dashboard-content" style="max-width: 1000px;">
        
        <div style="margin-bottom: 25px;">
            <h1 style="color: var(--primary); font-size: 1.8rem;">Gestionar Preguntas</h1>
            <p style="color: var(--text-light);">
                Editando: <strong><?php echo htmlspecialchars($cuestionario['titulo']); ?></strong>
            </p>
        </div>

        <div class="edit-tabs">
            <a href="cuestionario_editar.php?id=<?php echo $cuestionario_id; ?>" class="edit-tab-link">
                ⚙️ Configuración General
            </a>
            <a href="#" class="edit-tab-link active">
                ❓ Gestionar Preguntas (<?php echo $num_preguntas; ?>)
            </a>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
            <a href="pregunta_crear.php?cid=<?php echo $cuestionario_id; ?>" class="btn btn-create">
                + Nueva Pregunta
            </a>
        </div>

        <?php if ($num_preguntas > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%;">Img</th>
                        <th style="width: 45%;">Enunciado</th>
                        <th>Opciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preguntas as $index => $pregunta): 
                        $stmtOpt = $pdo->prepare("SELECT COUNT(*) FROM opciones WHERE pregunta_id = ?");
                        $stmtOpt->execute([$pregunta['id']]);
                        $num_opts = $stmtOpt->fetchColumn();
                    ?>
                    <tr>
                        <td style="font-weight: bold; color: #999;"><?php echo $index + 1; ?></td>
                        
                        <td>
                            <?php if (!empty($pregunta['imagen'])): ?>
                                <img src="../almacen/<?php echo htmlspecialchars($pregunta['imagen']); ?>" 
                                     alt="img" 
                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                            <?php else: ?>
                                <span style="font-size: 1.5rem; opacity: 0.2;">🖼️</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span style="font-weight: 500; color: var(--text-main);">
                                <?php echo htmlspecialchars(substr($pregunta['enunciado'], 0, 80)) . (strlen($pregunta['enunciado']) > 80 ? '...' : ''); ?>
                            </span>
                        </td>

                        <td>
                            <span class="status-tag <?php echo ($num_opts >= 2) ? 'publico' : 'privado'; ?>">
                                <?php echo $num_opts; ?> Opciones
                            </span>
                        </td>

                        <td>
                            <div class="actions-wrapper" style="justify-content: center;">
                                <a href="pregunta_editar.php?id=<?php echo $pregunta['id']; ?>" class="btn-action btn-sm-text btn-edit" title="Editar">Editar</a>
                                
                                <form action="../controladores/pregunta_borrar.php" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar esta pregunta?');">
                                    <input type="hidden" name="id" value="<?php echo $pregunta['id']; ?>">
                                    <input type="hidden" name="cid" value="<?php echo $cuestionario_id; ?>">
                                    <button type="submit" class="btn-action btn-sm-text btn-del" title="Borrar">Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);">
                <div style="font-size: 3rem; margin-bottom: 10px;">📝</div>
                <h3 style="color: var(--text-main);">Este cuestionario está vacío</h3>
                <p style="color: var(--text-light); margin-bottom: 20px;">Empieza añadiendo tu primera pregunta.</p>
                <a href="pregunta_crear.php?cid=<?php echo $cuestionario_id; ?>" class="btn btn-create">
                    Crear Primera Pregunta
                </a>
            </div>
        <?php endif; ?>

    </main>

    <footer class="main-footer"><p>&copy; 2026 UniQuiz.</p></footer>

</body>
</html>