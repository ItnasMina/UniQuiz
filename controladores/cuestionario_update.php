<?php
// UQ Lead Dev: controladores/cuestionario_update.php
// Objetivo: Procesar la edición de los datos generales del cuestionario.

session_start();
require_once 'conexion.php';

// 1. Seguridad básica
if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/dashboard.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$cuestionario_id = $_POST['cuestionario_id'];
$titulo = trim($_POST['titulo']);
$descripcion = trim($_POST['descripcion']);
$es_publico = isset($_POST['es_publico']) ? intval($_POST['es_publico']) : 0;

// (Opcional) Si en el futuro activas el checkbox "random", lo recoges así:
// $random = isset($_POST['random']) ? 1 : 0;

try {
    // 2. Verificar que el cuestionario pertenece al usuario antes de actualizar
    // Esto evita que alguien cambie el ID en el HTML y edite el cuestionario de otro.
    $checkSql = "SELECT id FROM cuestionarios WHERE id = :id AND usuario_id = :uid";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['id' => $cuestionario_id, 'uid' => $usuario_id]);

    if ($checkStmt->rowCount() > 0) {
        // 3. Es tuyo, procedemos a actualizar
        $sql = "UPDATE cuestionarios 
                SET titulo = :titulo, 
                    descripcion = :desc, 
                    es_publico = :publico 
                    -- , es_random = :rnd  <-- Descomentar si usas la columna random
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'titulo' => $titulo,
            'desc' => $descripcion,
            'publico' => $es_publico,
            // 'rnd' => $random,
            'id' => $cuestionario_id
        ]);

        // 4. Mensaje de éxito (Opcional: puedes usar sesiones para mostrar alertas)
        // $_SESSION['mensaje'] = "Cuestionario actualizado correctamente";

        // 5. Redirigir de vuelta a la edición
        header("Location: ../vistas/cuestionario_editar.php?id=" . $cuestionario_id . "&status=success");
        exit;

    } else {
        // No es tuyo o no existe
        header("Location: ../vistas/dashboard.php?error=acceso_denegado");
        exit;
    }

} catch (PDOException $e) {
    die("Error al actualizar: " . $e->getMessage());
}
?>