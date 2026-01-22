<?php
// UQ Lead Dev: resultado.php
// Objetivo: Mostrar la nota obtenida tras realizar un test.

session_start();
require_once '../controladores/conexion.php';

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['resultado_reciente'])) {
    header("Location: dashboard.php");
    exit;
}

// Recuperar datos calculados en el controlador
$resultado = $_SESSION['resultado_reciente'];
// Limpiamos la sesión para que si recarga no se duplique nada raro (opcional)
// unset($_SESSION['resultado_reciente']); 

$cuestionario_id = $_GET['id'];

// Obtener título del cuestionario para mostrarlo
$stmt = $pdo->prepare("SELECT titulo FROM cuestionarios WHERE id = ?");
$stmt->execute([$cuestionario_id]);
$cuestionario = $stmt->fetch();

$nota = floatval($resultado['nota']);
$color_nota = ($nota >= 5) ? '#28a745' : '#dc3545'; // Verde si aprueba, Rojo si suspende
$mensaje = ($nota >= 5) ? "¡Enhorabuena! Has aprobado." : "Vaya... toca estudiar un poco más.";
if ($nota == 10) $mensaje = "¡INCREÍBLE! Puntuación perfecta.";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
    <style>
        .result-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 50px auto;
        }
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 8px solid <?php echo $color_nota; ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: #333;
            margin: 20px auto;
        }
        .score-details {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo">
            <a href="dashboard.php">
                <img src="../assets/LogoUQ-w&b.png" alt="Logo UniQuiz" class="logo-image">
            </a>
        </div>
    </header>
    
    <main class="dashboard-content">
        
        <div class="result-card">
            <h1>Resultados del Test</h1>
            <h3 style="color: #555;"><?php echo htmlspecialchars($cuestionario['titulo']); ?></h3>
            
            <div class="score-circle">
                <?php echo $resultado['nota']; ?>
            </div>

            <h2 style="color: <?php echo $color_nota; ?>;"><?php echo $mensaje; ?></h2>

            <div class="score-details">
                Has acertado <strong><?php echo $resultado['aciertos']; ?></strong> de <strong><?php echo $resultado['total']; ?></strong> preguntas.
            </div>

            <div class="action-buttons" style="justify-content: center; gap: 15px;">
                <a href="cuestionario_realizar.php?id=<?php echo $cuestionario_id; ?>" class="btn btn-secondary">Intentar de nuevo</a>
                <a href="dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
            </div>
        </div>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> UniQuiz.</p>
    </footer>

</body>
</html>