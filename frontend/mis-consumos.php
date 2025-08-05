<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];

// Obtener historial de consumos (acumulaciones)
$sql = "SELECT * FROM transacciones WHERE usuario_id = ? AND tipo = 'acumulacion' ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$consumos = $stmt->get_result();

// Calcular estadísticas
$sqlStats = "SELECT 
    COUNT(*) as total_consumos,
    SUM(puntos) as total_puntos,
    MAX(fecha) as ultimo_consumo
FROM transacciones 
WHERE usuario_id = ? AND tipo = 'acumulacion'";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->bind_param("i", $userId);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Consumos - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        <h2>Mis Consumos</h2>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total de Consumos</h3>
                <p class="stat-number"><?php echo number_format($stats['total_consumos'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Puntos Acumulados</h3>
                <p class="stat-number"><?php echo number_format($stats['total_puntos'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Último Consumo</h3>
                <p class="stat-number">
                    <?php if ($stats['ultimo_consumo']): ?>
                        <?php echo date('d/m/Y', strtotime($stats['ultimo_consumo'])); ?>
                    <?php else: ?>
                        Sin registros
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Lista de consumos -->
        <div class="content-section">
            <h3>Historial de Consumos</h3>
            
            <?php if ($consumos->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Puntos</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($consumo = $consumos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($consumo['fecha'])); ?></td>
                                    <td class="points-positive">+<?php echo number_format($consumo['puntos']); ?> pts</td>
                                    <td><?php echo htmlspecialchars($consumo['descripcion'] ?: 'Compra en estación'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No tienes registros de consumos aún.</p>
                    <p>Los puntos se acumulan automáticamente con cada compra en nuestras estaciones.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: var(--white);
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        text-align: center;
    }
    
    .stat-card h3 {
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--secondary-color);
        margin: 0;
    }
    
    .content-section {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .content-section h3 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th, td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    
    th {
        background: var(--light-gray);
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .points-positive {
        color: var(--success-color);
        font-weight: bold;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
    }
    
    .empty-state p {
        margin: 0.5rem 0;
    }
    </style>
</body>
</html> 