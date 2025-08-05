<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];
$message = '';
$error = '';

// Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $nuevaContraseña = $_POST['nueva_contraseña'] ?? '';
    $confirmarContraseña = $_POST['confirmar_contraseña'] ?? '';
    
    // Validar correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, ingrese un correo electrónico válido.";
    } else {
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $sqlCheck = "SELECT id FROM usuarios WHERE correo = ? AND id != ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("si", $correo, $userId);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            $error = "El correo electrónico ya está registrado por otro usuario.";
        } else {
            // Actualizar datos básicos
            $sql = "UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nombre, $correo, $userId);
            
            if ($stmt->execute()) {
                // Actualizar contraseña si se proporcionó
                if (!empty($nuevaContraseña)) {
                    if ($nuevaContraseña === $confirmarContraseña) {
                        if (strlen($nuevaContraseña) >= 6) {
                            $hashContraseña = password_hash($nuevaContraseña, PASSWORD_DEFAULT);
                            $sqlPass = "UPDATE usuarios SET contraseña = ? WHERE id = ?";
                            $stmtPass = $conn->prepare($sqlPass);
                            $stmtPass->bind_param("si", $hashContraseña, $userId);
                            $stmtPass->execute();
                        } else {
                            $error = "La contraseña debe tener al menos 6 caracteres.";
                        }
                    } else {
                        $error = "Las contraseñas no coinciden.";
                    }
                }
                
                if (empty($error)) {
                    $message = "Datos actualizados correctamente.";
                    // Actualizar datos de sesión
                    $_SESSION['user']['nombre'] = $nombre;
                    $_SESSION['user']['correo'] = $correo;
                }
            } else {
                $error = "Error al actualizar los datos. Por favor, intente nuevamente.";
            }
        }
    }
}

// Obtener datos actuales del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Datos Personales - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        <h2>Mis Datos Personales</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="profile-form-container">
            <form method="POST" class="profile-form">
                <div class="form-section">
                    <h3>Información Personal</h3>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($user['nombre']); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" 
                               value="<?php echo htmlspecialchars($user['correo']); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Cambiar Contraseña</h3>
                    <p class="section-description">Deja en blanco si no deseas cambiar la contraseña.</p>
                    
                    <div class="form-group">
                        <label for="nueva_contraseña">Nueva Contraseña:</label>
                        <input type="password" id="nueva_contraseña" name="nueva_contraseña" 
                               minlength="6" placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contraseña">Confirmar Nueva Contraseña:</label>
                        <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" 
                               placeholder="Repite la nueva contraseña">
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Información de la Cuenta</h3>
                    
                    <div class="info-display">
                        <div class="info-item">
                            <label>ID de Usuario:</label>
                            <span><?php echo $user['id']; ?></span>
                        </div>
                        
                        <div class="info-item">
                            <label>Puntos Actuales:</label>
                            <span class="points"><?php echo number_format($user['puntos'] ?? 0); ?> pts</span>
                        </div>
                        
                        <div class="info-item">
                            <label>Rol:</label>
                            <span class="role-badge <?php echo $user['rol']; ?>">
                                <?php echo ucfirst($user['rol']); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <label>Fecha de Registro:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($user['fecha_registro'])); ?></span>
                        </div>
                        
                        <?php if ($user['ultimo_acceso']): ?>
                        <div class="info-item">
                            <label>Último Acceso:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($user['ultimo_acceso'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="perfil.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
        
        <!-- Opciones Adicionales -->
        <div class="additional-options">
            <h3>Opciones Adicionales</h3>
            <div class="options-grid">
                <div class="option-card">
                    <h4>Descargar Mis Datos</h4>
                    <p>Obtén una copia de todos tus datos personales.</p>
                    <button class="btn btn-secondary btn-small" onclick="downloadData()">Descargar</button>
                </div>
                
                <div class="option-card">
                    <h4>Historial de Actividad</h4>
                    <p>Revisa tu historial de transacciones y actividades.</p>
                    <a href="mis-consumos.php" class="btn btn-secondary btn-small">Ver Historial</a>
                </div>
                
                <div class="option-card">
                    <h4>Configuración de Privacidad</h4>
                    <p>Gestiona tus preferencias de privacidad.</p>
                    <button class="btn btn-secondary btn-small" onclick="privacySettings()">Configurar</button>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    function downloadData() {
        alert('Función de descarga de datos próximamente disponible.');
    }
    
    function privacySettings() {
        alert('Configuración de privacidad próximamente disponible.');
    }
    
    // Validación de contraseñas en tiempo real
    document.getElementById('confirmar_contraseña').addEventListener('input', function() {
        const nuevaContraseña = document.getElementById('nueva_contraseña').value;
        const confirmarContraseña = this.value;
        
        if (nuevaContraseña && confirmarContraseña && nuevaContraseña !== confirmarContraseña) {
            this.setCustomValidity('Las contraseñas no coinciden');
        } else {
            this.setCustomValidity('');
        }
    });
    </script>

    <style>
    .profile-form-container {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }
    
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .form-section h3 {
        color: var(--primary-color);
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }
    
    .section-description {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
        color: var(--text-color);
    }
    
    .form-group input {
        width: 100%;
        padding: 1rem;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        font-size: 1rem;
        transition: var(--transition);
        box-sizing: border-box;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    .info-display {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .info-item {
        background: var(--light-gray);
        padding: 1rem;
        border-radius: 4px;
    }
    
    .info-item label {
        display: block;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .info-item span {
        color: var(--text-color);
        font-size: 1rem;
    }
    
    .info-item .points {
        color: var(--secondary-color);
        font-weight: bold;
    }
    
    .role-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .role-badge.usuario {
        background: var(--secondary-color);
        color: white;
    }
    
    .role-badge.admin {
        background: var(--accent-color);
        color: white;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }
    
    .additional-options {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .additional-options h3 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }
    
    .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .option-card {
        background: var(--light-gray);
        padding: 1.5rem;
        border-radius: var(--border-radius);
        text-align: center;
    }
    
    .option-card h4 {
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .option-card p {
        color: var(--text-muted);
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-color: #28a745;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-color: #dc3545;
    }
    
    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }
        
        .info-display {
            grid-template-columns: 1fr;
        }
        
        .options-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .btn-secondary, .btn-small {
        color: #fff !important;
    }
    .btn-secondary.btn-small:hover {
        background: #7a8a94 !important;
        color: #fff !important;
    }
    </style>
</body>
</html> 