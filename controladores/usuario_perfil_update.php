<?php
// UQ Lead Dev: controladores/usuario_perfil_update.php
// Objetivo: Actualizar nombre y foto de perfil del usuario.

session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/dashboard.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    // 1. Obtener foto actual por si hay que borrarla o mantenerla
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario_actual = $stmt->fetch();
    $nombre_foto = $usuario_actual['foto_perfil'];

    // 2. Procesar Nueva Imagen (si la hay)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $permitidas)) {
            $carpeta = '../almacen/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

            // Borrar foto vieja si no es la default
            if ($nombre_foto && $nombre_foto != 'default_user.png' && file_exists($carpeta . $nombre_foto)) {
                unlink($carpeta . $nombre_foto);
            }

            // Guardar nueva
            $nuevo_nombre = uniqid('profile_') . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $carpeta . $nuevo_nombre)) {
                $nombre_foto = $nuevo_nombre;
                // Actualizar sesión al momento para ver el cambio ya
                $_SESSION['foto_perfil'] = $nombre_foto; 
            }
        }
    }

    // 3. Actualizar BBDD
    $sql = "UPDATE usuarios SET nombre = :nom, foto_perfil = :foto WHERE id = :id";
    $stmtUpd = $pdo->prepare($sql);
    $stmtUpd->execute(['nom' => $nombre, 'foto' => $nombre_foto, 'id' => $usuario_id]);

    // Actualizar nombre en sesión
    $_SESSION['nombre_usuario'] = $nombre;
    $_SESSION['mensaje'] = "Perfil actualizado correctamente.";

} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al actualizar perfil.";
}

header("Location: ../vistas/perfil.php");
exit;
?>