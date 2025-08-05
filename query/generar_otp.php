<?php
require_once '../includes/db_connect.php';

// Obtener los datos del POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_mail'])) {
    echo "Error: Email no proporcionado";
    exit;
}

$email = $data['user_mail'];

// Verificar si el usuario existe
$sql = "SELECT id, nombre FROM usuarios WHERE email = ? AND activo = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Error: Usuario no encontrado";
    exit;
}

$user = $result->fetch_assoc();
$usuario_id = $user['id'];

// Generar código OTP de 6 dígitos
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Calcular fecha de expiración (5 minutos)
$fecha_expiracion = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Guardar el código OTP en la base de datos
$sql = "INSERT INTO codigos_otp (usuario_id, codigo, fecha_expiracion) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $usuario_id, $otp, $fecha_expiracion);

if (!$stmt->execute()) {
    echo "Error al generar el código OTP";
    exit;
}

// Verificar si estamos en localhost (desarrollo)
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) || 
               strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;

if ($isLocalhost) {
    // En localhost, solo devolver el OTP sin intentar enviar correo
    echo "ok|$otp";
} else {
    // En producción, enviar el correo
    $to = $email;
    $subject = "Código de Verificación - Puntos Estilo";
    $message = "
    <html>
    <head>
        <title>Código de Verificación</title>
    </head>
    <body>
        <h2>Hola {$user['nombre']},</h2>
        <p>Tu código de verificación es: <strong>{$otp}</strong></p>
        <p>Este código expirará en 5 minutos.</p>
        <p>Si no solicitaste este código, por favor ignora este mensaje.</p>
        <br>
        <p>Saludos,<br>Equipo Puntos Estilo</p>
    </body>
    </html>
    ";

    // Headers para enviar HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Puntos Estilo <noreply@puntosestilo.com>' . "\r\n";

    // Enviar el correo
    if(mail($to, $subject, $message, $headers)) {
        echo "ok|$otp";
    } else {
        echo "Error al enviar el código OTP. OTP generado: $otp";
    }
}

$stmt->close();
$conn->close();
?> 