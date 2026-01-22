<?php
// UQ Lead Dev: vistas/perfil.php
// Objetivo: Edición de perfil con UX mejorada (Foto default y Input bonito).

session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header("Location: login.php"); 
    exit; 
}

$nombre = $_SESSION['nombre_usuario'];

// 1. Lógica Foto por Defecto
// Si no hay foto en sesión o está vacía, usamos la default.
$foto_actual = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : 'default_user.png';

$mensaje = $_SESSION['mensaje'] ?? ''; 
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo">
            <a href="dashboard.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo" class="logo-image">
            </a>
        </div>
        <div class="user-nav">
             <a href="dashboard.php" class="btn btn-back">&larr; Volver al Dashboard</a>
        </div>
    </header>

    <main class="dashboard-content edit-container" style="max-width: 500px;">
        
        <h1 style="text-align: center; margin-bottom: 30px;">Editar Perfil</h1>
        
        <?php if($mensaje): ?>
            <div class="alert" style="text-align: center;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="../controladores/usuario_perfil_update.php" method="POST" enctype="multipart/form-data" class="form-card">
            
            <div style="text-align: center; margin-bottom: 25px;">
                <img src="../almacen/<?php echo htmlspecialchars($foto_actual); ?>" 
                     alt="Foto Actual" 
                     class="profile-preview" 
                     id="preview-img"
                     style="width: 150px; height: 150px; object-fit: cover;">
            </div>

            <div class="form-group">
                <label for="nombre">Nombre de Usuario</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>

            <div class="form-group">
                <label>Cambiar Foto de Perfil</label>
                
                <div class="file-upload-wrapper">
                    <input type="file" id="foto-input" name="foto" accept="image/*" onchange="previewFile()">
                    
                    <label for="foto-input" class="custom-file-upload">
                        📂 Seleccionar nueva imagen
                    </label>
                    
                    <span id="file-name-display">Ningún archivo seleccionado</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full-width" style="margin-top: 20px;">
                Guardar Cambios
            </button>

        </form>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

    <script>
    function previewFile() {
        const input = document.getElementById('foto-input');
        const preview = document.getElementById('preview-img');
        const nameDisplay = document.getElementById('file-name-display');
        
        const file = input.files[0];

        if (file) {
            // Actualizar vista previa
            const reader = new FileReader();
            reader.onloadend = function () {
                preview.src = reader.result;
            }
            reader.readAsDataURL(file);

            // Actualizar texto con el nombre del archivo
            nameDisplay.textContent = file.name;
            nameDisplay.style.color = "#28a745"; // Verde para indicar éxito
            nameDisplay.style.fontWeight = "bold";
        } else {
            nameDisplay.textContent = "Ningún archivo seleccionado";
            nameDisplay.style.color = "#666";
            nameDisplay.style.fontWeight = "normal";
        }
    }
    </script>
</body>
</html>