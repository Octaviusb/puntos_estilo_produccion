<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Obtener estadísticas básicas
$stats = [];

// Total de usuarios
$sql = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'";
$result = $conn->query($sql);
$stats['usuarios'] = $result->fetch_assoc()['total'];

// Verificar si la columna 'puntos' existe
$checkPuntos = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'puntos'");
if ($checkPuntos->num_rows > 0) {
    // Total de puntos en el sistema
    $sql = "SELECT SUM(puntos) as total FROM usuarios";
    $result = $conn->query($sql);
    $stats['puntos'] = $result->fetch_assoc()['total'] ?? 0;
} else {
    $stats['puntos'] = 0;
}

// Verificar si la tabla transacciones existe
$checkTransacciones = $conn->query("SHOW TABLES LIKE 'transacciones'");
if ($checkTransacciones->num_rows > 0) {
    // Total de transacciones
    $sql = "SELECT COUNT(*) as total FROM transacciones";
    $result = $conn->query($sql);
    $stats['transacciones'] = $result->fetch_assoc()['total'];
} else {
    $stats['transacciones'] = 0;
}

// Verificar si la tabla mensajes_contacto existe
$checkMensajes = $conn->query("SHOW TABLES LIKE 'mensajes_contacto'");
if ($checkMensajes->num_rows > 0) {
    // Mensajes de contacto no leídos
    $sql = "SELECT COUNT(*) as total FROM mensajes_contacto WHERE estado = 'nuevo'";
    $result = $conn->query($sql);
    $stats['mensajes'] = $result->fetch_assoc()['total'];
} else {
    $stats['mensajes'] = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=2">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <h2>Panel de Administración</h2>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Estadísticas Generales</h3>
                <div class="stats">
                    <p><strong>Usuarios registrados:</strong> <?php echo number_format($stats['usuarios']); ?></p>
                    <p><strong>Puntos totales:</strong> <?php echo number_format($stats['puntos']); ?></p>
                    <p><strong>Transacciones:</strong> <?php echo number_format($stats['transacciones']); ?></p>
                    <p><strong>Mensajes nuevos:</strong> <?php echo number_format($stats['mensajes']); ?></p>
                </div>
            </div>

            <div class="dashboard-card">
                <h3>Gestión de Usuarios</h3>
                <div class="admin-actions">
                    <a href="gestion-usuarios.php" class="btn">Gestionar Usuarios</a>
                    <a href="crear-usuario.php" class="btn">Crear Nuevo Usuario</a>
                    <a href="cargar-puntos-csv.php" class="btn">Cargar Puntos por CSV</a>
                </div>
            </div>

            <div class="dashboard-card">
                <h3>Gestión de Contenido</h3>
                <div class="admin-actions">
                    <a href="gestion-productos.php" class="btn">Gestionar Productos</a>
                    <a href="gestion-canjes.php" class="btn">Gestionar Canjes</a>
                    <a href="mensajes.php" class="btn">Ver Mensajes</a>
                    <a href="reportes.php" class="btn">Generar Reportes</a>
                </div>
            </div>

            <div class="dashboard-card">
    <h3>Últimas Actividades</h3>
    <div class="activity-list">
    <?php
    if ($checkTransacciones->num_rows > 0):
        $sql = "SELECT t.*, u.nombre as usuario_nombre 
                FROM transacciones t 
                JOIN usuarios u ON t.usuario_id = u.id 
                ORDER BY t.fecha DESC LIMIT 5";
        $result = $conn->query($sql);
        if ($result->num_rows > 0):
            while ($trans = $result->fetch_assoc()):
    ?>
        <div class="activity-card">
            <div class="activity-header">
                <span class="user"><?php echo htmlspecialchars($trans['usuario_nombre']); ?></span>
                <span class="points"><?php echo number_format($trans['puntos']); ?> pts</span>
            </div>
            <div class="activity-footer">
                <span class="action"><?php echo ucfirst($trans['tipo']); ?></span>
                <span class="date"><?php echo date('d/m/Y H:i', strtotime($trans['fecha'])); ?></span>
            </div>
        </div>
    <?php 
            endwhile;
        else:
    ?>
        <p>No hay actividades recientes</p>
    <?php endif; else: ?>
        <p>Módulo de transacciones no disponible</p>
    <?php endif; ?>
    </div>
</div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
</body>
</html> 