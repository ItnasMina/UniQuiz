<?php
// UQ Lead Dev: controladores/pregunta_borrar.php
// Objetivo: Eliminar una pregunta, su imagen y sus opciones de forma limpia.

session_start();
require_once 'conexion.php';

// 1. Verificación básica de seguridad
if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    // Si intentan entrar escribiendo la URL o sin login, fuera.
    header("Location: ../vistas/dashboard.php");
    exit;
}

// Recogemos los datos del formulario (que vienen ocultos)
$pregunta_id = $_POST['id'];
$cuestionario_id = $_POST['cid'];
$usuario_id = $_SESSION['usuario_id'];

try {
    // 2. Verificar PROPIEDAD (Crucial: ¿Es esta pregunta de un cuestionario TUYO?)
    // Hacemos un JOIN para conectar Pregunta -> Cuestionario -> Usuario
    $sql = "SELECT p.id, p.imagen 
            FROM preguntas p
            JOIN cuestionarios c ON p.cuestionario_id = c.id
            WHERE p.id = :pid AND c.usuario_id = :uid";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pid' => $pregunta_id, 'uid' => $usuario_id]);
    $pregunta = $stmt->fetch();

    if ($pregunta) {
        // --- ES TUYA, PROCEDEMOS A BORRAR ---

        // A) Borrar la imagen física del servidor (si tiene)
        if (!empty($pregunta['imagen'])) {
            $ruta_imagen = "../almacen/" . $pregunta['imagen'];
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen); // unlink() borra el archivo
            }
        }

        // B) Borrar las opciones asociadas (Limpieza manual por si no hay CASCADE en BBDD)
        $stmtOpt = $pdo->prepare("DELETE FROM opciones WHERE pregunta_id = ?");
        $stmtOpt->execute([$pregunta_id]);

        // C) Borrar la pregunta
        $stmtDel = $pdo->prepare("DELETE FROM preguntas WHERE id = ?");
        $stmtDel->execute([$pregunta_id]);

    } else {
        // Si no se encuentra la pregunta o no es tuya
        die("Error: No tienes permisos para borrar esta pregunta o no existe.");
    }

    // 3. Redirigir de vuelta al listado
    header("Location: ../vistas/cuestionario_preguntas.php?id=" . $cuestionario_id);
    exit;

} catch (PDOException $e) {
    // Si falla la base de datos (ej: claves foráneas), mostramos el error
    die("Error en base de datos al borrar: " . $e->getMessage());
}
?>