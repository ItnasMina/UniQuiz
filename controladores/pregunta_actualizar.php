<?php
// UQ Lead Dev: controladores/pregunta_actualizar.php
// Objetivo: Actualizar una pregunta existente.
// Estrategia: Actualizamos la pregunta y regeneramos sus opciones para evitar inconsistencias.

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger datos
    $pregunta_id = $_POST['pregunta_id'];
    $cuestionario_id = $_POST['cuestionario_id']; // Necesario para redirigir
    $enunciado = filter_input(INPUT_POST, 'enunciado', FILTER_SANITIZE_SPECIAL_CHARS);
    $tipo = $_POST['tipo'];
    $usuario_id = $_SESSION['usuario_id'];

    // 2. Verificar propiedad (Seguridad CRÍTICA)
    // Comprobamos que el cuestionario de esa pregunta pertenece al usuario
    $sqlCheck = "SELECT p.id, p.imagen FROM preguntas p 
                 JOIN cuestionarios c ON p.cuestionario_id = c.id 
                 WHERE p.id = ? AND c.usuario_id = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$pregunta_id, $usuario_id]);
    $preguntaActual = $stmtCheck->fetch();

    if (!$preguntaActual) {
        die("Error: No tienes permisos para editar esta pregunta.");
    }

    try {
        $pdo->beginTransaction();

    // 3. GESTIÓN DE IMAGEN (Si suben una nueva)
            $nombre_imagen = $preguntaActual['imagen']; // Conservar la vieja por defecto

            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombre_original = basename($_FILES['imagen']['name']);
                $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($ext, $permitidas)) {
                    
                    // --- FIX: Asegurar carpeta ---
                    $carpeta_destino = '../almacen/';
                    if (!is_dir($carpeta_destino)) {
                        mkdir($carpeta_destino, 0755, true);
                    }
                    // -----------------------------

                    // Borrar imagen antigua si existía
                    if ($preguntaActual['imagen'] && file_exists($carpeta_destino . $preguntaActual['imagen'])) {
                        unlink($carpeta_destino . $preguntaActual['imagen']);
                    }

                    $nuevo_nombre = uniqid('img_') . '.' . $ext;
                    $ruta_destino = $carpeta_destino . $nuevo_nombre;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                        $nombre_imagen = $nuevo_nombre;
                    } else {
                        throw new Exception("Error al guardar la nueva imagen en /almacen/.");
                    }
                }
            }

        // 4. Actualizar datos de la PREGUNTA
        $sqlUpdate = "UPDATE preguntas SET enunciado = :enunciado, tipo = :tipo, imagen = :img WHERE id = :pid";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            'enunciado' => $enunciado,
            'tipo' => $tipo,
            'img' => $nombre_imagen,
            'pid' => $pregunta_id
        ]);

        // 5. REGENERAR OPCIONES
        // Borramos las viejas
        $stmtDel = $pdo->prepare("DELETE FROM opciones WHERE pregunta_id = ?");
        $stmtDel->execute([$pregunta_id]);

        // Insertamos las nuevas (Lógica idéntica a crear)
        if ($tipo == 'opcion_multiple') {
            $textos = $_POST['opcion_texto']; 
            $correcta_idx = $_POST['opcion_correcta_idx'];

            for ($i = 0; $i < 4; $i++) {
                // Guardamos si tiene texto o si es uno de los 2 primeros obligatorios (aunque venga vacío el post, lo forzamos en vista)
                if (isset($textos[$i])) {
                    $es_correcta = ($i == $correcta_idx) ? 1 : 0;
                    $stmtO = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, ?, ?)");
                    $stmtO->execute([$pregunta_id, $textos[$i], $es_correcta]);
                }
            }

        } elseif ($tipo == 'verdadero_falso') {
            $respuesta_correcta = $_POST['respuesta_vf']; // 'V' o 'F'
            
            // Verdadero
            $es_v = ($respuesta_correcta == 'V') ? 1 : 0;
            $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, 'Verdadero', ?)")
                ->execute([$pregunta_id, $es_v]);

            // Falso
            $es_f = ($respuesta_correcta == 'F') ? 1 : 0;
            $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (?, 'Falso', ?)")
                ->execute([$pregunta_id, $es_f]);
        }

        $pdo->commit();
        $_SESSION['mensaje'] = "Pregunta actualizada correctamente.";
        header("Location: ../vistas/cuestionario_editar.php?id=" . $cuestionario_id . "&subtab=preguntas");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_editar_pregunta'] = "Error al actualizar: " . $e->getMessage();
        header("Location: ../vistas/pregunta_editar.php?id=" . $pregunta_id);
        exit;
    }

} else {
    header("Location: ../vistas/dashboard.php");
    exit;
}
?>