<?php
// UQ Lead Dev: cuestionario_realizar.php
// Objetivo: Interfaz para que el estudiante responda al examen.

session_start();
require_once '../controladores/conexion.php';

// Verificación básica de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$cuestionario_id = $_GET['id'] ?? null;
if (!$cuestionario_id) {
    header("Location: dashboard.php");
    exit;
}

try {
    // 1. Obtener datos del cuestionario
    $stmt = $pdo->prepare("SELECT * FROM cuestionarios WHERE id = :id");
    $stmt->execute(['id' => $cuestionario_id]);
    $cuestionario = $stmt->fetch();

    if (!$cuestionario) {
        die("El cuestionario no existe.");
    }

    // Seguridad: Si es privado y NO es mío, no puedo entrar
    if (!$cuestionario['es_publico'] && $cuestionario['usuario_id'] != $_SESSION['usuario_id']) {
        $_SESSION['mensaje'] = "Este cuestionario es privado.";
        header("Location: dashboard.php");
        exit;
    }

    // 2. Obtener Preguntas
    $orden_sql = $cuestionario['es_aleatorio'] ? "RAND()" : "orden ASC, id ASC";
    $limite_sql = $cuestionario['es_aleatorio'] ? "LIMIT " . $cuestionario['num_preguntas_aleatorias'] : "";
    
    $sqlPreguntas = "SELECT * FROM preguntas WHERE cuestionario_id = :cid ORDER BY $orden_sql $limite_sql";
    $stmtP = $pdo->prepare($sqlPreguntas);
    $stmtP->execute(['cid' => $cuestionario_id]);
    $preguntas = $stmtP->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizando: <?php echo htmlspecialchars($cuestionario['titulo']); ?> | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
    <style>
        .pregunta-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .pregunta-img {
            max-width: 100%;
            max-height: 300px;
            margin: 15px 0;
            border-radius: 4px;
            display: block;
        }
        .opcion-label {
            display: block;
            padding: 10px;
            border: 1px solid #eee;
            margin-bottom: 5px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .opcion-label:hover {
            background-color: #f9f9f9;
        }
        .opcion-input {
            margin-right: 10px;
        }
    </style>
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo">
            <span style="color: #386DBD; font-weight:bold; font-size:1.2rem;">📝 Realizando Test</span>
        </div>
        <div class="user-nav">
             <a href="dashboard.php" class="btn btn-secondary" onclick="return confirm('Si sales ahora perderás tus respuestas. ¿Seguro?');">Cancelar</a>
        </div>
    </header>
    
    <main class="dashboard-content edit-container" style="max-width: 800px;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1><?php echo htmlspecialchars($cuestionario['titulo']); ?></h1>
            <p><?php echo htmlspecialchars($cuestionario['descripcion']); ?></p>
        </div>

        <form action="../controladores/cuestionario_corregir.php" method="POST">
            <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario_id; ?>">
            
            <?php foreach ($preguntas as $index => $pregunta): ?>
                <div class="pregunta-card">
                    <h3><?php echo ($index + 1) . ". " . htmlspecialchars($pregunta['enunciado']); ?></h3>
                    
                    <?php if ($pregunta['imagen']): ?>
                        <img src="../almacen/<?php echo htmlspecialchars($pregunta['imagen']); ?>" alt="Imagen de la pregunta" class="pregunta-img">
                    <?php endif; ?>

                    <div class="opciones-container">
                        <?php
                        // Obtener opciones para esta pregunta
                        $stmtO = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = :pid ORDER BY id ASC");
                        $stmtO->execute(['pid' => $pregunta['id']]);
                        $opciones = $stmtO->fetchAll();

                        // --- DIAGNÓSTICO: SI NO HAY OPCIONES, AVISAR ---
                        if (count($opciones) == 0): ?>
                            <div style="padding:15px; background-color:#ffe6e6; color:#cc0000; border-radius:5px; border:1px solid #ffcccc;">
                                ⚠️ <strong>Error de datos:</strong> Esta pregunta no tiene opciones registradas en la base de datos.
                                <br><small>Por favor, edita la pregunta y vuelve a guardar las opciones.</small>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($opciones as $opcion): ?>
                            <label class="opcion-label">
                                <input type="radio" 
                                       name="respuestas[<?php echo $pregunta['id']; ?>]" 
                                       value="<?php echo $opcion['id']; ?>" 
                                       class="opcion-input" required>
                                <?php echo htmlspecialchars($opcion['texto_opcion']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="text-align: center; margin-top: 30px; margin-bottom: 50px;">
                <button type="submit" class="btn btn-primary btn-call-to-action" style="width: 100%; max-width: 400px;">
                    Finalizar y Ver Nota
                </button>
            </div>
        </form>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html>