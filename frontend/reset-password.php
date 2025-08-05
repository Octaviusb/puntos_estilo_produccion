<?php
session_start();
require_once '../server/config.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$messageType = '';
$validToken = false;
$email = '';

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $sql = "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $reset = $result->fetch_assoc();
        $email = $reset['email'];
        $validToken = true;
    } else {
        $message = 'El enlace de recuperación es inválido o ha expirado.';
        $messageType = 'error';
    }
} else {
    $message = 'Enlace de recuperación no válido.';
    $messageType = 'error';
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirmPassword)) {
        $message = 'Por favor, complete todos los campos.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'La contraseña debe tener al menos 6 caracteres.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Las contraseñas no coinciden.';
        $messageType = 'error';
    } else {
        // Actualizar contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuarios SET contraseña = ? WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hashedPassword, $email);
        
        if ($stmt->execute()) {
            // Marcar token como usado
            $sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $message = 'Contraseña actualizada correctamente. Puede iniciar sesión con su nueva contraseña.';
            $messageType = 'success';
            $validToken = false; // Ocultar formulario
        } else {
            $message = 'Error al actualizar la contraseña. Intente nuevamente.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Puntos Estilo</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="img/logoPe.jpg" alt="Logo" class="logo">
            <h1>Restablecer Contraseña</h1>
            
            <?php if ($message): ?>
                <div class="error-message <?php echo $messageType === 'success' ? 'success' : ''; ?> show">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($validToken): ?>
                <form method="POST" autocomplete="off">
                    <div class="input-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    <div class="input-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" class="login-button">Cambiar Contraseña</button>
                </form>
            <?php endif; ?>
            
            <div class="links">
                <a href="login.php">Volver al Login</a>
                <a href="registro.php">Crear cuenta</a>
            </div>
        </div>
    </div>
    
    <script>
    // Validar que las contraseñas coincidan
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    });
    </script>
</body>
</html>

<style>
.error-message.success {
    background: var(--success-color);
}

input[readonly] {
    background-color: #f5f5f5;
    cursor: not-allowed;
}
</style> 