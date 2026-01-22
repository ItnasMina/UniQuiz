<?php
// UQ Lead Dev: cuestionario_editar.php (CORREGIDO Y SINCRONIZADO)
session_start();
require_once '../controladores/conexion.php';

// 1. Seguridad
if (!isset($_SESSION['usuario_id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$cuestionario_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// 2. Obtener datos y verificar propiedad
$stmt = $pdo->prepare("SELECT * FROM cuestionarios WHERE id = :id AND usuario_id = :uid");
$stmt->execute(['id' => $cuestionario_id, 'uid' => $usuario_id]);
$cuestionario = $stmt->fetch();

if (!$cuestionario) {
    header("Location: dashboard.php");
    exit;
}

// 3. Contar preguntas (Para mostrar el número en la pestaña)
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM preguntas WHERE cuestionario_id = ?");
$stmtCount->execute([$cuestionario_id]);
$num_preguntas = $stmtCount->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar: <?php echo htmlspecialchars($cuestionario['titulo']); ?> | UniQuiz</title>
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

    <main class="dashboard-content" style="max-width: 900px;">
        
        <div style="margin-bottom: 25px;">
            <h1 style="color: var(--primary); font-size: 1.8rem;">Editando Cuestionario</h1>
            <p style="color: var(--text-light);">Configura los detalles generales o gestiona las preguntas.</p>
        </div>

        <div class="edit-tabs">
            <a href="#" class="edit-tab-link active">
                ⚙️ Configuración General
            </a>
            <a href="cuestionario_preguntas.php?id=<?php echo $cuestionario_id; ?>" class="edit-tab-link">
                ❓ Gestionar Preguntas (<?php echo $num_preguntas; ?>)
            </a>
        </div>


        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-bottom: 20px;">
                ✅ ¡Cambios guardados correctamente!
            </div>
            <?php endif; ?>
        <div class="form-card">
            <form action="../controladores/cuestionario_update.php" method="POST">
                <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario['id']; ?>">

                <div class="form-group">
                    <label>Título del Cuestionario</label>
                    <input type="text" name="titulo" value="<?php echo htmlspecialchars($cuestionario['titulo']); ?>" required 
                           style="font-size: 1.1rem; font-weight: 600;">
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3" required><?php echo htmlspecialchars($cuestionario['descripcion']); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
                    
                    <div class="form-group">
                        <label>Visibilidad</label>
                        <div class="radio-group-container">
                            <div class="radio-option">
                                <input type="radio" id="privado" name="es_publico" value="0" 
                                       <?php echo (!$cuestionario['es_publico']) ? 'checked' : ''; ?>>
                                <label for="privado">🔒 Privado</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="publico" name="es_publico" value="1" 
                                       <?php echo ($cuestionario['es_publico']) ? 'checked' : ''; ?>>
                                <label for="publico">🌍 Público</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Opciones de Examen</label>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="random" name="random" value="1" disabled>
                            <label for="random" style="margin:0; cursor: not-allowed; color: #999;">Aleatorizar (Próximamente)</label>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary" style="padding-left: 40px; padding-right: 40px;">
                        Guardar Cambios
                    </button>
                </div>

            </form>
        </div>

    </main>

    <footer class="main-footer"><p>&copy; 2026 UniQuiz.</p></footer>

</body>
</html>