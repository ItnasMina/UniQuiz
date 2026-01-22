<?php
// UQ Lead Dev: controladores/usuario_registro.php
// Objetivo: Procesar el registro, validar datos y crear el usuario en BBDD.

session_start();
require_once 'conexion.php'; // Usamos la conexión PDO que creamos antes

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitización de entradas
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // 2. Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password) || empty($password_confirm)) {
        $_SESSION['error_registro'] = "Todos los campos son obligatorios.";
        header("Location: ../vistas/registro.php");
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_registro'] = "Las contraseñas no coinciden.";
        header("Location: ../vistas/registro.php");
        exit;
    }

    // 3. Comprobar si el email ya existe en la BBDD
    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        
        if ($stmt->fetch()) {
            $_SESSION['error_registro'] = "Ese correo ya está registrado en UniQuiz.";
            header("Location: ../vistas/registro.php");
            exit;
        }

        // 4. Crear el usuario nuevo
        // Encriptamos la contraseña (requisito de seguridad implícito en login/registro)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Imagen por defecto (apunta a /almacen/ aunque sea virtual por ahora) [cite: 35]
        $foto_default = 'default_user.png'; 

        $sql_insert = "INSERT INTO usuarios (nombre, email, password, foto_perfil, rol, fecha_registro) 
                       VALUES (:nombre, :email, :pass, :foto, 'estudiante', NOW())";
        
        $stmtInsert = $pdo->prepare($sql_insert);
        $ejecucion = $stmtInsert->execute([
            'nombre' => $nombre,
            'email' => $email,
            'pass' => $password_hash,
            'foto' => $foto_default
        ]);

        if ($ejecucion) {
            // 5. Auto-login: Iniciamos sesión directamente para mejorar la UX
            $_SESSION['usuario_id'] = $pdo->lastInsertId();
            $_SESSION['nombre_usuario'] = $nombre;
            $_SESSION['rol'] = 'estudiante';
            $_SESSION['foto_perfil'] = $foto_default;

            // Redirigir al Dashboard
            header("Location: ../vistas/dashboard.php");
            exit;
        } else {
            throw new Exception("No se pudo guardar el usuario.");
        }

    } catch (PDOException $e) {
        // Error técnico (BBDD)
        $_SESSION['error_registro'] = "Error en el sistema: " . $e->getMessage();
        header("Location: ../vistas/registro.php");
        exit;
    } catch (Exception $e) {
        // Error lógico
        $_SESSION['error_registro'] = $e->getMessage();
        header("Location: ../vistas/registro.php");
        exit;
    }

} else {
    // Acceso directo no permitido
    header("Location: ../vistas/registro.php");
    exit;
}
?>  