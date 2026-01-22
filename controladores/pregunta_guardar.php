<?php
// UQ Lead Dev: controladores/pregunta_guardar.php
// Objetivo: Guardar una nueva pregunta, su imagen asociada y sus opciones de respuesta.

session_start();
require_once 'conexion.php';

// Seguridad
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger datos básicos
    $cuestionario_id = $_POST['cuestionario_id'];
    $enunciado = filter_input(INPUT_POST, 'enunciado', FILTER_SANITIZE_SPECIAL_CHARS);
    $tipo = $_POST['tipo']; // opcion_multiple, verdadero_falso
    $usuario_id = $_SESSION['usuario_id'];

    // 2. Verificar que el cuestionario pertenece al usuario (Seguridad)
    $stmtCheck = $pdo->prepare("SELECT id FROM cuestionarios WHERE id = ? AND usuario_id = ?");
    $stmtCheck->execute([$cuestionario_id, $usuario_id]);
    if (!$stmtCheck->fetch()) {
        die("Acceso denegado: No eres el propietario de este cuestionario.");
    }

    try {
        $pdo->beginTransaction(); // Iniciamos transacción para asegurar consistencia
        
        // 3. GESTIÓN DE IMAGEN (Subida de archivos ROBUSTA)
                $nombre_imagen = null;
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $nombre_original = basename($_FILES['imagen']['name']);
                    $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                    
                    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($ext, $permitidas)) {
                        
                        // --- FIX: Asegurar que la carpeta existe ---
                        $carpeta_destino = '../almacen/';
                        if (!is_dir($carpeta_destino)) {
                            if (!mkdir($carpeta_destino, 0755, true)) {
                                throw new Exception("No se pudo crear la carpeta /almacen/.");
                            }
                        }
                        // -------------------------------------------

                        $nombre_imagen = uniqid('img_') . '.' . $ext;
                        $ruta_destino = $carpeta_destino . $nombre_imagen;
                        
                        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                            throw new Exception("Error al mover la imagen. Verifica permisos en /almacen/.");
                        }
                    }
                }

        // 4. Insertar la PREGUNTA
        $sqlPregunta = "INSERT INTO preguntas (cuestionario_id, enunciado, tipo, imagen, orden) 
                        VALUES (:cid, :enunciado, :tipo, :img, 0)";
        $stmtP = $pdo->prepare($sqlPregunta);
        $stmtP->execute([
            'cid' => $cuestionario_id,
            'enunciado' => $enunciado,
            'tipo' => $tipo,
            'img' => $nombre_imagen
        ]);
        
        $pregunta_id = $pdo->lastInsertId();

        // 5. Insertar las OPCIONES según el tipo
        
        if ($tipo == 'opcion_multiple') {
            // Recibimos arrays: opcion_texto[] y opcion_correcta (radio value index)
            $textos = $_POST['opcion_texto']; 
            $correcta_idx = $_POST['opcion_correcta_idx']; // Índice 0, 1, 2 o 3

            for ($i = 0; $i < 4; $i++) {
                // Si el texto está vacío, no lo guardamos (o guardamos vacío, según preferencia)
                if (!empty(trim($textos[$i]))) {
                    $es_correcta = ($i == $correcta_idx) ? 1 : 0;
                    $stmtO = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, ?, ?)");
                    $stmtO->execute([$pregunta_id, $textos[$i], $es_correcta]);
                }
            }

        } elseif ($tipo == 'verdadero_falso') {
            // Creamos automáticamente las dos opciones
            $respuesta_correcta = $_POST['respuesta_vf']; // 'V' o 'F'

            // Opción Verdadero
            $es_v = ($respuesta_correcta == 'V') ? 1 : 0;
            $stmtO = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, 'Verdadero', ?)");
            $stmtO->execute([$pregunta_id, $es_v]);

            // Opción Falso
            $es_f = ($respuesta_correcta == 'F') ? 1 : 0;
            $stmtO = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, 'Falso', ?)");
            $stmtO->execute([$pregunta_id, $es_f]);
        }

        $pdo->commit(); // Confirmar cambios

        $_SESSION['mensaje'] = "Pregunta creada correctamente.";
        header("Location: ../vistas/cuestionario_editar.php?id=" . $cuestionario_id . "&subtab=preguntas");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack(); // Deshacer cambios si algo falló
        $_SESSION['error_crear_pregunta'] = "Error: " . $e->getMessage();
        // Volver al formulario de creación (habría que pasar el ID de nuevo)
        header("Location: ../vistas/pregunta_crear.php?cuestionario_id=" . $cuestionario_id);
        exit;
    }

} else {
    header("Location: ../vistas/dashboard.php");
    exit;
}
?>