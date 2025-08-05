<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar descarga de reportes
if (isset($_GET['download']) && isset($_GET['type'])) {
    $type = $_GET['type'];
    $filename = '';
    $headers = [];
    $data = [];
    
    switch ($type) {
        case 'usuarios':
            $filename = 'reporte_usuarios_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = ['ID', 'Nombre', 'Correo', 'Puntos', 'Rol', 'Estado', 'Fecha Registro', 'Último Acceso'];
            
            $sql = "SELECT id, nombre, correo, puntos, rol, estado, fecha_registro, ultimo_acceso FROM usuarios ORDER BY fecha_registro DESC";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    $row['id'],
                    $row['nombre'],
                    $row['correo'],
                    $row['puntos'],
                    $row['rol'],
                    $row['estado'] ? 'Activo' : 'Inactivo',
                    $row['fecha_registro'],
                    $row['ultimo_acceso'] ?: 'Nunca'
                ];
            }
            break;
            
        case 'transacciones':
            $filename = 'reporte_transacciones_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = ['ID', 'Usuario', 'Correo', 'Tipo', 'Puntos', 'Descripción', 'Fecha'];
            
            $sql = "SELECT t.id, u.nombre as usuario, u.correo, t.tipo, t.puntos, t.descripcion, t.fecha 
                    FROM transacciones t 
                    JOIN usuarios u ON t.usuario_id = u.id 
                    ORDER BY t.fecha DESC";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    $row['id'],
                    $row['usuario'],
                    $row['correo'],
                    ucfirst($row['tipo']),
                    $row['puntos'],
                    $row['descripcion'],
                    $row['fecha']
                ];
            }
            break;
            
        case 'productos':
            $filename = 'reporte_productos_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = ['ID', 'Nombre', 'Descripción', 'Puntos', 'Destacado', 'Stock', 'Estado', 'Fecha Creación'];
            
            $sql = "SELECT * FROM productos ORDER BY fecha_creacion DESC";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    $row['id'],
                    $row['nombre'],
                    $row['descripcion'],
                    $row['puntos'],
                    $row['destacado'] ? 'Sí' : 'No',
                    $row['stock'],
                    $row['estado'] ? 'Activo' : 'Inactivo',
                    $row['fecha_creacion']
                ];
            }
            break;
            
        case 'mensajes':
            $filename = 'reporte_mensajes_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = ['ID', 'Nombre', 'Correo', 'Celular', 'Mensaje', 'Estado', 'Fecha'];
            
            $sql = "SELECT * FROM mensajes_contacto ORDER BY fecha DESC";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    $row['id'],
                    $row['nombre'],
                    $row['correo'],
                    $row['celular'],
                    $row['mensaje'],
                    ucfirst($row['estado']),
                    $row['fecha']
                ];
            }
            break;
    }
    
    if (!empty($data)) {
        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        fputcsv($output, $headers);
        
        // Escribir datos
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    }
}

// Obtener estadísticas para mostrar
$stats = [];

// Total usuarios
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$stats['usuarios'] = $result->fetch_assoc()['total'];

// Total transacciones
$result = $conn->query("SELECT COUNT(*) as total FROM transacciones");
$stats['transacciones'] = $result->fetch_assoc()['total'];

// Total productos
$result = $conn->query("SELECT COUNT(*) as total FROM productos");
$stats['productos'] = $result->fetch_assoc()['total'];

// Total mensajes
$result = $conn->query("SELECT COUNT(*) as total FROM mensajes_contacto");
$stats['mensajes'] = $result->fetch_assoc()['total'];

// Puntos totales
$result = $conn->query("SELECT SUM(puntos) as total FROM usuarios");
$stats['puntos_totales'] = $result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="administracion.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver a Administración</a>
        <h2>Generar Reportes</h2>
        
        <!-- Estadísticas generales -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Usuarios</h3>
                <p class="stat-number"><?php echo number_format($stats['usuarios']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Transacciones</h3>
                <p class="stat-number"><?php echo number_format($stats['transacciones']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Productos</h3>
                <p class="stat-number"><?php echo number_format($stats['productos']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Mensajes</h3>
                <p class="stat-number"><?php echo number_format($stats['mensajes']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Puntos Totales</h3>
                <p class="stat-number"><?php echo number_format($stats['puntos_totales']); ?></p>
            </div>
        </div>
        
        <div class="reports-container">
            <h3>Descargar Reportes</h3>
            
            <div class="reports-grid">
                <div class="report-card">
                    <h4>Reporte de Usuarios</h4>
                    <p>Lista completa de usuarios registrados con sus puntos y estado.</p>
                    <ul>
                        <li>ID, Nombre, Correo</li>
                        <li>Puntos actuales</li>
                        <li>Rol y Estado</li>
                        <li>Fechas de registro y último acceso</li>
                    </ul>
                    <a href="?download=1&type=usuarios" class="btn">Descargar CSV</a>
                </div>
                
                <div class="report-card">
                    <h4>Reporte de Transacciones</h4>
                    <p>Historial completo de acumulaciones y redenciones de puntos.</p>
                    <ul>
                        <li>Usuario y correo</li>
                        <li>Tipo de transacción</li>
                        <li>Cantidad de puntos</li>
                        <li>Descripción y fecha</li>
                    </ul>
                    <a href="?download=1&type=transacciones" class="btn">Descargar CSV</a>
                </div>
                
                <div class="report-card">
                    <h4>Reporte de Productos</h4>
                    <p>Catálogo completo de productos disponibles para redención.</p>
                    <ul>
                        <li>Nombre y descripción</li>
                        <li>Puntos requeridos</li>
                        <li>Stock disponible</li>
                        <li>Estado y destacado</li>
                    </ul>
                    <a href="?download=1&type=productos" class="btn">Descargar CSV</a>
                </div>
                
                <div class="report-card">
                    <h4>Reporte de Mensajes</h4>
                    <p>Mensajes de contacto recibidos y su estado de atención.</p>
                    <ul>
                        <li>Datos del remitente</li>
                        <li>Mensaje completo</li>
                        <li>Estado de atención</li>
                        <li>Fecha de recepción</li>
                    </ul>
                    <a href="?download=1&type=mensajes" class="btn">Descargar CSV</a>
                </div>
            </div>
            
            <div class="report-info">
                <h4>Información sobre los reportes:</h4>
                <ul>
                    <li>Los archivos se descargan en formato CSV (Comma Separated Values)</li>
                    <li>Puedes abrirlos en Excel, Google Sheets o cualquier editor de texto</li>
                    <li>Los archivos incluyen codificación UTF-8 para caracteres especiales</li>
                    <li>Los reportes se generan con la fecha y hora actual</li>
                </ul>
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
    
    .reports-container {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .report-card {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .report-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .report-card h4 {
        color: var(--primary-color);
        margin: 0 0 1rem 0;
    }
    
    .report-card p {
        color: #666;
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    
    .report-card ul {
        margin: 1rem 0;
        padding-left: 1.5rem;
        color: #666;
    }
    
    .report-card li {
        margin-bottom: 0.25rem;
    }
    
    .report-info {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: 4px;
        border-left: 4px solid var(--secondary-color);
    }
    
    .report-info h4 {
        margin: 0 0 1rem 0;
        color: var(--primary-color);
    }
    
    .report-info ul {
        margin: 0;
        padding-left: 1.5rem;
        color: #666;
    }
    
    .report-info li {
        margin-bottom: 0.5rem;
    }
    </style>
</body>
</html> 