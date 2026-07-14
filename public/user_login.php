<?php
// Configuramos sesiones seguras antes de arrancar
ini_set('session.cookie_httponly', 1);
session_start();

// Importamos nuestra clase Singleton
require_once __DIR__ . '/../config/database.php';

// Si el usuario ya está logueado, lo expulsamos de esta página y lo enviamos al Feed
if (isset($_SESSION['user_id'])) {
    header("Location: /index.php");
    exit;
}

$error = '';

// Si el formulario ha sido enviado por POST...
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, rellena todos los campos.';
    } else {
        try {
            // Obtenemos la conexión a la base de datos
            $pdo = Database::getInstance();
            
            // Buscamos al usuario por su email usando Sentencias Preparadas (Evita Inyección SQL)
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role, subscription_tier FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            // Verificamos si el usuario existe y si la contraseña licuada coincide con el hash
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // ¡Éxito! Prevenimos ataques de fijación de sesión regenerando el ID
                session_regenerate_id(true);
                
                // Guardamos los datos no sensibles en la sesión del navegador
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['tier'] = $user['subscription_tier'];
                
                // Redirigimos al Feed principal
                header("Location: /index.php");
                exit;
            } else {
                // Mensaje genérico por seguridad
                $error = 'Email o contraseña incorrectos.';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Error interno del servidor. Inténtalo más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UniQuiz</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <style>
        /* Estilos en línea temporales para el MVP */
        .auth-container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .error-msg { color: red; margin-bottom: 15px; }
        .btn-submit { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Iniciar Sesión</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="/user_login.php" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <!-- Evitamos que el usuario tenga que volver a escribir su email si se equivoca en la contraseña -->
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-submit">Entrar</button>
        </form>
        
        <p style="text-align: center; margin-top: 15px;">
            ¿No tienes cuenta? <a href="/user_register.php">Regístrate aquí</a>
        </p>
    </div>
</body>
</html>