<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];

// Verificar si existe la tabla referidos, si no, crearla
$checkTable = $conn->query("SHOW TABLES LIKE 'referidos'");
if ($checkTable->num_rows == 0) {
    $createTable = "CREATE TABLE referidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        referido_id INT NOT NULL,
        fecha_referido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        puntos_ganados INT DEFAULT 0,
        estado ENUM('pendiente', 'confirmado') DEFAULT 'pendiente',
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
        FOREIGN KEY (referido_id) REFERENCES usuarios(id)
    )";
    $conn->query($createTable);
}

// Obtener referidos del usuario
$sql = "SELECT r.*, u.nombre as referido_nombre, u.correo as referido_correo, u.fecha_registro
        FROM referidos r 
        JOIN usuarios u ON r.referido_id = u.id 
        WHERE r.usuario_id = ? 
        ORDER BY r.fecha_referido DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$referidos = $stmt->get_result();

// Calcular estadísticas
$sqlStats = "SELECT 
    COUNT(*) as total_referidos,
    SUM(puntos_ganados) as total_puntos,
    COUNT(CASE WHEN estado = 'confirmado' THEN 1 END) as confirmados
FROM referidos 
WHERE usuario_id = ?";
$stmtStats = $conn->prepare($sqlStats);
$stmtStats->bind_param("i", $userId);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

// Generar código de referido único
$codigoReferido = 'REF' . str_pad($userId, 6, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Referidos - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        <h2>Mis Referidos</h2>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Referidos</h3>
                <p class="stat-number"><?php echo number_format($stats['total_referidos'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Puntos Ganados</h3>
                <p class="stat-number"><?php echo number_format($stats['total_puntos'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Referidos Confirmados</h3>
                <p class="stat-number"><?php echo number_format($stats['confirmados'] ?? 0); ?></p>
            </div>
        </div>
        
        <!-- Código de Referido -->
        <div class="content-section">
            <h3>Tu Código de Referido</h3>
            <div class="referral-code">
                <div class="code-display">
                    <span class="code"><?php echo $codigoReferido; ?></span>
                    <button onclick="copyCode()" class="btn btn-small">Copiar</button>
                </div>
                <p class="code-info">Comparte este código con tus amigos y gana puntos cuando se registren.</p>
            </div>
        </div>
        
        <!-- Lista de Referidos -->
        <div class="content-section">
            <h3>Mis Referidos</h3>
            
            <?php if ($referidos->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Referido</th>
                                <th>Fecha de Registro</th>
                                <th>Puntos Ganados</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($referido = $referidos->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <strong><?php echo htmlspecialchars($referido['referido_nombre']); ?></strong>
                                            <small><?php echo htmlspecialchars($referido['referido_correo']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($referido['fecha_registro'])); ?></td>
                                    <td><?php echo number_format($referido['puntos_ganados']); ?> pts</td>
                                    <td>
                                        <span class="status-badge <?php echo $referido['estado']; ?>">
                                            <?php echo ucfirst($referido['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Aún no tienes referidos.</p>
                    <p>¡Comparte tu código de referido y comienza a ganar puntos!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Cómo Funciona -->
        <div class="content-section">
            <h3>¿Cómo Funciona?</h3>
            <div class="how-it-works">
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Comparte tu código</h4>
                    <p>Comparte tu código de referido con amigos y familiares.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>Se registran</h4>
                    <p>Ellos se registran usando tu código de referido.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Ganas puntos</h4>
                    <p>Recibes puntos por cada referido que se registre y active su cuenta.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script>
    function copyCode() {
        const code = '<?php echo $codigoReferido; ?>';
        navigator.clipboard.writeText(code).then(function() {
            alert('Código copiado al portapapeles: ' + code);
        });
    }
    </script>

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
        margin-bottom: 2rem;
    }
    
    .content-section h3 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }
    
    .referral-code {
        text-align: center;
    }
    
    .code-display {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .code {
        background: var(--light-gray);
        padding: 1rem 2rem;
        border-radius: var(--border-radius);
        font-family: monospace;
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--secondary-color);
        border: 2px solid var(--secondary-color);
    }
    
    .code-info {
        color: var(--text-muted);
        margin: 0;
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
    
    .user-info strong {
        display: block;
        color: var(--text-color);
    }
    
    .user-info small {
        color: var(--text-muted);
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .status-badge.pendiente {
        background: var(--warning-color);
        color: white;
    }
    
    .status-badge.confirmado {
        background: var(--success-color);
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
    }
    
    .empty-state p {
        margin: 0.5rem 0;
    }
    
    .how-it-works {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }
    
    .step {
        text-align: center;
        padding: 1rem;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        background: var(--secondary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0 auto 1rem auto;
    }
    
    .step h4 {
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .step p {
        color: var(--text-muted);
        margin: 0;
    }
    </style>
</body>
</html> 