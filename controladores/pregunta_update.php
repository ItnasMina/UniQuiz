<?php
// UQ Lead Dev: controladores/pregunta_update.php
// Objetivo: Actualizar una pregunta existente, su imagen y sus opciones.

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/dashboard.php");
    exit;
}

$pregunta_id = $_POST['pregunta_id'];
$cuestionario_id = $_POST['cuestionario_id'];
$enunciado = trim($_POST['enunciado']);
$usuario_id = $_SESSION['usuario_id'];

// Arrays que vienen del formulario
$opciones_texto = $_POST['opciones']; // Array [0 => 'Texto', 1 => 'Texto'...]
$ids_opciones = $_POST['ids_opciones']; // Array con los IDs reales de la BBDD
$correcta_idx = intval($_POST['correcta_idx']); // Índice (0, 1, 2 o 3) seleccionado

try {
    // 1. VERIFICAR PROPIEDAD (Seguridad)
    // Obtenemos también la imagen actual para saber qué borrar si hace falta
    $sqlCheck = "SELECT p.id, p.imagen, c.usuario_id 
                 FROM preguntas p 
                 JOIN cuestionarios c ON p.cuestionario_id = c.id 
                 WHERE p.id = :pid AND c.usuario_id = :uid";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute(['pid' => $pregunta_id, 'uid' => $usuario_id]);
    $pregunta_actual = $stmtCheck->fetch();

    if (!$pregunta_actual) {
        die("Error: No tienes permiso para editar esta pregunta.");
    }

    // 2. GESTIÓN DE LA IMAGEN
    $imagen_final = $pregunta_actual['imagen']; // Por defecto, mantenemos la vieja

    // A) ¿El usuario marcó "Borrar imagen"?
    if (isset($_POST['borrar_imagen']) && $_POST['borrar_imagen'] == '1') {
        if (!empty($imagen_final) && file_exists("../almacen/" . $imagen_final)) {
            unlink("../almacen/" . $imagen_final); // Borrar archivo físico
        }
        $imagen_final = null;
    }

    // B) ¿El usuario subió una imagen NUEVA?
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        // Borrar la vieja si existe y no se borró ya en el paso A
        if (!empty($pregunta_actual['imagen']) && file_exists("../almacen/" . $pregunta_actual['imagen'])) {
            unlink("../almacen/" . $pregunta_actual['imagen']);
        }

        // Subir la nueva
        $directorio = "../almacen/";
        if (!is_dir($directorio)) mkdir($directorio, 0777, true);
        
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = uniqid() . "." . $extension;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $nombre_archivo)) {
            $imagen_final = $nombre_archivo;
        }
    }

    // 3. ACTUALIZAR TABLA PREGUNTAS
    $sqlUpd = "UPDATE preguntas SET enunciado = :enu, imagen = :img WHERE id = :pid";
    $stmtUpd = $pdo->prepare($sqlUpd);
    $stmtUpd->execute([
        'enu' => $enunciado,
        'img' => $imagen_final,
        'pid' => $pregunta_id
    ]);

    // 4. ACTUALIZAR LAS OPCIONES
    // Recorremos las 4 opciones (0, 1, 2, 3)
    for ($i = 0; $i < 4; $i++) {
        $texto = trim($opciones_texto[$i]);
        $id_opcion = $ids_opciones[$i];
        
        // Determinar si esta es la correcta
        $es_correcta = ($i === $correcta_idx) ? 1 : 0;

        // Actualizamos la opción en BBDD
        if (!empty($id_opcion)) {
            $stmtOpt = $pdo->prepare("UPDATE opciones SET texto_opcion = :txt, es_correcta = :correcta WHERE id = :oid");
            $stmtOpt->execute([
                'txt' => $texto,
                'correcta' => $es_correcta,
                'oid' => $id_opcion
            ]);
        } else {
            // (Caso raro) Si por algún motivo faltaba una opción en BBDD, la creamos
            if (!empty($texto)) {
                $stmtIns = $pdo->prepare("INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES (:pid, :txt, :correcta)");
                $stmtIns->execute([
                    'pid' => $pregunta_id,
                    'txt' => $texto,
                    'correcta' => $es_correcta
                ]);
            }
        }
    }

    // 5. REDIRIGIR AL LISTADO
    header("Location: ../vistas/cuestionario_preguntas.php?id=" . $cuestionario_id);
    exit;

} catch (PDOException $e) {
    die("Error en base de datos: " . $e->getMessage());
}
?>