<?php
// UQ Lead Dev: controladores/perfil_actualizar.php
// Objetivo: Subir foto de perfil y actualizar la base de datos.

session_start();
require_once 'conexion.php';

// 1. Seguridad
if (!isset($_SESSION['usuario_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../vistas/perfil.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    // 2. Verificar si se ha subido un archivo
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileType = $_FILES['avatar']['type'];
        
        // Extraer extensión
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Extensiones permitidas
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            
            // 3. Crear nombre único para evitar conflictos y caché
            // Ejemplo: profile_15_a8f9d.png
            $newFileName = 'profile_' . $usuario_id . '_' . md5(time() . $fileName) . '.' . $fileExtension;

            // Directorio destino (carpeta 'perfiles' en la raíz)
            $uploadFileDir = '../perfiles/';
            
            // Crear carpeta si no existe
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            // 4. Mover el archivo
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                
                // 5. Borrar foto anterior para no llenar el servidor (Opcional pero recomendado)
                // Primero buscamos la foto vieja
                $stmtOld = $pdo->prepare("SELECT avatar FROM usuarios WHERE id = ?");
                $stmtOld->execute([$usuario_id]);
                $oldAvatar = $stmtOld->fetchColumn();

                if ($oldAvatar && file_exists($uploadFileDir . $oldAvatar)) {
                    unlink($uploadFileDir . $oldAvatar);
                }

                // 6. Actualizar Base de Datos
                $sql = "UPDATE usuarios SET avatar = :avatar WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['avatar' => $newFileName, 'id' => $usuario_id]);

                // ¡Éxito!
                header("Location: ../vistas/perfil.php?status=success");
                exit;
            } else {
                die("Error al mover el archivo al directorio de destino. Verifica permisos.");
            }
        } else {
            die("Formato de archivo no válido. Solo JPG, PNG, GIF y WEBP.");
        }
    } else {
        // Si no subió foto, redirigimos sin hacer nada (o podrías mostrar error)
        header("Location: ../vistas/perfil.php?error=no_file");
        exit;
    }

} catch (PDOException $e) {
    die("Error en base de datos: " . $e->getMessage());
}
?>