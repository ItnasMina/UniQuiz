<?php
// UQ Lead Dev: controladores/cuestionario_borrar.php
// Objetivo: Eliminar un cuestionario y todo su contenido (Cascade).

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/dashboard.php");
    exit;
}

$cuestionario_id = $_POST['cuestionario_id'];
$usuario_id = $_SESSION['usuario_id'];

try {
    // Solo borramos si el cuestionario pertenece al usuario logueado
    $stmt = $pdo->prepare("DELETE FROM cuestionarios WHERE id = :id AND usuario_id = :uid");
    $stmt->execute(['id' => $cuestionario_id, 'uid' => $usuario_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['mensaje'] = "Cuestionario eliminado correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error: No se pudo borrar (o no eres el propietario).";
    }

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error BBDD: " . $e->getMessage();
}

header("Location: ../vistas/dashboard.php?tab=mis_cuestionarios");
exit;
?>