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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $message = 'Por favor, ingrese su correo electrónico.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Por favor, ingrese un correo electrónico válido.';
        $messageType = 'error';
    } else {
        // Verificar si el usuario existe
        $sql = "SELECT id, nombre FROM usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generar token de recuperación
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Guardar token en la base de datos
            $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE token = ?, expires_at = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $email, $token, $expires, $token, $expires);
            
            if ($stmt->execute()) {
                // En producción, enviar email con el enlace
                // Por ahora, mostrar el token
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset-password.php?token=" . $token;
                
                $message = 'Se ha enviado un enlace de recuperación a su correo electrónico. 
                           <br><br><strong>Enlace de prueba:</strong> <a href="' . $resetLink . '">' . $resetLink . '</a>';
                $messageType = 'success';
            } else {
                $message = 'Error al procesar la solicitud. Intente nuevamente.';
                $messageType = 'error';
            }
        } else {
            // Por seguridad, no revelar si el email existe o no
            $message = 'Si el correo electrónico existe en nuestra base de datos, recibirá un enlace de recuperación.';
            $messageType = 'success';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Puntos Estilo</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="img/logoPe.jpg" alt="Logo" class="logo">
            <h1>Recuperar Contraseña</h1>
            
            <?php if ($message): ?>
                <div class="error-message <?php echo $messageType === 'success' ? 'success' : ''; ?> show">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="off">
                <div class="input-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <button type="submit" class="login-button">Enviar Enlace de Recuperación</button>
            </form>
            
            <div class="links">
                <a href="login.php">Volver al Login</a>
                <a href="registro.php">Crear cuenta</a>
            </div>
        </div>
    </div>
</body>
</html>

<style>
.error-message.success {
    background: var(--success-color);
}
</style> 