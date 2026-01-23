<?php
// UQ Lead Dev: controladores/perfil_actualizar.php
// Objetivo: Subir foto de perfil a la carpeta 'perfiles'.

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
        
        // Extraer extensión
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Extensiones permitidas
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            
            // 3. Crear nombre único
            $newFileName = 'profile_' . $usuario_id . '_' . md5(time() . $fileName) . '.' . $fileExtension;

            // --- CAMBIO: USAR CARPETA 'PERFILES' ---
            $uploadFileDir = '../perfiles/';
            
            // Crear carpeta si no existe
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            // 4. Mover el archivo
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                
                // 5. Borrar foto anterior (opcional)
                $stmtOld = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
                $stmtOld->execute([$usuario_id]);
                $oldAvatar = $stmtOld->fetchColumn();

                // Solo borramos si existe y no es la por defecto
                if ($oldAvatar && $oldAvatar != 'default_user.png' && file_exists($uploadFileDir . $oldAvatar)) {
                    unlink($uploadFileDir . $oldAvatar);
                }

                // 6. Actualizar Base de Datos
                // Nota: Tu columna en BBDD es 'foto_perfil' o 'avatar'? 
                // En bbdd.sql dice 'foto_perfil', ajustamos la consulta:
                $sql = "UPDATE usuarios SET foto_perfil = :avatar WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['avatar' => $newFileName, 'id' => $usuario_id]);

                // IMPORTANTE: Actualizar sesión
                $_SESSION['foto_perfil'] = $newFileName;

                header("Location: ../vistas/perfil.php?status=success");
                exit;
            } else {
                die("Error al mover el archivo a la carpeta 'perfiles'. Verifica permisos.");
            }
        } else {
            die("Formato no válido. Solo JPG, PNG, GIF y WEBP.");
        }
    } else {
        header("Location: ../vistas/perfil.php?error=no_file");
        exit;
    }

} catch (PDOException $e) {
    die("Error en base de datos: " . $e->getMessage());
}
?>