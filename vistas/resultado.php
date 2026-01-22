<?php
// UQ Lead Dev: resultado.php (VERSIÓN FINAL: SVG GRANDE + GRADIENTE DE COLOR)
session_start();
require_once '../controladores/conexion.php';

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['resultado_reciente'])) {
    header("Location: dashboard.php");
    exit;
}

$resultado = $_SESSION['resultado_reciente'];
$detalles = $resultado['detalles'] ?? [];

$cuestionario_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT titulo FROM cuestionarios WHERE id = ?");
$stmt->execute([$cuestionario_id]);
$cuestionario = $stmt->fetch();

$nota = floatval($resultado['nota']); // Nota real (ej: 7.50)

// Mensajes estáticos (El color del círculo lo manejará JS dinámicamente)
if ($nota >= 9) {
    $mensaje = "¡Excelente trabajo!";
} elseif ($nota >= 5) {
    $mensaje = "¡Enhorabuena! Has aprobado.";
} else {
    $mensaje = "Vaya, toca repasar un poco más.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo"><a href="dashboard.php"><img src="../assets/LogoUQ-w&b.png" alt="Logo" class="logo-image"></a></div>
        <div class="user-nav"><a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a></div>
    </header>
    
    <main class="dashboard-content" style="max-width: 800px;">
        
        <div class="result-card" style="text-align: center; margin-bottom: 40px;">
            <h1 style="color: #386DBD; margin-bottom: 10px;"><?php echo htmlspecialchars($cuestionario['titulo']); ?></h1>
            <p style="color: #6c757d; margin-bottom: 30px;">Resultados del Test</p>
            
            <div class="score-container">
                <svg viewBox="0 0 36 36" class="circular-chart">
                    <path class="circle-bg"
                        d="M18 2.0845
                           a 15.9155 15.9155 0 0 1 0 31.831
                           a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                    <path class="circle-progress"
                        id="progress-ring"
                        d="M18 2.0845
                           a 15.9155 15.9155 0 0 1 0 31.831
                           a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                </svg>
                
                <div class="score-text-container">
                    <span class="score-value" id="score-counter">0.00</span>
                    <span class="score-label">Puntos</span>
                </div>
            </div>
            <h2 id="mensaje-nota" style="margin-bottom: 10px; color: #333;"><?php echo $mensaje; ?></h2>
            <p style="color: #666;">Has acertado <strong><?php echo $resultado['aciertos']; ?></strong> de <strong><?php echo $resultado['total']; ?></strong> preguntas.</p>
            
            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center;">
                 <a href="cuestionario_realizar.php?id=<?php echo $cuestionario_id; ?>" class="btn btn-secondary">Intentar de nuevo</a>
                 <a href="dashboard.php" class="btn btn-primary">Volver al Dashboard</a>
            </div>
        </div>

        <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px;">
            Revisión de Respuestas
        </h3>
        
        <?php if (empty($detalles)): ?>
            <div class="alert">⚠️ No hay detalles disponibles.</div>
        <?php else: ?>
            <div class="review-container">
                <?php foreach ($detalles as $idx => $item): ?>
                    <div class="correction-card <?php echo $item['es_correcta'] ? 'correction-correct' : 'correction-wrong'; ?>">
                        <div class="correction-header">
                            <span class="q-number">Pregunta <?php echo $idx + 1; ?></span>
                            <?php if($item['es_correcta']): ?>
                                <span class="badge-success">✓ Correcta</span>
                            <?php else: ?>
                                <span class="badge-danger">✕ Incorrecta</span>
                            <?php endif; ?>
                        </div>
                        <p class="q-text"><?php echo htmlspecialchars($item['enunciado']); ?></p>
                        <?php if(!empty($item['imagen'])): ?>
                            <img src="../almacen/<?php echo htmlspecialchars($item['imagen']); ?>" class="review-img">
                        <?php endif; ?>
                        <div class="correction-details">
                            <div class="user-choice">
                                <small>Tu respuesta:</small><br>
                                <strong><?php echo htmlspecialchars($item['tu_respuesta']); ?></strong>
                            </div>
                            <?php if(!$item['es_correcta']): ?>
                                <div class="correct-choice">
                                    <small>Solución correcta:</small><br>
                                    <strong><?php echo htmlspecialchars($item['respuesta_correcta']); ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <footer class="main-footer"><p>&copy; 2026 UniQuiz.</p></footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const notaFinal = <?php echo $nota; ?>; 
        const circle = document.getElementById('progress-ring');
        const counter = document.getElementById('score-counter');
        const mensajeTitulo = document.getElementById('mensaje-nota');

        // --- FUNCIÓN MÁGICA DE COLOR HSL ---
        // Mapea una nota (0-10) a un matiz HSL (0 Rojo -> 120 Verde)
        function getColorForScore(score) {
            // Asegurar que está entre 0 y 10
            score = Math.max(0, Math.min(10, score));
            // Calcular el matiz (Hue). 0 es rojo, 120 es verde.
            const hue = (score / 10) * 120; 
            // Devolvemos el color en formato HSL (Hue, Saturation%, Lightness%)
            // Usamos 80% saturación y 45% luminosidad para colores vibrantes pero legibles
            return `hsl(${hue}, 80%, 45%)`;
        }

        // 1. Iniciar animación del círculo (llenado)
        setTimeout(() => {
            const porcentaje = notaFinal * 10; 
            circle.style.strokeDasharray = `${porcentaje}, 100`;
        }, 100);

        // 2. Iniciar animación del contador y el color
        let start = 0;
        const duration = 1500; // 1.5 segundos
        const startTime = performance.now();

        function updateAnimation(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3); // Efecto de frenado
            
            // Valor actual del número en este frame
            const currentScore = start + (notaFinal - start) * easeOut;
            
            // Actualizar el texto del número
            counter.innerText = currentScore.toFixed(2);
            
            // --- CALCULAR Y APLICAR COLOR DINÁMICO ---
            const dynamicColor = getColorForScore(currentScore);
            
            // Aplicar color al círculo
            circle.style.stroke = dynamicColor;
            // Aplicar color al texto del número
            counter.style.color = dynamicColor;
            // Aplicar color al título del mensaje (opcional, queda bien)
            mensajeTitulo.style.color = dynamicColor;

            if (progress < 1) {
                requestAnimationFrame(updateAnimation);
            } else {
                // Asegurar valores finales exactos al terminar
                counter.innerText = notaFinal.toFixed(2);
                const finalColor = getColorForScore(notaFinal);
                circle.style.stroke = finalColor;
                counter.style.color = finalColor;
                mensajeTitulo.style.color = finalColor;
            }
        }

        requestAnimationFrame(updateAnimation);
    });
    </script>

</body>
</html>