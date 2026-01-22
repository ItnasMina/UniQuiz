<?php
// UQ Lead Dev: controladores/cuestionario_corregir.php
// Objetivo: Calcular nota Y GUARDAR EL DESGLOSE DE RESPUESTAS.

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/dashboard.php");
    exit;
}

$cuestionario_id = $_POST['cuestionario_id'];
$respuestas_usuario = $_POST['respuestas'] ?? []; 
$usuario_id = $_SESSION['usuario_id'];

try {
    $stmtP = $pdo->prepare("SELECT * FROM preguntas WHERE cuestionario_id = :cid ORDER BY id ASC");
    $stmtP->execute(['cid' => $cuestionario_id]);
    $preguntas = $stmtP->fetchAll();

    $aciertos = 0;
    $total_preguntas = count($preguntas);
    $detalles_correccion = []; // ARRAY CLAVE PARA LAS TARJETAS

    foreach ($preguntas as $pregunta) {
        $p_id = $pregunta['id'];
        
        $stmtCorrecta = $pdo->prepare("SELECT * FROM opciones WHERE pregunta_id = ? AND es_correcta = 1");
        $stmtCorrecta->execute([$p_id]);
        $opcion_correcta_bd = $stmtCorrecta->fetch();

        $respuesta_user_id = $respuestas_usuario[$p_id] ?? null;
        $texto_respuesta_user = "No respondida";
        $es_acierto = false;

        if ($respuesta_user_id) {
            $stmtUserOpt = $pdo->prepare("SELECT texto_opcion FROM opciones WHERE id = ?");
            $stmtUserOpt->execute([$respuesta_user_id]);
            $optUser = $stmtUserOpt->fetch();
            if ($optUser) $texto_respuesta_user = $optUser['texto_opcion'];

            if ($opcion_correcta_bd && $respuesta_user_id == $opcion_correcta_bd['id']) {
                $aciertos++;
                $es_acierto = true;
            }
        }

        // Guardamos los datos para pintar las tarjetas luego
        $detalles_correccion[] = [
            'enunciado' => $pregunta['enunciado'],
            'imagen'    => $pregunta['imagen'],
            'tu_respuesta' => $texto_respuesta_user,
            'respuesta_correcta' => $opcion_correcta_bd['texto_opcion'] ?? 'Error datos',
            'es_correcta' => $es_acierto
        ];
    }

    $nota = ($total_preguntas > 0) ? ($aciertos / $total_preguntas) * 10 : 0;
    $nota_final = number_format($nota, 2);

    // Guardar en BBDD
    $stmtIns = $pdo->prepare("INSERT INTO resultados (usuario_id, cuestionario_id, puntuacion, fecha_realizacion) VALUES (:uid, :cid, :nota, NOW())");
    $stmtIns->execute(['uid' => $usuario_id, 'cid' => $cuestionario_id, 'nota' => $nota_final]);

    // Guardar en SESIÓN para que resultado.php lo lea
    $_SESSION['resultado_reciente'] = [
        'nota' => $nota_final,
        'aciertos' => $aciertos,
        'total' => $total_preguntas,
        'detalles' => $detalles_correccion 
    ];
    
    header("Location: ../vistas/resultado.php?id=" . $cuestionario_id);
    exit;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>