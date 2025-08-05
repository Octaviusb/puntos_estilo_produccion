<?php
session_start();
require_once '../includes/db_connect.php';

// Obtener los datos del POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_mail']) || !isset($data['pass']) || !isset($data['otp'])) {
    echo "Error: Faltan datos de inicio de sesión";
    exit;
}

$email = $data['user_mail'];
$password = $data['pass'];
$otp = $data['otp'];

// Verificar OTP
if (strlen($otp) !== 6 || !ctype_digit($otp)) {
    echo "Error: Código OTP inválido";
    exit;
}

// Consultar el usuario
$sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Verificar OTP
        $sql = "SELECT * FROM codigos_otp 
                WHERE usuario_id = ? 
                AND codigo = ? 
                AND usado = 0 
                AND fecha_expiracion > NOW() 
                ORDER BY fecha_creacion DESC 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user['id'], $otp);
        $stmt->execute();
        $otp_result = $stmt->get_result();

        if ($otp_result->num_rows === 1) {
            // Marcar OTP como usado
            $otp_data = $otp_result->fetch_assoc();
            $sql = "UPDATE codigos_otp SET usado = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $otp_data['id']);
            $stmt->execute();

            // Iniciar sesión
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol'] ?? 'usuario',
                'puntos' => 0 // En benefits_system no hay columna puntos
            ];
            echo "ok";
        } else {
            echo "Error: Código OTP incorrecto o expirado";
        }
    } else {
        echo "Error: Contraseña incorrecta";
    }
} else {
    echo "Error: Usuario no encontrado";
}

$stmt->close();
$conn->close();
?> 