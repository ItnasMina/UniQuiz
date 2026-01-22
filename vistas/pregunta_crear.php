<?php
// UQ Lead Dev: pregunta_crear.php
// Objetivo: Formulario para crear una pregunta. 
// FIX: Gestiona correctamente los atributos 'required' y 'disabled' al cambiar de tipo.

session_start();
require_once '../controladores/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$cuestionario_id = $_GET['cuestionario_id'] ?? null;
if (!$cuestionario_id) {
    header("Location: dashboard.php");
    exit;
}

// Obtener info básica
$stmt = $pdo->prepare("SELECT titulo FROM cuestionarios WHERE id = ?");
$stmt->execute([$cuestionario_id]);
$cuestionario = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Pregunta | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
    
    <style>
        .option-row { display: flex; align-items: center; margin-bottom: 10px; gap: 10px; }
        .option-row input[type="text"] { flex-grow: 1; }
        .img-preview { max-width: 200px; margin-top: 10px; display: none; border: 1px solid #ccc; padding: 5px; }
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
             <a href="cuestionario_editar.php?id=<?php echo $cuestionario_id; ?>&subtab=preguntas" class="btn btn-back">Cancelar y Volver</a>
        </div>
    </header>
    
    <main class="dashboard-content edit-container">
        
        <h1>Añadir Pregunta a: <?php echo htmlspecialchars($cuestionario['titulo']); ?></h1>

        <form action="../controladores/pregunta_guardar.php" method="POST" enctype="multipart/form-data" class="form-standard form-card">
            
            <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario_id; ?>">

            <div class="form-group">
                <label for="enunciado">Enunciado de la Pregunta *</label>
                <textarea id="enunciado" name="enunciado" rows="3" required placeholder="Escribe aquí la pregunta..."></textarea>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen de apoyo (Opcional)</label>
                <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)">
                <img id="preview" class="img-preview" alt="Vista previa">
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de Respuesta</label>
                <select id="tipo" name="tipo" class="select-standard" onchange="toggleOptions()">
                    <option value="opcion_multiple">Opción Múltiple (Test)</option>
                    <option value="verdadero_falso">Verdadero / Falso</option>
                </select>
            </div>

            <hr class="separator">

            <div id="bloque-multiple">
                <h4>Opciones de Respuesta</h4>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">Escribe las respuestas. Las dos primeras son obligatorias.</p>
                
                <?php for($i=0; $i<4; $i++): ?>
                    <div class="option-row">
                        <input type="radio" name="opcion_correcta_idx" value="<?php echo $i; ?>" <?php echo ($i===0)?'checked':''; ?>>
                        <input type="text" name="opcion_texto[]" class="input-opcion" placeholder="Opción <?php echo $i+1; ?>" <?php echo ($i<2)?'required':''; ?>>
                    </div>
                <?php endfor; ?>
            </div>

            <div id="bloque-vf" style="display: none;">
                <h4>Respuesta Correcta</h4>
                <div class="radio-group">
                    <input type="radio" id="resp_v" name="respuesta_vf" value="V" checked>
                    <label for="resp_v">Verdadero</label>
                    
                    <input type="radio" id="resp_f" name="respuesta_vf" value="F" style="margin-left: 20px;">
                    <label for="resp_f">Falso</label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary btn-full-width">Guardar Pregunta</button>
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
            
            // Seleccionamos todos los inputs de texto de las opciones múltiples
            const inputsMultiples = document.querySelectorAll('.input-opcion');

            if (tipo === 'opcion_multiple') {
                // MOSTRAR Múltiple
                bloqueMultiple.style.display = 'block';
                bloqueVF.style.display = 'none';
                
                // Reactivar inputs y restaurar 'required' en los dos primeros
                inputsMultiples.forEach((input, index) => {
                    input.disabled = false;
                    // Solo los 2 primeros eran obligatorios originalmente
                    if (index < 2) input.required = true;
                });

            } else {
                // MOSTRAR Verdadero/Falso
                bloqueMultiple.style.display = 'none';
                bloqueVF.style.display = 'block';
                
                // Desactivar inputs múltiples para que HTML5 no los valide ni los envíe
                inputsMultiples.forEach(input => {
                    input.disabled = true;
                    input.required = false; // Doble seguridad
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
    </script>
</body>
</html>