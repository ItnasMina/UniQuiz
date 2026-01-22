<?php
// UQ Lead Dev: controladores/cuestionario_guardar.php
// Objetivo: Recibir datos POST y crear un nuevo registro en la tabla 'cuestionarios'.

session_start();
require_once 'conexion.php';

// Seguridad: Solo usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recogida y sanitización de datos
    $usuario_id = $_SESSION['usuario_id'];
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Checkbox y Radios (convertir a 1 o 0 para la BBDD)
    $es_publico = (isset($_POST['acceso']) && $_POST['acceso'] == '1') ? 1 : 0;
    $es_aleatorio = isset($_POST['random']) ? 1 : 0;
    $num_random = isset($_POST['num_random']) ? (int)$_POST['num_random'] : 10;

    // 2. Validación simple
    if (empty($titulo)) {
        $_SESSION['error_crear'] = "El título es obligatorio para crear un cuestionario.";
        header("Location: ../vistas/cuestionario_crear.php");
        exit;
    }

    try {
        // 3. Inserción en Base de Datos
        $sql = "INSERT INTO cuestionarios 
                (usuario_id, titulo, descripcion, es_publico, es_aleatorio, num_preguntas_aleatorias, fecha_creacion) 
                VALUES (:uid, :titulo, :desc, :publico, :random, :num, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'uid' => $usuario_id,
            'titulo' => $titulo,
            'desc' => $descripcion,
            'publico' => $es_publico,
            'random' => $es_aleatorio,
            'num' => $num_random
        ]);

        // 4. Redirección inteligente
        // Obtenemos el ID del cuestionario recién creado
        $nuevo_id = $pdo->lastInsertId();
        
        $_SESSION['mensaje'] = "¡Cuestionario creado con éxito! Ahora añade las preguntas.";
        
        // Lo mandamos a la vista de edición (pestaña preguntas) para que siga trabajando
        header("Location: ../vistas/cuestionario_editar.php?id=" . $nuevo_id . "&subtab=preguntas");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_crear'] = "Error al guardar en base de datos: " . $e->getMessage();
        header("Location: ../vistas/cuestionario_crear.php");
        exit;
    }

} else {
    // Si intenta entrar directamente sin POST
    header("Location: ../vistas/dashboard.php");
    exit;
}
?>