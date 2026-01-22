<?php
// UQ Lead Dev: controladores/pregunta_borrar.php
// Objetivo: Eliminar una pregunta específica.

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/dashboard.php");
    exit;
}

$pregunta_id = $_POST['pregunta_id'];
$cuestionario_id = $_POST['cuestionario_id']; // Para volver al sitio correcto
$usuario_id = $_SESSION['usuario_id'];

try {
    // Verificamos propiedad haciendo JOIN con cuestionarios
    // "Borra la pregunta X solo si su cuestionario Y pertenece al usuario Z"
    $sql = "DELETE p FROM preguntas p 
            INNER JOIN cuestionarios c ON p.cuestionario_id = c.id
            WHERE p.id = :pid AND c.usuario_id = :uid";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pid' => $pregunta_id, 'uid' => $usuario_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['mensaje'] = "Pregunta eliminada.";
        
        // Opcional: Borrar la imagen del servidor si existía (Limpieza)
        // Para esto habríamos necesitado hacer un SELECT antes del DELETE.
        // Por simplicidad y tiempo, lo dejamos así (los archivos huérfanos no rompen la web).
    } else {
        $_SESSION['mensaje'] = "No se pudo eliminar la pregunta.";
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error BBDD: " . $e->getMessage();
}

// Volver a la edición del cuestionario, pestaña preguntas
header("Location: ../vistas/cuestionario_editar.php?id=" . $cuestionario_id . "&subtab=preguntas");
exit;
?>