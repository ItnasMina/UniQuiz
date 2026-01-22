<?php
// UQ Lead Dev: pregunta_editar.php
// Objetivo: Formulario PRE-RELLENADO para editar una pregunta existente.

session_start();
require_once '../controladores/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$pregunta_id = $_GET['id'] ?? null;
if (!$pregunta_id) {
    header("Location: dashboard.php");
    exit;
}

// 1. Obtener Datos de la Pregunta y verificar propiedad
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT p.*, c.titulo as titulo_cuestionario, c.id as cuestionario_id 
        FROM preguntas p
        JOIN cuestionarios c ON p.cuestionario_id = c.id
        WHERE p.id = :pid AND c.usuario_id = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute(['pid' => $pregunta_id, 'uid' => $usuario_id]);
$pregunta = $stmt->fetch();

if (!$pregunta) {
    die("Pregunta no encontrada o acceso denegado.");
}

// 2. Obtener Opciones actuales
$stmtO = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = :pid ORDER BY id ASC");
$stmtO->execute(['pid' => $pregunta_id]);
$opciones = $stmtO->fetchAll();

// Lógica para pre-seleccionar la correcta
// Si es V/F, determinamos si la correcta actual es "Verdadero" o "Falso"
$vf_correcta = 'V'; // Valor por defecto
if ($pregunta['tipo'] == 'verdadero_falso') {
    foreach ($opciones as $op) {
        if ($op['texto_opcion'] == 'Falso' && $op['es_correcta']) {
            $vf_correcta = 'F';
        }
    }
}

// Errores flash
$error = $_SESSION['error_editar_pregunta'] ?? '';
unset($_SESSION['error_editar_pregunta']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pregunta | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
    <style>
        .option-row { display: flex; align-items: center; margin-bottom: 10px; gap: 10px; }
        .option-row input[type="text"] { flex-grow: 1; }
        .img-preview { max-width: 200px; margin-top: 10px; border: 1px solid #ccc; padding: 5px; }
        .current-img-info { margin-bottom: 10px; font-size: 0.9em; color: #555; }
    </style>
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo">
            <a href="dashboard.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo UniQuiz" class="logo-image">
            </a>
        </div>
        <div class="user-nav">
             <a href="cuestionario_editar.php?id=<?php echo $pregunta['cuestionario_id']; ?>&subtab=preguntas" class="btn btn-back">Cancelar</a>
        </div>
    </header>
    
    <main class="dashboard-content edit-container">
        
        <h1>Editar Pregunta</h1>
        <p class="text-muted">Cuestionario: <?php echo htmlspecialchars($pregunta['titulo_cuestionario']); ?></p>

        <?php if ($error): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="../controladores/pregunta_actualizar.php" method="POST" enctype="multipart/form-data" class="form-standard form-card">
            
            <input type="hidden" name="pregunta_id" value="<?php echo $pregunta['id']; ?>">
            <input type="hidden" name="cuestionario_id" value="<?php echo $pregunta['cuestionario_id']; ?>">

            <div class="form-group">
                <label for="enunciado">Enunciado *</label>
                <textarea id="enunciado" name="enunciado" rows="3" required><?php echo htmlspecialchars($pregunta['enunciado']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen (Opcional)</label>
                
                <?php if ($pregunta['imagen']): ?>
                    <div class="current-img-info">
                        <p>Imagen actual:</p>
                        <img src="../almacen/<?php echo htmlspecialchars($pregunta['imagen']); ?>" class="img-preview" style="display:block;">
                        <small>Sube otra para reemplazarla.</small>
                    </div>
                <?php endif; ?>

                <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)">
                <img id="preview" class="img-preview" alt="Vista previa nueva" style="display:none;">
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de Respuesta</label>
                <select id="tipo" name="tipo" class="select-standard" onchange="toggleOptions()">
                    <option value="opcion_multiple" <?php echo ($pregunta['tipo'] == 'opcion_multiple') ? 'selected' : ''; ?>>Opción Múltiple (Test)</option>
                    <option value="verdadero_falso" <?php echo ($pregunta['tipo'] == 'verdadero_falso') ? 'selected' : ''; ?>>Verdadero / Falso</option>
                </select>
            </div>

            <hr class="separator">

            <div id="bloque-multiple">
                <h4>Opciones de Respuesta</h4>
                <?php 
                    // Preparamos 4 iteraciones. Si hay menos opciones guardadas (ej: 3), rellenamos vacíos.
                    // Si el tipo actual es V/F, las opciones guardadas no sirven para este bloque, saldrán vacías.
                    for($i=0; $i<4; $i++): 
                        $texto_val = '';
                        $checked = '';
                        
                        if ($pregunta['tipo'] == 'opcion_multiple' && isset($opciones[$i])) {
                            $texto_val = $opciones[$i]['texto_opcion'];
                            if ($opciones[$i]['es_correcta']) $checked = 'checked';
                        }
                        // Default check first if none selected (rare edge case)
                        if ($i==0 && $pregunta['tipo'] != 'opcion_multiple') $checked = 'checked';
                ?>
                    <div class="option-row">
                        <input type="radio" name="opcion_correcta_idx" value="<?php echo $i; ?>" <?php echo $checked; ?>>
                        <input type="text" name="opcion_texto[]" class="input-opcion" value="<?php echo htmlspecialchars($texto_val); ?>" placeholder="Opción <?php echo $i+1; ?>" <?php echo ($i<2)?'required':''; ?>>
                    </div>
                <?php endfor; ?>
            </div>

            <div id="bloque-vf" style="display: none;">
                <h4>Respuesta Correcta</h4>
                <div class="radio-group">
                    <input type="radio" id="resp_v" name="respuesta_vf" value="V" <?php echo ($vf_correcta == 'V') ? 'checked' : ''; ?>>
                    <label for="resp_v">Verdadero</label>
                    
                    <input type="radio" id="resp_f" name="respuesta_vf" value="F" style="margin-left: 20px;" <?php echo ($vf_correcta == 'F') ? 'checked' : ''; ?>>
                    <label for="resp_f">Falso</label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary btn-full-width">Guardar Cambios</button>
            </div>

        </form>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

    <script>
        function toggleOptions() {
            const tipo = document.getElementById('tipo').value;
            const bloqueMultiple = document.getElementById('bloque-multiple');
            const bloqueVF = document.getElementById('bloque-vf');
            const inputsMultiples = document.querySelectorAll('.input-opcion');

            if (tipo === 'opcion_multiple') {
                bloqueMultiple.style.display = 'block';
                bloqueVF.style.display = 'none';
                inputsMultiples.forEach((input, index) => {
                    input.disabled = false;
                    if (index < 2) input.required = true;
                });
            } else {
                bloqueMultiple.style.display = 'none';
                bloqueVF.style.display = 'block';
                inputsMultiples.forEach(input => {
                    input.disabled = true;
                    input.required = false;
                });
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // Ejecutar al cargar para establecer el estado inicial correcto
        window.onload = toggleOptions;
    </script>
</body>
</html>