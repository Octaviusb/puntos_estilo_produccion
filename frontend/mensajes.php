<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $mensajeId = (int)$_POST['mensaje_id'];
        
        switch ($_POST['action']) {
            case 'mark_read':
                $sql = "UPDATE mensajes_contacto SET estado = 'leido' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $mensajeId);
                $stmt->execute();
                break;
                
            case 'mark_responded':
                $sql = "UPDATE mensajes_contacto SET estado = 'respondido' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $mensajeId);
                $stmt->execute();
                break;
                
            case 'delete':
                $sql = "DELETE FROM mensajes_contacto WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $mensajeId);
                $stmt->execute();
                break;
        }
    }
}

// Obtener mensajes
$sql = "SELECT * FROM mensajes_contacto ORDER BY fecha DESC";
$mensajes = $conn->query($sql);

// Contar mensajes por estado
$stats = [
    'nuevos' => 0,
    'leidos' => 0,
    'respondidos' => 0
];

$statsResult = $conn->query("SELECT estado, COUNT(*) as total FROM mensajes_contacto GROUP BY estado");
while ($stat = $statsResult->fetch_assoc()) {
    $stats[$stat['estado']] = $stat['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Mensajes - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="administracion.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver a Administración</a>
        <h2>Gestión de Mensajes</h2>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Nuevos</h3>
                <p class="stat-number"><?php echo $stats['nuevos']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Leídos</h3>
                <p class="stat-number"><?php echo $stats['leidos']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Respondidos</h3>
                <p class="stat-number"><?php echo $stats['respondidos']; ?></p>
            </div>
        </div>
        
        <div class="messages-container">
            <h3>Lista de Mensajes</h3>
            
            <?php if ($mensajes->num_rows > 0): ?>
                <div class="messages-list">
                    <?php while ($mensaje = $mensajes->fetch_assoc()): ?>
                        <div class="message-card <?php echo $mensaje['estado']; ?>">
                            <div class="message-header">
                                <div class="message-info">
                                    <h4><?php echo htmlspecialchars($mensaje['nombre']); ?></h4>
                                    <p class="message-email"><?php echo htmlspecialchars($mensaje['correo']); ?></p>
                                    <p class="message-phone"><?php echo htmlspecialchars($mensaje['celular']); ?></p>
                                    <p class="message-date"><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha'])); ?></p>
                                </div>
                                <div class="message-status">
                                    <span class="status-badge <?php echo $mensaje['estado']; ?>">
                                        <?php echo ucfirst($mensaje['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="message-content">
                                <p><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></p>
                            </div>
                            
                            <div class="message-actions">
                                <?php if ($mensaje['estado'] === 'nuevo'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="mensaje_id" value="<?php echo $mensaje['id']; ?>">
                                        <button type="submit" class="btn-small">Marcar como Leído</button>
                                    </form>
                                <?php elseif ($mensaje['estado'] === 'leido'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_responded">
                                        <input type="hidden" name="mensaje_id" value="<?php echo $mensaje['id']; ?>">
                                        <button type="submit" class="btn-small">Marcar como Respondido</button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="mailto:<?php echo htmlspecialchars($mensaje['correo']); ?>" class="btn-small">Responder</a>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este mensaje?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="mensaje_id" value="<?php echo $mensaje['id']; ?>">
                                    <button type="submit" class="btn-small btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-messages">
                    <p>No hay mensajes de contacto.</p>
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
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: var(--secondary-color);
        margin: 0;
    }
    
    .messages-container {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .messages-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .message-card {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .message-card.nuevo {
        border-left: 4px solid #e74c3c;
        background-color: #fff5f5;
    }
    
    .message-card.leido {
        border-left: 4px solid #f39c12;
        background-color: #fffbf0;
    }
    
    .message-card.respondido {
        border-left: 4px solid #27ae60;
        background-color: #f0fff4;
    }
    
    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .message-info h4 {
        margin: 0 0 0.5rem 0;
        color: var(--primary-color);
    }
    
    .message-email,
    .message-phone,
    .message-date {
        margin: 0.25rem 0;
        color: #666;
        font-size: 0.9rem;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .status-badge.nuevo {
        background-color: #e74c3c;
        color: white;
    }
    
    .status-badge.leido {
        background-color: #f39c12;
        color: white;
    }
    
    .status-badge.respondido {
        background-color: #27ae60;
        color: white;
    }
    
    .message-content {
        margin-bottom: 1rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    .message-content p {
        margin: 0;
        line-height: 1.6;
    }
    
    .message-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background-color: var(--secondary-color);
        color: white;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-small.btn-danger {
        background-color: #e74c3c;
    }
    
    .btn-small:hover {
        opacity: 0.8;
    }
    
    .no-messages {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    </style>
</body>
</html> 