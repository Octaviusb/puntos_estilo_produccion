<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];

// Obtener bonos canjeados por el usuario
$sql = "SELECT tc.*, p.nombre as producto_nombre, p.imagen as producto_imagen 
        FROM tickets_canje tc 
        JOIN productos p ON tc.producto_id = p.id 
        WHERE tc.usuario_id = ? AND tc.estado = 'confirmado'
        ORDER BY tc.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$bonosCanjeados = $stmt->get_result();

// Obtener bonos disponibles (productos tipo bono)
$sqlBonos = "SELECT * FROM productos WHERE nombre LIKE '%bono%' OR nombre LIKE '%bonos%' ORDER BY puntos ASC";
$bonosDisponibles = $conn->query($sqlBonos);

// Calcular estadísticas
$sqlStats = "SELECT 
    COUNT(*) as total_bonos,
    SUM(puntos_usados) as total_puntos
FROM tickets_canje 
WHERE usuario_id = ? AND estado = 'confirmado'";
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
    <title>Mis Bonos - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        <h2>Mis Bonos</h2>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Bonos Canjeados</h3>
                <p class="stat-number"><?php echo number_format($stats['total_bonos'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Puntos Usados</h3>
                <p class="stat-number"><?php echo number_format($stats['total_puntos'] ?? 0); ?></p>
            </div>
        </div>
        
        <!-- Bonos Disponibles -->
        <div class="content-section">
            <h3>Bonos Disponibles</h3>
            <div class="bonos-grid">
                <?php while ($bono = $bonosDisponibles->fetch_assoc()): ?>
                    <div class="bono-card">
                        <img src="<?php echo htmlspecialchars($bono['imagen'] ?: 'img/default-product.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($bono['nombre']); ?>">
                        <div class="bono-info">
                            <h4><?php echo htmlspecialchars($bono['nombre']); ?></h4>
                            <p class="points"><?php echo number_format($bono['puntos']); ?> puntos</p>
                            <a href="catalogo.php" class="btn btn-small">Canjear</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Bonos Canjeados -->
        <div class="content-section">
            <h3>Mis Bonos Canjeados</h3>
            
            <?php if ($bonosCanjeados->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Bono</th>
                                <th>Fecha de Canje</th>
                                <th>Puntos Usados</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($bono = $bonosCanjeados->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <img src="<?php echo htmlspecialchars($bono['producto_imagen'] ?: 'img/default-product.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($bono['producto_nombre']); ?>">
                                            <span><?php echo htmlspecialchars($bono['producto_nombre']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($bono['fecha'])); ?></td>
                                    <td><?php echo number_format($bono['puntos_usados']); ?> pts</td>
                                    <td>
                                        <span class="status-badge confirmado">Confirmado</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Aún no has canjeado ningún bono.</p>
                    <p>¡Revisa los bonos disponibles y canjea tus puntos!</p>
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
        margin-bottom: 2rem;
    }
    
    .content-section h3 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }
    
    .bonos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .bono-card {
        background: var(--light-gray);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--transition);
    }
    
    .bono-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    
    .bono-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    
    .bono-info {
        padding: 1rem;
        text-align: center;
    }
    
    .bono-info h4 {
        color: var(--text-color);
        margin: 0 0 0.5rem 0;
    }
    
    .bono-info .points {
        color: var(--secondary-color);
        font-weight: bold;
        margin: 0 0 1rem 0;
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
    
    .product-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .product-info img {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
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
    
    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    </style>
</body>
</html> 