<?php
// UQ Lead Dev: pregunta_crear.php (SOPORTE V/F + MÚLTIPLE)
session_start();
require_once '../controladores/conexion.php';

if (!isset($_SESSION['usuario_id']) || empty($_GET['cid'])) {
    header("Location: dashboard.php");
    exit;
}

$cuestionario_id = $_GET['cid'];
$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT titulo FROM cuestionarios WHERE id = :id AND usuario_id = :uid");
$stmt->execute(['id' => $cuestionario_id, 'uid' => $usuario_id]);
$cuestionario = $stmt->fetch();

if (!$cuestionario) { header("Location: dashboard.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Pregunta | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo"><a href="dashboard.php"><img src="../assets/LogoUQ-w&b.png" alt="Logo" class="logo-image"></a></div>
        <div class="user-nav"><a href="cuestionario_preguntas.php?id=<?php echo $cuestionario_id; ?>" class="btn btn-secondary">← Cancelar</a></div>
    </header>

    <main class="dashboard-content" style="max-width: 800px;">
        <div style="margin-bottom: 25px;">
            <h1 style="color: var(--primary);">Añadir Pregunta</h1>
            <p style="color: var(--text-light);">Para: <strong><?php echo htmlspecialchars($cuestionario['titulo']); ?></strong></p>
        </div>

        <div class="form-card">
            <form action="../controladores/pregunta_guardar.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="cuestionario_id" value="<?php echo $cuestionario_id; ?>">

                <div class="form-group">
                    <label>Enunciado</label>
                    <textarea name="enunciado" rows="3" required placeholder="Escribe aquí la pregunta..."></textarea>
                </div>

                <div class="form-group">
                    <label>Imagen de Apoyo (Opcional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="img-input" name="imagen" accept="image/*" onchange="previewFile()">
                        <label for="img-input" class="custom-file-upload">📂 Seleccionar Imagen</label>
                        <span id="file-name" style="display:block; margin-top:10px; color:#666; font-size:0.9rem; font-style: italic;">Ningún archivo seleccionado</span>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-weight: 600; color: #495057; display:block; margin-bottom: 10px;">TIPO DE PREGUNTA</label>
                    <div class="radio-group-container" style="max-width: 500px;">
                        <div class="radio-option">
                            <input type="radio" id="type-multi" name="tipo_pregunta" value="multiple" checked onchange="toggleQuestionType()">
                            <label for="type-multi"> Opción Múltiple (4)</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="type-vf" name="tipo_pregunta" value="vf" onchange="toggleQuestionType()">
                            <label for="type-vf"> Verdadero / Falso</label>
                        </div>
                    </div>
                </div>

                <div style="background: #f8f9fa; padding: 25px; border-radius: 12px; border: 2px solid #e9ecef; margin-bottom: 25px;">
                    <h3 style="margin-bottom: 15px; font-size: 1rem; color: var(--secondary);">Respuestas</h3>
                    
                    <div class="form-group-option">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="radio" name="correcta" value="1" required style="width: 20px; height: 20px; cursor: pointer;">
                            <input type="text" id="opt_1" name="opcion_1" placeholder="Opción 1" required style="margin-bottom:0;">
                        </div>
                    </div>

                    <div class="form-group-option" style="margin-top: 10px;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="radio" name="correcta" value="2" style="width: 20px; height: 20px; cursor: pointer;">
                            <input type="text" id="opt_2" name="opcion_2" placeholder="Opción 2" required style="margin-bottom:0;">
                        </div>
                    </div>

                    <div class="form-group-option extra-option" style="margin-top: 10px;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="radio" name="correcta" value="3" style="width: 20px; height: 20px; cursor: pointer;">
                            <input type="text" id="opt_3" name="opcion_3" placeholder="Opción 3" required style="margin-bottom:0;">
                        </div>
                    </div>

                    <div class="form-group-option extra-option" style="margin-top: 10px;">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="radio" name="correcta" value="4" style="width: 20px; height: 20px; cursor: pointer;">
                            <input type="text" id="opt_4" name="opcion_4" placeholder="Opción 4" required style="margin-bottom:0;">
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn btn-create" style="width: 100%;">Guardar Pregunta</button>
                </div>
            </form>
        </div>
    </main>

    <footer class="main-footer"><p>&copy; 2026 UniQuiz.</p></footer>

    <script>
        // 1. Script para previsualizar nombre de archivo
        function previewFile() {
            const input = document.getElementById('img-input');
            const fileName = document.getElementById('file-name');
            if(input.files.length > 0){
                fileName.textContent = "✅ " + input.files[0].name;
                fileName.style.color = "#28a745";
                fileName.style.fontWeight = "600";
            } else {
                fileName.textContent = "Ningún archivo seleccionado";
                fileName.style.color = "#666";
            }
        }

        // 2. Script para cambiar entre Múltiple y V/F
        function toggleQuestionType() {
            const isVF = document.getElementById('type-vf').checked;
            const extraOptions = document.querySelectorAll('.extra-option');
            
            const input1 = document.getElementById('opt_1');
            const input2 = document.getElementById('opt_2');
            const input3 = document.getElementById('opt_3');
            const input4 = document.getElementById('opt_4');

            if (isVF) {
                // MODO VERDADERO / FALSO
                // 1. Ocultar opciones 3 y 4
                extraOptions.forEach(div => div.style.display = 'none');
                
                // 2. Quitar 'required' de la 3 y 4 para que deje enviar
                input3.required = false;
                input4.required = false;
                input3.value = ""; // Limpiar
                input4.value = ""; // Limpiar

                // 3. Rellenar textos automáticamente
                input1.value = "Verdadero";
                input2.value = "Falso";
                
                // Opcional: Hacerlos 'readonly' si no quieres que editen el texto
                // input1.readOnly = true; 
                // input2.readOnly = true;

            } else {
                // MODO MÚLTIPLE (Reset)
                // 1. Mostrar opciones 3 y 4
                extraOptions.forEach(div => div.style.display = 'block');

                // 2. Restaurar 'required'
                input3.required = true;
                input4.required = true;

                // 3. Limpiar textos para que escriban lo que quieran
                input1.value = "";
                input2.value = "";
                input1.readOnly = false;
                input2.readOnly = false;
            }
        }
    </script>
</body>
</html>