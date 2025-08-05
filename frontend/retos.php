<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];

// Verificar si existe la tabla retos, si no, crearla
$checkTable = $conn->query("SHOW TABLES LIKE 'retos'");
if ($checkTable->num_rows == 0) {
    $createTable = "CREATE TABLE retos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(100) NOT NULL,
        descripcion TEXT,
        puntos_recompensa INT NOT NULL,
        tipo ENUM('diario', 'semanal', 'mensual', 'especial') DEFAULT 'diario',
        estado BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTable);
    
    // Insertar retos de ejemplo
    $retosEjemplo = [
        ['Visita Diaria', 'Inicia sesión todos los días', 10, 'diario'],
        ['Primera Compra', 'Realiza tu primera compra', 50, 'especial'],
        ['Compra Semanal', 'Realiza al menos 3 compras esta semana', 100, 'semanal'],
        ['Referido', 'Invita a un amigo a registrarse', 200, 'especial'],
        ['Canje', 'Canjea tu primer producto', 75, 'especial']
    ];
    
    $stmt = $conn->prepare("INSERT INTO retos (titulo, descripcion, puntos_recompensa, tipo) VALUES (?, ?, ?, ?)");
    foreach ($retosEjemplo as $reto) {
        $stmt->bind_param("ssis", $reto[0], $reto[1], $reto[2], $reto[3]);
        $stmt->execute();
    }
}

// Verificar si existe la tabla retos_usuarios, si no, crearla
$checkTableUser = $conn->query("SHOW TABLES LIKE 'retos_usuarios'");
if ($checkTableUser->num_rows == 0) {
    $createTableUser = "CREATE TABLE retos_usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        reto_id INT NOT NULL,
        completado BOOLEAN DEFAULT FALSE,
        fecha_completado TIMESTAMP NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
        FOREIGN KEY (reto_id) REFERENCES retos(id)
    )";
    $conn->query($createTableUser);
}

// Obtener todos los retos
$sql = "SELECT r.*, 
        CASE WHEN ru.completado IS NULL THEN FALSE ELSE ru.completado END as completado,
        ru.fecha_completado
        FROM retos r 
        LEFT JOIN retos_usuarios ru ON r.id = ru.reto_id AND ru.usuario_id = ?
        WHERE r.estado = TRUE
        ORDER BY r.tipo, r.puntos_recompensa DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$retos = $stmt->get_result();

// Calcular estadísticas
$sqlStats = "SELECT 
    COUNT(*) as total_retos,
    COUNT(CASE WHEN ru.completado = TRUE THEN 1 END) as completados,
    SUM(CASE WHEN ru.completado = TRUE THEN r.puntos_recompensa ELSE 0 END) as puntos_ganados
FROM retos r 
LEFT JOIN retos_usuarios ru ON r.id = ru.reto_id AND ru.usuario_id = ?
WHERE r.estado = TRUE";
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
    <title>Retos - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        <h2>Retos y Misiones</h2>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Retos</h3>
                <p class="stat-number"><?php echo number_format($stats['total_retos'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Completados</h3>
                <p class="stat-number"><?php echo number_format($stats['completados'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Puntos Ganados</h3>
                <p class="stat-number"><?php echo number_format($stats['puntos_ganados'] ?? 0); ?></p>
            </div>
        </div>
        
        <!-- Retos por Categoría -->
        <div class="challenges-section">
            <h3>Retos Diarios</h3>
            <div class="challenges-grid">
                <?php 
                $retos->data_seek(0);
                $hayRetosDiarios = false;
                while ($reto = $retos->fetch_assoc()): 
                    if ($reto['tipo'] === 'diario'):
                        $hayRetosDiarios = true;
                ?>
                    <div class="challenge-card <?php echo $reto['completado'] ? 'completed' : ''; ?>">
                        <div class="challenge-header">
                            <h4><?php echo htmlspecialchars($reto['titulo']); ?></h4>
                            <span class="points"><?php echo number_format($reto['puntos_recompensa']); ?> pts</span>
                        </div>
                        <p><?php echo htmlspecialchars($reto['descripcion']); ?></p>
                        <div class="challenge-status">
                            <?php if ($reto['completado']): ?>
                                <span class="status completed">✓ Completado</span>
                                <small><?php echo date('d/m/Y', strtotime($reto['fecha_completado'])); ?></small>
                            <?php else: ?>
                                <span class="status pending">⏳ Pendiente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endif;
                endwhile; 
                if (!$hayRetosDiarios): ?>
                    <p class="no-challenges">No hay retos diarios disponibles.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="challenges-section">
            <h3>Retos Semanales</h3>
            <div class="challenges-grid">
                <?php 
                $retos->data_seek(0);
                $hayRetosSemanales = false;
                while ($reto = $retos->fetch_assoc()): 
                    if ($reto['tipo'] === 'semanal'):
                        $hayRetosSemanales = true;
                ?>
                    <div class="challenge-card <?php echo $reto['completado'] ? 'completed' : ''; ?>">
                        <div class="challenge-header">
                            <h4><?php echo htmlspecialchars($reto['titulo']); ?></h4>
                            <span class="points"><?php echo number_format($reto['puntos_recompensa']); ?> pts</span>
                        </div>
                        <p><?php echo htmlspecialchars($reto['descripcion']); ?></p>
                        <div class="challenge-status">
                            <?php if ($reto['completado']): ?>
                                <span class="status completed">✓ Completado</span>
                                <small><?php echo date('d/m/Y', strtotime($reto['fecha_completado'])); ?></small>
                            <?php else: ?>
                                <span class="status pending">⏳ Pendiente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endif;
                endwhile; 
                if (!$hayRetosSemanales): ?>
                    <p class="no-challenges">No hay retos semanales disponibles.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="challenges-section">
            <h3>Retos Especiales</h3>
            <div class="challenges-grid">
                <?php 
                $retos->data_seek(0);
                $hayRetosEspeciales = false;
                while ($reto = $retos->fetch_assoc()): 
                    if ($reto['tipo'] === 'especial'):
                        $hayRetosEspeciales = true;
                ?>
                    <div class="challenge-card <?php echo $reto['completado'] ? 'completed' : ''; ?>">
                        <div class="challenge-header">
                            <h4><?php echo htmlspecialchars($reto['titulo']); ?></h4>
                            <span class="points"><?php echo number_format($reto['puntos_recompensa']); ?> pts</span>
                        </div>
                        <p><?php echo htmlspecialchars($reto['descripcion']); ?></p>
                        <div class="challenge-status">
                            <?php if ($reto['completado']): ?>
                                <span class="status completed">✓ Completado</span>
                                <small><?php echo date('d/m/Y', strtotime($reto['fecha_completado'])); ?></small>
                            <?php else: ?>
                                <span class="status pending">⏳ Pendiente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endif;
                endwhile; 
                if (!$hayRetosEspeciales): ?>
                    <p class="no-challenges">No hay retos especiales disponibles.</p>
                <?php endif; ?>
            </div>
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
    
    .challenges-section {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }
    
    .challenges-section h3 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--secondary-color);
        padding-bottom: 0.5rem;
    }
    
    .challenges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
    }
    
    .challenge-card {
        background: var(--light-gray);
        padding: 1.5rem;
        border-radius: var(--border-radius);
        border-left: 4px solid var(--secondary-color);
        transition: var(--transition);
    }
    
    .challenge-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .challenge-card.completed {
        border-left-color: var(--success-color);
        background: #f8fff8;
    }
    
    .challenge-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .challenge-header h4 {
        color: var(--primary-color);
        margin: 0;
        font-size: 1.1rem;
    }
    
    .challenge-header .points {
        background: var(--secondary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.9rem;
    }
    
    .challenge-card p {
        color: var(--text-color);
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    
    .challenge-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .status {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .status.pending {
        background: var(--warning-color);
        color: white;
    }
    
    .status.completed {
        background: var(--success-color);
        color: white;
    }
    
    .challenge-status small {
        color: var(--text-muted);
        font-size: 0.8rem;
    }
    
    .no-challenges {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
        grid-column: 1 / -1;
    }
    </style>
</body>
</html> 