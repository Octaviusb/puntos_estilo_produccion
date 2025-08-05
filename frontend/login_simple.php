<?php
session_start();
require_once '../server/config.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        // Verificar si la columna 'estado' existe
        $checkColumn = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'estado'");
        $hasEstado = $checkColumn->num_rows > 0;
        
        // Construir la consulta según si existe la columna estado
        if ($hasEstado) {
            $sql = "SELECT * FROM usuarios WHERE correo = ? AND estado = TRUE";
        } else {
            $sql = "SELECT * FROM usuarios WHERE correo = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($password, $user['contraseña'])) {
                // Actualizar último acceso si la columna existe
                $checkLastAccess = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'ultimo_acceso'");
                if ($checkLastAccess->num_rows > 0) {
                    $updateSql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                }
                
                // Guardar datos en sesión
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'correo' => $user['correo'],
                    'rol' => $user['rol'] ?? 'usuario',
                    'puntos' => $user['puntos'] ?? 0
                ];
                
                // Redirigir según el rol
                if (isset($user['rol']) && $user['rol'] === 'admin') {
                    header('Location: dashboard.php');
                } else {
                    header('Location: perfil.php');
                }
                exit();
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado o cuenta desactivada.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Puntos Estilo</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="img/logoPe.jpg" alt="Logo" class="logo">
            <h1>Puntos Estilo</h1>
            <p class="subtitle">Inicia sesión para acceder a tus beneficios</p>
            <div class="mode-indicator">
                <span class="mode-badge">Modo Simple</span>
            </div>
            <?php if ($error): ?>
                <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-button">Iniciar Sesión</button>
            </form>
            <div class="links">
                <a href="registro.php">¿No tienes cuenta? Regístrate</a>
                <a href="login.php">Login con OTP</a>
            </div>
        </div>
    </div>
    <style>
    .mode-indicator {
        margin-bottom: 1.5rem;
    }
    .mode-badge {
        background: #ffc107;
        color: #000;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    .subtitle {
        color: #666;
        margin-bottom: 1rem;
        font-size: 1rem;
    }
    </style>
</body>
</html>