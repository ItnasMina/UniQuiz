<?php
// UQ Lead Dev: controladores/cuestionario_corregir.php
// Objetivo: Calcular nota, guardar en histórico y mostrar resultado.

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/dashboard.php");
    exit;
}

$cuestionario_id = $_POST['cuestionario_id'];
$respuestas_usuario = $_POST['respuestas'] ?? []; // Array [pregunta_id => opcion_id]
$usuario_id = $_SESSION['usuario_id'];

try {
    // 1. Obtener todas las preguntas y sus opciones CORRECTAS de este cuestionario
    // Hacemos un JOIN para traer solo las opciones que valen 1 (es_correcta = 1)
    $sql = "SELECT p.id as pregunta_id, o.id as opcion_correcta_id 
            FROM preguntas p
            JOIN opciones o ON p.id = o.pregunta_id
            WHERE p.cuestionario_id = :cid AND o.es_correcta = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cid' => $cuestionario_id]);
    $solucionario = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
    // FETCH_KEY_PAIR devuelve un array directo: [pregunta_id => opcion_correcta_id]

    // 2. Calcular Nota
    $total_preguntas = count($solucionario);
    $aciertos = 0;

    if ($total_preguntas > 0) {
        foreach ($solucionario as $p_id => $correcta_id) {
            // Si el usuario respondió a esta pregunta Y la respuesta coincide
            if (isset($respuestas_usuario[$p_id]) && $respuestas_usuario[$p_id] == $correcta_id) {
                $aciertos++;
            }
        }
        
        // Regla de tres para nota sobre 10
        $nota = ($aciertos / $total_preguntas) * 10;
    } else {
        $nota = 0; // Evitar división por cero si el cuestionario no tenía preguntas
    }

    // Formatear a 2 decimales
    $nota_final = number_format($nota, 2);

    // 3. Guardar en la tabla 'resultados' (Entidad Extra)
    $sqlInsert = "INSERT INTO resultados (usuario_id, cuestionario_id, puntuacion, fecha_realizacion) 
                  VALUES (:uid, :cid, :nota, NOW())";
    $stmtIns = $pdo->prepare($sqlInsert);
    $stmtIns->execute([
        'uid' => $usuario_id,
        'cid' => $cuestionario_id,
        'nota' => $nota_final
    ]);

    // 4. Redirigir a la vista de resultados
    // Pasamos datos por URL (o sesión) para mostrarlos
    $_SESSION['resultado_reciente'] = [
        'nota' => $nota_final,
        'aciertos' => $aciertos,
        'total' => $total_preguntas
    ];
    
    header("Location: ../vistas/resultado.php?id=" . $cuestionario_id);
    exit;

} catch (PDOException $e) {
    die("Error al corregir: " . $e->getMessage());
}
?>