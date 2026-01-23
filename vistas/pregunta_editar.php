<?php
// UQ Lead Dev: pregunta_editar.php (SIN OPCIÓN DE BORRAR IMAGEN)
session_start();
require_once '../controladores/conexion.php';

// 1. Seguridad
if (!isset($_SESSION['usuario_id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$pregunta_id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// 2. Verificar Propiedad
$sql = "SELECT p.*, c.titulo as titulo_cuestionario, c.id as cid 
        FROM preguntas p
        JOIN cuestionarios c ON p.cuestionario_id = c.id
        WHERE p.id = :pid AND c.usuario_id = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute(['pid' => $pregunta_id, 'uid' => $usuario_id]);
$pregunta = $stmt->fetch();

if (!$pregunta) {
    header("Location: dashboard.php");
    exit;
}

// 3. Obtener Opciones
$stmtOpt = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = :pid ORDER BY id ASC");
$stmtOpt->execute(['pid' => $pregunta_id]);
$opciones = $stmtOpt->fetchAll();

// 4. Detectar tipo
$opciones_validas = 0;
foreach($opciones as $opt) {
    if (!empty(trim($opt['texto_opcion']))) {
        $opciones_validas++;
    }
}
$es_vf = ($opciones_validas <= 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pregunta | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="dashboard-body" onload="toggleQuestionType()"> 

    <header class="main-header private-header small-header">
        <div class="logo">
            <a href="dashboard.php"><img src="../assets/LogoUQ-w&b.png" alt="Logo" class="logo-image"></a>
        </div>
        <div class="user-nav">
             <a href="cuestionario_preguntas.php?id=<?php echo $pregunta['cid']; ?>" class="btn btn-secondary">
                ← Cancelar
             </a>
        </div>
    </header>

    <main class="dashboard-content" style="max-width: 800px;">
        
        <div style="margin-bottom: 25px;">
            <h1 style="color: var(--primary);">Editar Pregunta</h1>
            <p style="color: var(--text-light);">
                Cuestionario: <strong><?php echo htmlspecialchars($pregunta['titulo_cuestionario']); ?></strong>
            </p>
        </div>

        <div class="form-card">
            <form action="../controladores/pregunta_update.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="pregunta_id" value="<?php echo $pregunta_id; ?>">
                <input type="hidden" name="cuestionario_id" value="<?php echo $pregunta['cid']; ?>">

                <div class="form-group">
                    <label>Enunciado</label>
                    <textarea name="enunciado" rows="3" required><?php echo htmlspecialchars($pregunta['enunciado']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Imagen</label>
                    
                    <?php if(!empty($pregunta['imagen'])): ?>
                        <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #eee;">
                            <p style="font-size: 0.85rem; color: #666; margin-bottom: 8px; font-weight: 600;">IMAGEN ACTUAL:</p>
                            <img src="../almacen/<?php echo htmlspecialchars($pregunta['imagen']); ?>" 
                                 style="height: 120px; border-radius: 6px; border: 1px solid #ddd; display: block;">
                        </div>
                    <?php endif; ?>

                    <div class="file-upload-wrapper">
                        <input type="file" id="img-input" name="imagen" accept="image/*" onchange="previewFile()">
                        <label for="img-input" class="custom-file-upload">🔄 Sustituir Imagen</label>
                        <span id="file-name" style="display:block; margin-top:10px; color:#666; font-size:0.9rem;"></span>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #495057; display:block; margin-bottom: 10px;">TIPO DE PREGUNTA</label>
                    <div class="radio-group-container" style="max-width: 500px;">
                        <div class="radio-option">
                            <input type="radio" id="type-multi" name="tipo_pregunta" value="multiple" 
                                   <?php echo (!$es_vf) ? 'checked' : ''; ?> onchange="toggleQuestionType()">
                            <label for="type-multi"> Opción Múltiple</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="type-vf" name="tipo_pregunta" value="vf" 
                                   <?php echo ($es_vf) ? 'checked' : ''; ?> onchange="toggleQuestionType()">
                            <label for="type-vf"> Verdadero / Falso</label>
                        </div>
                    </div>
                </div>

                <div style="background: #f8f9fa; padding: 25px; border-radius: 12px; border: 2px solid #e9ecef; margin-bottom: 25px;">
                    <h3 style="margin-bottom: 15px; font-size: 1rem; color: var(--secondary);">Respuestas</h3>
                    
                    <?php 
                    for($i=0; $i<4; $i++): 
                        $opt = $opciones[$i] ?? null;
                        $texto = $opt ? $opt['texto_opcion'] : '';
                        $es_correcta = $opt ? $opt['es_correcta'] : 0;
                        $opt_id = $opt ? $opt['id'] : '';
                        
                        $extraClass = ($i >= 2) ? 'extra-option' : '';
                    ?>
                    <div class="form-group-option <?php echo $extraClass; ?>" style="margin-bottom: 15px;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="radio" name="correcta_idx" value="<?php echo $i; ?>" 
                                   <?php echo ($es_correcta) ? 'checked' : ''; ?> 
                                   style="width: 20px; height: 20px; cursor: pointer;" required>
                            
                            <input type="text" id="opt_<?php echo $i; ?>" name="opciones[<?php echo $i; ?>]" 
                                   value="<?php echo htmlspecialchars($texto); ?>" 
                                   placeholder="Opción <?php echo $i+1; ?>" required style="margin-bottom:0;">
                            
                            <input type="hidden" name="ids_opciones[<?php echo $i; ?>]" value="<?php echo $opt_id; ?>">
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar Cambios</button>
                </div>
            </form>
        </div>

    </main>
    
    <footer class="main-footer"><p>&copy; 2026 UniQuiz.</p></footer>

    <script>
        function previewFile() {
            const input = document.getElementById('img-input');
            const fileName = document.getElementById('file-name');
            if(input.files.length > 0){
                fileName.textContent = "Se subirá: " + input.files[0].name;
                fileName.style.color = "#28a745";
                fileName.style.fontWeight = "600";
            }
        }

        function toggleQuestionType() {
            const isVF = document.getElementById('type-vf').checked;
            const extraOptions = document.querySelectorAll('.extra-option');
            
            const input1 = document.getElementById('opt_0'); 
            const input2 = document.getElementById('opt_1');
            const input3 = document.getElementById('opt_2');
            const input4 = document.getElementById('opt_3');

            if (isVF) {
                extraOptions.forEach(div => div.style.display = 'none');
                
                if(input3) { input3.required = false; input3.value = ""; }
                if(input4) { input4.required = false; input4.value = ""; }

                if(input1 && input1.value === "") input1.value = "Verdadero";
                if(input2 && input2.value === "") input2.value = "Falso";

            } else {
                extraOptions.forEach(div => div.style.display = 'block');
                if(input3) input3.required = true;
                if(input4) input4.required = true;
            }
        }
    </script>
</body>
</html>