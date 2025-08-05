<?php
session_start();
require_once '../server/config.php';

// Validar sesión y permisos de administrador
requireAdmin();

$message = '';
$error = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Validar archivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error al subir el archivo.';
    } elseif ($file['type'] !== 'text/csv' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
        $error = 'El archivo debe ser un CSV válido.';
    } else {
        // Procesar CSV
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $row = 1;
            $successCount = 0;
            $errorCount = 0;
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if ($row === 1) {
                    // Verificar encabezados
                    if (count($data) < 2 || strtolower(trim($data[0])) !== 'correo' || strtolower(trim($data[1])) !== 'puntos') {
                        $error = 'El CSV debe tener los encabezados: correo, puntos';
                        break;
                    }
                } else {
                    // Procesar fila de datos
                    $correo = trim($data[0]);
                    $puntos = (int)trim($data[1]);
                    
                    if (empty($correo) || $puntos <= 0) {
                        $results[] = [
                            'row' => $row,
                            'correo' => $correo,
                            'puntos' => $puntos,
                            'status' => 'error',
                            'message' => 'Datos inválidos'
                        ];
                        $errorCount++;
                    } else {
                        // Buscar usuario
                        $sql = "SELECT id, nombre, puntos FROM usuarios WHERE correo = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $correo);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows === 1) {
                            $user = $result->fetch_assoc();
                            
                            // Actualizar puntos
                            $newPoints = $user['puntos'] + $puntos;
                            $updateSql = "UPDATE usuarios SET puntos = ? WHERE id = ?";
                            $updateStmt = $conn->prepare($updateSql);
                            $updateStmt->bind_param("ii", $newPoints, $user['id']);
                            
                            if ($updateStmt->execute()) {
                                // Registrar transacción
                                $transSql = "INSERT INTO transacciones (usuario_id, tipo, puntos, descripcion) VALUES (?, 'acumulacion', ?, 'Carga masiva por CSV')";
                                $transStmt = $conn->prepare($transSql);
                                $transStmt->bind_param("ii", $user['id'], $puntos);
                                $transStmt->execute();
                                
                                $results[] = [
                                    'row' => $row,
                                    'correo' => $correo,
                                    'nombre' => $user['nombre'],
                                    'puntos' => $puntos,
                                    'puntos_anteriores' => $user['puntos'],
                                    'puntos_nuevos' => $newPoints,
                                    'status' => 'success',
                                    'message' => 'Puntos agregados correctamente'
                                ];
                                $successCount++;
                            } else {
                                $results[] = [
                                    'row' => $row,
                                    'correo' => $correo,
                                    'puntos' => $puntos,
                                    'status' => 'error',
                                    'message' => 'Error al actualizar puntos'
                                ];
                                $errorCount++;
                            }
                        } else {
                            $results[] = [
                                'row' => $row,
                                'correo' => $correo,
                                'puntos' => $puntos,
                                'status' => 'error',
                                'message' => 'Usuario no encontrado'
                            ];
                            $errorCount++;
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
            
            $message = "Procesamiento completado: $successCount exitosos, $errorCount errores.";
        } else {
            $error = 'No se pudo abrir el archivo CSV.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Puntos por CSV - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="administracion.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver a Administración</a>
        <h2>Cargar Puntos por CSV</h2>
        
        <div class="csv-container">
            <div class="upload-section">
                <h3>Subir Archivo CSV</h3>
                
                <?php if ($message): ?>
                    <div class="success-message"><?php echo sanitizeOutput($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo sanitizeOutput($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="csv_file">Seleccionar archivo CSV:</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    
                    <div class="form-info">
                        <h4>Formato requerido del CSV:</h4>
                        <p>El archivo debe tener los siguientes encabezados:</p>
                        <ul>
                            <li><strong>correo</strong> - Correo electrónico del usuario</li>
                            <li><strong>puntos</strong> - Cantidad de puntos a agregar</li>
                        </ul>
                        
                        <h4>Ejemplo:</h4>
                        <pre>correo,puntos
usuario1@ejemplo.com,100
usuario2@ejemplo.com,250
usuario3@ejemplo.com,500</pre>
                    </div>
                    
                    <button type="submit" class="btn">Procesar CSV</button>
                </form>
            </div>
            
            <?php if (!empty($results)): ?>
            <div class="results-section">
                <h3>Resultados del Procesamiento</h3>
                
                <div class="results-summary">
                    <?php
                    $successCount = count(array_filter($results, function($r) { return $r['status'] === 'success'; }));
                    $errorCount = count(array_filter($results, function($r) { return $r['status'] === 'error'; }));
                    ?>
                    <p><strong>Exitosos:</strong> <?php echo $successCount; ?></p>
                    <p><strong>Errores:</strong> <?php echo $errorCount; ?></p>
                </div>
                
                <div class="results-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Fila</th>
                                <th>Correo</th>
                                <th>Nombre</th>
                                <th>Puntos Agregados</th>
                                <th>Puntos Anteriores</th>
                                <th>Puntos Nuevos</th>
                                <th>Estado</th>
                                <th>Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                            <tr class="<?php echo $result['status']; ?>">
                                <td><?php echo $result['row']; ?></td>
                                <td><?php echo htmlspecialchars($result['correo']); ?></td>
                                <td><?php echo isset($result['nombre']) ? htmlspecialchars($result['nombre']) : '-'; ?></td>
                                <td><?php echo $result['puntos']; ?></td>
                                <td><?php echo isset($result['puntos_anteriores']) ? $result['puntos_anteriores'] : '-'; ?></td>
                                <td><?php echo isset($result['puntos_nuevos']) ? $result['puntos_nuevos'] : '-'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $result['status']; ?>">
                                        <?php echo ucfirst($result['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($result['message']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <style>
    .csv-container {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    
    .upload-section,
    .results-section {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .upload-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-group label {
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .form-group input[type="file"] {
        padding: 0.75rem;
        border: 2px dashed var(--border-color);
        border-radius: 4px;
        background-color: #f8f9fa;
    }
    
    .form-info {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: 4px;
        border-left: 4px solid var(--secondary-color);
    }
    
    .form-info h4 {
        margin: 0 0 1rem 0;
        color: var(--primary-color);
    }
    
    .form-info ul {
        margin: 0.5rem 0;
        padding-left: 1.5rem;
    }
    
    .form-info pre {
        background-color: white;
        padding: 1rem;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        font-family: monospace;
        margin: 1rem 0;
    }
    
    .results-summary {
        display: flex;
        gap: 2rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
    
    .results-table {
        overflow-x: auto;
    }
    
    .results-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .results-table th,
    .results-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    
    .results-table th {
        background-color: var(--light-gray);
        font-weight: bold;
    }
    
    .results-table tr.success {
        background-color: #d4edda;
    }
    
    .results-table tr.error {
        background-color: #f8d7da;
    }
    
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .status-badge.success {
        background-color: #27ae60;
        color: white;
    }
    
    .status-badge.error {
        background-color: #e74c3c;
        color: white;
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
</body>
</html> 