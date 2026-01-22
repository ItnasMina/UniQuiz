<?php
// UQ Lead Dev: controladores/usuario_login.php
// Objetivo: Procesar el formulario de login, validar credenciales e iniciar sesión.

session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recoger y limpiar datos
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? ''; // La contraseña no se sanea, se verifica tal cual

    // 2. Validaciones básicas
    if (empty($email) || empty($password)) {
        $_SESSION['error_login'] = "Por favor, rellena todos los campos.";
        header("Location: ../vistas/login.php");
        exit;
    }

    try {
        // 3. Consulta segura (Prepared Statement)
        $sql = "SELECT id, nombre, password, rol, foto_perfil FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        // 4. Verificar contraseña
        // NOTA: Si usaste el hash de prueba del script SQL anterior, la contraseña es '12345678'
        if ($usuario && password_verify($password, $usuario['password'])) {
            
            // ¡Login Éxitoso! Guardamos datos críticos en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['foto_perfil'] = $usuario['foto_perfil'];

            // Redirigir al Dashboard
            header("Location: ../vistas/dashboard.php");
            exit;

        } else {
            // Login fallido
            $_SESSION['error_login'] = "Usuario o contraseña incorrectos.";
            header("Location: ../vistas/login.php");
            exit;
        }

    } catch (PDOException $e) {
        // Error de servidor
        $_SESSION['error_login'] = "Error en el sistema. Inténtalo más tarde.";
        // Loguear error real internamente: error_log($e->getMessage());
        header("Location: ../vistas/login.php");
        exit;
    }

} else {
    // Si alguien intenta entrar directamente a este archivo sin enviar POST
    header("Location: ../vistas/login.php");
    exit;
}