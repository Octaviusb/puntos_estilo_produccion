<?php
session_start();
require_once '../server/config.php';

$error = '';
$success = '';

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($telefono) || empty($password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'El email ya está registrado';
        } else {
            // Crear el usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $fecha_registro = date('Y-m-d H:i:s');
            
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, telefono, password, rol, fecha_registro, puntos) VALUES (?, ?, ?, ?, 'usuario', ?, 0)");
            $stmt->bind_param("sssss", $nombre, $email, $telefono, $hashed_password, $fecha_registro);
            
            if ($stmt->execute()) {
                $success = 'Usuario registrado exitosamente. Ya puedes iniciar sesión.';
                // Limpiar el formulario
                $_POST = array();
            } else {
                $error = 'Error al registrar el usuario: ' . $conn->error;
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
    <title>Registro - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/registro.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <div class="header-content">
            <img src="img/logoPe.jpg" alt="Puntos Estilo" class="logo">
            <h1>Puntos Estilo</h1>
        </div>
    </header>

    <main class="registro-container">
        <h2>Crear Cuenta</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form class="registro-form" method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono *</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">
                    La contraseña debe tener al menos 6 caracteres
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn-registro">Crear Cuenta</button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Validación en tiempo real
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const requirements = this.parentNode.querySelector('.password-requirements');
            
            if (password.length < 6) {
                requirements.style.color = '#c62828';
            } else {
                requirements.style.color = '#2e7d32';
            }
        });
        
        // Validar que las contraseñas coincidan
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#c62828';
            } else {
                this.style.borderColor = '';
            }
        });
    </script>
</body>
</html> 