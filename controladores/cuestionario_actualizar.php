<?php
// UQ Lead Dev: controladores/cuestionario_actualizar.php
// Objetivo: Procesar la actualización de los datos básicos de un cuestionario.

session_start();
require_once 'conexion.php';

// Seguridad: Usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recogida de datos
    $cuestionario_id = $_POST['cuestionario_id'];
    $usuario_id = $_SESSION['usuario_id'];
    
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Checkbox y Radios
    $es_publico = (isset($_POST['acceso']) && $_POST['acceso'] == '1') ? 1 : 0;
    $es_aleatorio = isset($_POST['random']) ? 1 : 0;
    $num_random = isset($_POST['num_random']) ? (int)$_POST['num_random'] : 10;

    // 2. Validaciones básicas
    if (empty($titulo)) {
        $_SESSION['error_editar'] = "El título no puede estar vacío.";
        header("Location: ../vistas/cuestionario_editar.php?id=" . $cuestionario_id);
        exit;
    }

    try {
        // 3. SEGURIDAD CRÍTICA: Verificar que el cuestionario pertenece al usuario
        // Solo actualizamos si el ID y el USUARIO coinciden.
        $sql = "UPDATE cuestionarios 
                SET titulo = :titulo, 
                    descripcion = :desc, 
                    es_publico = :publico, 
                    es_aleatorio = :random, 
                    num_preguntas_aleatorias = :num 
                WHERE id = :id AND usuario_id = :uid";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            'titulo' => $titulo,
            'desc' => $descripcion,
            'publico' => $es_publico,
            'random' => $es_aleatorio,
            'num' => $num_random,
            'id' => $cuestionario_id,
            'uid' => $usuario_id
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = "Ajustes actualizados correctamente.";
        } else {
            // Si no se afectó ninguna fila, puede ser que no hubo cambios o que no es el dueño.
            // Para UX, asumimos que guardó sin cambios si no saltó error.
            $_SESSION['mensaje'] = "Cuestionario guardado (sin cambios detectados).";
        }

        // 4. Volver a la edición
        header("Location: ../vistas/cuestionario_editar.php?id=" . $cuestionario_id . "&subtab=ajustes");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_editar'] = "Error BBDD: " . $e->getMessage();
        header("Location: ../vistas/cuestionario_editar.php?id=" . $cuestionario_id);
        exit;
    }

} else {
    header("Location: ../vistas/dashboard.php");
    exit;
}
?>