<?php
// UQ Lead Dev: perfil.php (AGRUPACIÓN INTELIGENTE: MEDIA DIARIA vs MEDIA HORARIA)
session_start();
require_once '../controladores/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// 1. Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$usuario = $stmt->fetch();

// 2. OBTENER HISTORIAL (Ordenado)
$sqlHistorial = "SELECT r.puntuacion, 
                        DATE_FORMAT(r.fecha_realizacion, '%Y-%m-%dT%H:%i:%s') as fecha_iso,
                        c.titulo, 
                        c.id as quest_id
                 FROM resultados r
                 JOIN cuestionarios c ON r.cuestionario_id = c.id
                 WHERE r.usuario_id = :uid
                 ORDER BY r.fecha_realizacion ASC";

$stmtH = $pdo->prepare($sqlHistorial);
$stmtH->execute(['uid' => $usuario_id]);
$historial = $stmtH->fetchAll(PDO::FETCH_ASSOC);

// 3. Lista de Cuestionarios Únicos
$cuestionarios_unicos = [];
foreach ($historial as $h) {
    if (!isset($cuestionarios_unicos[$h['quest_id']])) {
        $cuestionarios_unicos[$h['quest_id']] = $h['titulo'];
    }
}

// Stats generales
$total_tests = count($historial);
$suma_notas = 0;
foreach($historial as $h) $suma_notas += $h['puntuacion'];
$promedio_global = ($total_tests > 0) ? number_format($suma_notas / $total_tests, 2) : "0.00";

$json_historial = json_encode($historial);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | UniQuiz</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
    <link rel="icon" href="../assets/LogoUQ.png" type="image/png">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    
    <style>
        .quiz-filter-container {
            display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;
            background: #f8f9fa; padding: 15px; border-radius: 12px; border: 1px solid #e9ecef;
        }
        .quiz-checkbox-label {
            display: flex; align-items: center; gap: 8px;
            background: white; padding: 8px 15px; border-radius: 20px;
            border: 2px solid #dee2e6; cursor: pointer; transition: all 0.2s;
            font-size: 0.9rem; font-weight: 600; color: #495057; user-select: none;
        }
        .quiz-checkbox-label:hover { border-color: #386DBD; transform: translateY(-2px); }
        .quiz-checkbox-label.selected {
            border-color: var(--chk-color); background-color: var(--chk-bg); color: var(--chk-text);
        }
        .quiz-checkbox-label input { display: none; }
        .color-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; background-color: #ccc; }
        .time-select {
            padding: 8px 15px; border-radius: 8px; border: 2px solid #386DBD;
            background: white; color: #386DBD; font-weight: 600; cursor: pointer; outline: none;
        }
    </style>
</head>
<body class="dashboard-body">

    <header class="main-header private-header small-header">
        <div class="logo"><a href="dashboard.php"><img src="../assets/LogoUQ-w&b.png" alt="Logo" class="logo-image"></a></div>
        <div class="user-nav"><a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a></div>
    </header>
    
    <main class="dashboard-content" style="max-width: 1000px;">
        
        <div style="text-align: center; margin-bottom: 20px;">
            <?php if (!empty($usuario['avatar'])): ?>
                <img src="../perfiles/<?php echo htmlspecialchars($usuario['avatar']); ?>" class="profile-preview" style="width: 150px; height: 150px;">
            <?php else: ?>
                <div class="profile-preview" style="width: 150px; height: 150px; background: #ddd; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 3rem;">👤</div>
            <?php endif; ?>
            <h1 style="margin-top: 15px; color: var(--primary);"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            <p style="color: var(--text-light);"><?php echo htmlspecialchars($usuario['email']); ?></p>
        </div>

        <div class="profile-tabs">
            <button class="profile-tab-btn active" onclick="openTab(event, 'tab-datos')">Mis Datos</button>
            <button class="profile-tab-btn" onclick="openTab(event, 'tab-stats')">📈 Comparativa</button>
        </div>

        <div id="tab-datos" class="tab-content active">
            <div class="form-card" style="max-width: 600px; margin: 0 auto;">
                <form action="../controladores/perfil_actualizar.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Cambiar Foto de Perfil</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewFile()">
                            <label for="avatar" class="custom-file-upload">📂 Seleccionar Imagen</label>
                            <span id="file-name" style="display:block; margin-top:10px; color:#666;">Ningún archivo seleccionado</span>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Actualizar Foto</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="tab-stats" class="tab-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_tests; ?></div>
                    <div class="stat-label">Total Tests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $promedio_global; ?></div>
                    <div class="stat-label">Media Global</div>
                </div>
            </div>

            <div class="form-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="color: #333; margin: 0;">Filtros de Análisis</h3>
                    <select id="timeRange" class="time-select" onchange="updateChartData()">
                        <option value="1">⏱️ Últimas 24 Horas</option>
                        <option value="7">Última Semana</option>
                        <option value="30">Último Mes</option>
                        <option value="90">Últimos 3 Meses</option>
                        <option value="all" selected>📅 Todo el historial</option>
                    </select>
                </div>
                
                <div class="quiz-filter-container" id="checkboxArea"></div>

                <div class="chart-wrapper">
                    <canvas id="progressChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer"><p>&copy; 2026 UniQuiz.</p></footer>

    <script>
        // --- UTILIDADES ---
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) tabcontent[i].classList.remove("active");
            tablinks = document.getElementsByClassName("profile-tab-btn");
            for (i = 0; i < tablinks.length; i++) tablinks[i].classList.remove("active");
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
            if(tabName === 'tab-stats' && myChart) updateChartData();
        }
        function previewFile() {
            const input = document.getElementById('avatar');
            const fileName = document.getElementById('file-name');
            if(input.files.length > 0) {
                fileName.textContent = "✅ " + input.files[0].name;
                fileName.style.color = "#28a745";
            }
        }

        // --- LÓGICA GRÁFICA ---
        const rawData = <?php echo $json_historial; ?>;
        const availableQuizzes = <?php echo json_encode($cuestionarios_unicos); ?>;
        let myChart = null;

        const colorPalette = [
            { border: '#386DBD', bg: 'rgba(56, 109, 189, 0.1)' },
            { border: '#e74c3c', bg: 'rgba(231, 76, 60, 0.1)' },
            { border: '#2ecc71', bg: 'rgba(46, 204, 113, 0.1)' },
            { border: '#f1c40f', bg: 'rgba(241, 196, 15, 0.1)' },
            { border: '#9b59b6', bg: 'rgba(155, 89, 182, 0.1)' },
            { border: '#e67e22', bg: 'rgba(230, 126, 34, 0.1)' },
            { border: '#34495e', bg: 'rgba(52, 73, 94, 0.1)' }
        ];

        function getColor(index) { return colorPalette[index % colorPalette.length]; }

        const checkboxContainer = document.getElementById('checkboxArea');
        let quizIndex = 0;
        for (const [id, titulo] of Object.entries(availableQuizzes)) {
            const color = getColor(quizIndex);
            const label = document.createElement('label');
            label.className = 'quiz-checkbox-label selected';
            label.style.setProperty('--chk-color', color.border);
            label.style.setProperty('--chk-bg', color.bg);
            label.style.setProperty('--chk-text', color.border);
            label.innerHTML = `<span class="color-dot" style="background-color: ${color.border}"></span> ${titulo} <input type="checkbox" value="${id}" onchange="toggleDataset(this)" checked>`;
            checkboxContainer.appendChild(label);
            quizIndex++;
        }

        function initChart() {
            const ctx = document.getElementById('progressChart').getContext('2d');
            myChart = new Chart(ctx, {
                type: 'line',
                data: { datasets: [] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 10 },
                        x: { 
                            type: 'time',
                            time: {
                                // Quitamos unit: 'day' para que Chart.js decida solo (días u horas)
                                displayFormats: { 
                                    hour: 'HH:mm',
                                    day: 'dd/MM'
                                },
                                tooltipFormat: 'dd/MM/yyyy HH:mm'
                            },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'bottom' },
                        tooltip: { 
                            mode: 'nearest', 
                            intersect: false,
                            callbacks: {
                                title: function(context) {
                                    const date = new Date(context[0].parsed.x);
                                    const timeRange = document.getElementById('timeRange').value;
                                    
                                    if(timeRange === '1') {
                                        // Muestra hora en modo 24h
                                        return date.toLocaleTimeString("es-ES", {hour: '2-digit', minute:'2-digit'}) + ' (Media Horaria)';
                                    } else {
                                        // Muestra día en modo 7d, 30d, etc.
                                        return date.toLocaleDateString("es-ES") + ' (Media Diaria)';
                                    }
                                }
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.3,
                            borderJoinStyle: 'round'
                        }
                    }
                }
            });
            updateChartData();
        }

        function updateChartData() {
            const timeRange = document.getElementById('timeRange').value;
            const now = new Date();
            let minDate = null; 

            // 1. Establecer el rango de tiempo (Zoom)
            if (timeRange !== 'all') {
                const days = parseInt(timeRange);
                const cutDate = new Date();
                cutDate.setDate(now.getDate() - days); 
                minDate = cutDate.getTime();
            } else {
                if (rawData.length > 0) {
                    minDate = new Date(rawData[0].fecha_iso).getTime();
                    minDate = minDate - (24*60*60*1000); 
                } else {
                    minDate = now.getTime();
                }
            }
            if(isNaN(minDate)) minDate = now.getTime();

            myChart.options.scales.x.min = minDate;
            myChart.options.scales.x.max = now.getTime();

            // 2. Definir modo de agrupación
            // Si es '1' (24h) -> Agrupamos por Hora.
            // Si es cualquier otro ('7', '30', '90', 'all') -> Agrupamos por Día.
            const groupMode = (timeRange === '1') ? 'hourly' : 'daily';

            const checkboxes = document.querySelectorAll('.quiz-filter-container input[type="checkbox"]');
            const newDatasets = [];

            checkboxes.forEach((cb, index) => {
                if (!cb.checked) return;

                const qId = cb.value;
                const qTitle = availableQuizzes[qId];
                const color = getColor(index);

                // Filtrar datos brutos dentro del rango
                let filteredData = rawData
                    .filter(r => r.quest_id == qId)
                    .filter(r => new Date(r.fecha_iso).getTime() >= minDate);

                let chartData = [];

                // 3. Lógica de Agrupación (Media)
                const groupedData = {};

                filteredData.forEach(r => {
                    let key;
                    if (groupMode === 'hourly') {
                        // Cortamos en la 'T' y luego en ':' para obtener YYYY-MM-DDTHH
                        // formato fecha_iso: 2023-10-25T14:30:00
                        // key resultante: 2023-10-25T14
                        key = r.fecha_iso.split(':')[0]; 
                    } else {
                        // Cortamos en la 'T' para obtener YYYY-MM-DD
                        key = r.fecha_iso.split('T')[0];
                    }

                    if (!groupedData[key]) { groupedData[key] = { sum: 0, count: 0 }; }
                    groupedData[key].sum += parseFloat(r.puntuacion);
                    groupedData[key].count++;
                });

                // Convertir grupos a puntos {x, y}
                chartData = Object.keys(groupedData).map(keyStr => {
                    const avg = groupedData[keyStr].sum / groupedData[keyStr].count;
                    
                    // Si es hora, añadimos :00:00 para que el Date lo parsee bien como hora exacta
                    let dateStr = keyStr;
                    if(groupMode === 'hourly') dateStr += ":00:00"; 

                    return { 
                        x: new Date(dateStr).getTime(), 
                        y: parseFloat(avg.toFixed(2)) 
                    };
                });

                if (chartData.length > 0) {
                    newDatasets.push({
                        label: qTitle,
                        data: chartData,
                        borderColor: color.border,
                        backgroundColor: color.bg,
                        borderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: false,
                        cubicInterpolationMode: 'monotone'
                    });
                }
            });

            myChart.data.datasets = newDatasets;
            myChart.update();
        }

        function toggleDataset(checkbox) {
            const label = checkbox.parentElement;
            if (checkbox.checked) label.classList.add('selected');
            else label.classList.remove('selected');
            updateChartData();
        }

        document.addEventListener("DOMContentLoaded", initChart);
    </script>
</body>
</html>