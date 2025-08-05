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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre']);
    $correo = sanitize($_POST['correo']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    $puntos = (int)$_POST['puntos'];
    
    // Validar datos
    if (empty($nombre) || empty($correo) || empty($password)) {
        $error = 'Todos los campos obligatorios deben estar completos.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // Verificar si el correo ya existe
        $checkSql = "SELECT id FROM usuarios WHERE correo = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $correo);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'El correo electrónico ya está registrado.';
        } else {
            // Crear el usuario
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, correo, contraseña, rol, puntos) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nombre, $correo, $hashedPassword, $rol, $puntos);
            
            if ($stmt->execute()) {
                $message = 'Usuario creado exitosamente.';
                // Limpiar el formulario
                $_POST = array();
            } else {
                $error = 'Error al crear el usuario: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="gestion-usuarios.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver a Gestión de Usuarios</a>
        <h2>Crear Nuevo Usuario</h2>
        
        <div class="form-container">
            <?php if ($message): ?>
                <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="user-form">
                <div class="form-group">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo electrónico *</label>
                    <input type="email" id="correo" name="correo" required 
                           value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol *</label>
                    <select id="rol" name="rol" required>
                        <option value="usuario" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                        <option value="admin" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="puntos">Puntos iniciales</label>
                    <input type="number" id="puntos" name="puntos" min="0" 
                           value="<?php echo isset($_POST['puntos']) ? (int)$_POST['puntos'] : 0; ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Crear Usuario</button>
                    <a href="gestion-usuarios.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <style>
    .form-container {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 0 auto;
    }
    
    .user-form {
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
    
    .form-group input,
    .form-group select {
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 1rem;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }
    
    .form-group small {
        color: #666;
        font-size: 0.8rem;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
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
        background-color: #95a5a6;
        color: #222 !important;
    }
    </style>
</body>
</html> 