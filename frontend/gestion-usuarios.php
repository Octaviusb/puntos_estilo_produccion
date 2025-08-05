<?php
session_start();
require_once '../server/config.php';

// Validar sesión y permisos de administrador
requireAdmin();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $userId = (int)$_POST['user_id'];
        
        $allowedActions = ['delete', 'toggle_status', 'add_points'];
        $action = validateInput($_POST['action'], 'string');
        
        if (in_array($action, $allowedActions)) {
            switch ($action) {
                case 'delete':
                    $stmt = prepareQuery($conn, "DELETE FROM usuarios WHERE id = ? AND rol != 'admin'", [$userId]);
                    if ($stmt) $stmt->execute();
                    break;
                    
                case 'toggle_status':
                    $stmt = prepareQuery($conn, "UPDATE usuarios SET estado = NOT estado WHERE id = ? AND rol != 'admin'", [$userId]);
                    if ($stmt) $stmt->execute();
                    break;
                    
                case 'add_points':
                    $points = (int)$_POST['points'];
                    if ($points > 0) {
                        $stmt = prepareQuery($conn, "UPDATE usuarios SET puntos = puntos + ? WHERE id = ?", [$points, $userId]);
                        if ($stmt) $stmt->execute();
                    }
                    break;
            }
        }
    }
}

// Obtener lista de usuarios
$sql = "SELECT id, nombre, correo, puntos, rol, estado, fecha_registro, ultimo_acceso 
        FROM usuarios 
        ORDER BY fecha_registro DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="administracion.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver a Administración</a>
        <h2>Gestión de Usuarios</h2>
        
        <div class="users-container">
            <div class="users-header">
                <h3>Lista de Usuarios</h3>
                <a href="crear-usuario.php" class="btn">Crear Nuevo Usuario</a>
            </div>
            
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Puntos</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Último Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($user['correo']); ?></td>
                            <td><?php echo number_format($user['puntos']); ?></td>
                            <td>
                                <span class="role-badge <?php echo $user['rol']; ?>">
                                    <?php echo ucfirst($user['rol']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $user['estado'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $user['estado'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?></td>
                            <td>
                                <?php echo $user['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($user['ultimo_acceso'])) : 'Nunca'; ?>
                            </td>
                            <td class="actions">
                                <?php if ($user['rol'] !== 'admin'): ?>
                                <button onclick="addPoints(<?php echo $user['id']; ?>)" class="btn-small">+ Puntos</button>
                                <button onclick="toggleStatus(<?php echo $user['id']; ?>)" class="btn-small">
                                    <?php echo $user['estado'] ? 'Desactivar' : 'Activar'; ?>
                                </button>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn-small btn-danger">Eliminar</button>
                                <?php else: ?>
                                <span class="no-actions">Sin acciones</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal para agregar puntos -->
    <div id="pointsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Agregar Puntos</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_points">
                <input type="hidden" name="user_id" id="modalUserId">
                <div class="input-group">
                    <label for="points">Cantidad de puntos:</label>
                    <input type="number" name="points" id="points" min="1" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn">Agregar</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <style>
    .users-container {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .users-table {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th, td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    
    th {
        background-color: var(--light-gray);
        font-weight: bold;
    }
    
    .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .role-badge.admin {
        background-color: #e74c3c;
        color: white;
    }
    
    .role-badge.usuario {
        background-color: var(--secondary-color);
        color: white;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .status-badge.active {
        background-color: #27ae60;
        color: white;
    }
    
    .status-badge.inactive {
        background-color: #95a5a6;
        color: white;
    }
    
    .actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
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
    
    .btn-small.btn-danger {
        background-color: #e74c3c;
    }
    
    .btn-small:hover {
        opacity: 0.8;
    }
    
    .no-actions {
        color: #95a5a6;
        font-style: italic;
    }
    
    /* Modal */
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
        min-width: 300px;
    }
    
    .modal-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .btn-secondary {
        background-color: #95a5a6;
    }
    </style>

    <script>
    function addPoints(userId) {
        document.getElementById('modalUserId').value = userId;
        document.getElementById('pointsModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('pointsModal').style.display = 'none';
    }
    
    function toggleStatus(userId) {
        if (confirm('¿Estás seguro de que quieres cambiar el estado de este usuario?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="user_id" value="${userId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function deleteUser(userId) {
        if (confirm('¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" value="${userId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('pointsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html> 