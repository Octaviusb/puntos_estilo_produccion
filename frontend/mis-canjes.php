<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Obtener canjes del usuario
$sql = "SELECT tc.*, p.nombre as producto_nombre, p.imagen as producto_imagen 
        FROM tickets_canje tc 
        JOIN productos p ON tc.producto_id = p.id 
        WHERE tc.usuario_id = ? 
        ORDER BY tc.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$canjes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Canjes - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <style>
    .no-exchanges {
        text-align: center;
    }
    .no-exchanges .btn {
        color: #fff !important;
        background: #3498db;
        margin: 1.5rem auto 0 auto;
        display: inline-block;
    }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        <h2>Mis Canjes</h2>
        
        <div class="exchanges-container">
            <?php if ($canjes->num_rows > 0): ?>
                <div class="exchanges-list">
                    <?php while ($canje = $canjes->fetch_assoc()): ?>
                        <div class="exchange-card">
                            <div class="exchange-header">
                                <div class="exchange-info">
                                    <h3><?php echo htmlspecialchars($canje['producto_nombre']); ?></h3>
                                    <p class="exchange-date"><?php echo date('d/m/Y H:i', strtotime($canje['fecha'])); ?></p>
                                    <p class="exchange-id">Ticket: <?php echo $canje['id']; ?></p>
                                </div>
                                <div class="exchange-status">
                                    <span class="status-badge <?php echo $canje['estado']; ?>">
                                        <?php echo ucfirst($canje['estado']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="exchange-details">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($canje['producto_imagen'] ?: 'img/default-product.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($canje['producto_nombre']); ?>">
                                </div>
                                
                                <div class="exchange-summary">
                                    <div class="summary-item">
                                        <span class="label">Cantidad:</span>
                                        <span class="value"><?php echo $canje['cantidad']; ?></span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="label">Puntos usados:</span>
                                        <span class="value"><?php echo number_format($canje['puntos_usados']); ?></span>
                                    </div>
                                    <?php if ($canje['puntos_excedentes'] > 0): ?>
                                    <div class="summary-item">
                                        <span class="label">Excedente:</span>
                                        <span class="value"><?php echo number_format($canje['puntos_excedentes']); ?> pts</span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="summary-item">
                                        <span class="label">Método de pago:</span>
                                        <span class="value"><?php echo ucfirst($canje['metodo_pago']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="exchange-actions">
                                <button onclick="printTicket('<?php echo $canje['id']; ?>')" class="btn-small">Imprimir Ticket</button>
                                <button onclick="downloadTicket('<?php echo $canje['id']; ?>')" class="btn-small">Descargar PDF</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-exchanges">
                    <p>Aún no has realizado ningún canje.</p>
                    <button onclick="window.location.href='catalogo.php'" class="btn btn-primary">Ver Catálogo</button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    function printTicket(ticketId) {
        // Aquí se implementaría la funcionalidad de impresión
        alert('Funcionalidad de impresión en desarrollo para el ticket: ' + ticketId);
    }
    
    function downloadTicket(ticketId) {
        // Aquí se implementaría la descarga del PDF
        alert('Funcionalidad de descarga PDF en desarrollo para el ticket: ' + ticketId);
    }
    </script>
</body>
</html> 