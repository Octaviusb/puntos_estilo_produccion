<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Procesar cambios de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $ticketId = $_POST['ticket_id'];
    
    if ($_POST['action'] === 'cambiar_estado') {
        $nuevoEstado = $_POST['nuevo_estado'];
        
        $sql = "UPDATE tickets_canje SET estado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nuevoEstado, $ticketId);
        
        if ($stmt->execute()) {
            $message = "Estado del ticket actualizado exitosamente.";
        } else {
            $error = "Error al actualizar el estado del ticket.";
        }
    }
}

// Obtener filtros
$filtroEstado = $_GET['estado'] ?? '';
$filtroFecha = $_GET['fecha'] ?? '';

// Construir consulta con filtros
$sql = "SELECT tc.*, p.nombre as producto_nombre, p.imagen as producto_imagen, 
               u.nombre as usuario_nombre, u.correo as usuario_email 
        FROM tickets_canje tc 
        JOIN productos p ON tc.producto_id = p.id 
        JOIN usuarios u ON tc.usuario_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

if ($filtroEstado) {
    $sql .= " AND tc.estado = ?";
    $params[] = $filtroEstado;
    $types .= "s";
}

if ($filtroFecha) {
    $sql .= " AND DATE(tc.fecha) = ?";
    $params[] = $filtroFecha;
    $types .= "s";
}

$sql .= " ORDER BY tc.fecha DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$canjes = $stmt->get_result();

// Obtener estadísticas
$sqlStats = "SELECT 
    COUNT(*) as total_canjes,
    SUM(puntos_usados) as total_puntos,
    COUNT(CASE WHEN estado = 'confirmado' THEN 1 END) as confirmados,
    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
    COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as cancelados
FROM tickets_canje";
$stats = $conn->query($sqlStats)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Canjes - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="administracion.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver a Administración</a>
        <h2>Gestión de Canjes</h2>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Canjes</h3>
                <p class="stat-number"><?php echo number_format($stats['total_canjes'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Puntos Canjeados</h3>
                <p class="stat-number"><?php echo number_format($stats['total_puntos'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Confirmados</h3>
                <p class="stat-number"><?php echo number_format($stats['confirmados'] ?? 0); ?></p>
            </div>
            <div class="stat-card">
                <h3>Pendientes</h3>
                <p class="stat-number"><?php echo number_format($stats['pendientes'] ?? 0); ?></p>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="estado">Estado:</label>
                    <select name="estado" id="estado">
                        <option value="">Todos</option>
                        <option value="confirmado" <?php echo $filtroEstado === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                        <option value="pendiente" <?php echo $filtroEstado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="cancelado" <?php echo $filtroEstado === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="<?php echo $filtroFecha; ?>">
                </div>
                
                <button type="submit" class="btn">Filtrar</button>
                <a href="gestion-canjes.php" class="btn btn-secondary">Limpiar</a>
            </form>
        </div>
        
        <!-- Lista de canjes -->
        <div class="exchanges-container">
            <?php if ($canjes->num_rows > 0): ?>
                <div class="exchanges-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Usuario</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Puntos</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($canje = $canjes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $canje['id']; ?></td>
                                    <td>
                                        <div class="user-info">
                                            <strong><?php echo htmlspecialchars($canje['usuario_nombre']); ?></strong>
                                            <small><?php echo htmlspecialchars($canje['usuario_email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="product-info">
                                            <img src="<?php echo htmlspecialchars($canje['producto_imagen'] ?: 'img/default-product.jpg'); ?>" 
                                                 alt="<?php echo htmlspecialchars($canje['producto_nombre']); ?>">
                                            <span><?php echo htmlspecialchars($canje['producto_nombre']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $canje['cantidad']; ?></td>
                                    <td><?php echo number_format($canje['puntos_usados'] ?? 0); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($canje['fecha'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $canje['estado']; ?>">
                                            <?php echo ucfirst($canje['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button onclick="viewDetails('<?php echo $canje['id']; ?>')" class="btn-small">Ver</button>
                                            <button onclick="changeStatus('<?php echo $canje['id']; ?>', '<?php echo $canje['estado']; ?>')" class="btn-small">Cambiar</button>
                                            <button onclick="printTicket('<?php echo $canje['id']; ?>')" class="btn-small">Imprimir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-exchanges">
                    <p>No se encontraron canjes con los filtros aplicados.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal de detalles -->
    <div id="detailsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Detalles del Canje</h3>
            <div id="modalContent"></div>
            <div class="modal-actions">
                <button onclick="closeModal()" class="btn">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal de cambio de estado -->
    <div id="statusModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Cambiar Estado</h3>
            <form method="POST">
                <input type="hidden" name="action" value="cambiar_estado">
                <input type="hidden" name="ticket_id" id="statusTicketId">
                
                <div class="form-group">
                    <label for="nuevo_estado">Nuevo Estado:</label>
                    <select name="nuevo_estado" id="nuevo_estado" required>
                        <option value="confirmado">Confirmado</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn">Confirmar</button>
                    <button type="button" onclick="closeStatusModal()" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

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
    
    .filters-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filters-form {
        display: flex;
        gap: 1rem;
        align-items: end;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-group label {
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .exchanges-container {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .exchanges-table {
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
        background-color: var(--light-gray);
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .user-info small {
        color: #666;
    }
    
    .product-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .product-info img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .status-badge.confirmado {
        background-color: #27ae60;
        color: white;
    }
    
    .status-badge.pendiente {
        background-color: #f39c12;
        color: white;
    }
    
    .status-badge.cancelado {
        background-color: #e74c3c;
        color: white;
    }
    
    .actions {
        display: flex;
        gap: 0.25rem;
    }
    
    .btn-small {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background-color: var(--secondary-color);
        color: white;
    }
    
    .btn-small:hover {
        opacity: 0.8;
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        min-width: 400px;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .success-message {
        background-color: #d4edda;
        color: #155724;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        border: 1px solid #c3e6cb;
    }
    
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        border: 1px solid #f5c6cb;
    }
    
    .btn-secondary {
        color: #222 !important;
    }
    </style>

    <script>
    function viewDetails(ticketId) {
        // Aquí se cargarían los detalles del ticket
        document.getElementById('modalContent').innerHTML = `
            <p>Cargando detalles del ticket: ${ticketId}</p>
            <p>Esta funcionalidad mostrará todos los detalles del canje.</p>
        `;
        document.getElementById('detailsModal').style.display = 'flex';
    }
    
    function changeStatus(ticketId, currentStatus) {
        document.getElementById('statusTicketId').value = ticketId;
        document.getElementById('nuevo_estado').value = currentStatus;
        document.getElementById('statusModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('detailsModal').style.display = 'none';
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
    }
    
    function printTicket(ticketId) {
        alert('Funcionalidad de impresión en desarrollo para el ticket: ' + ticketId);
    }
    
    // Cerrar modales al hacer clic fuera
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html> 