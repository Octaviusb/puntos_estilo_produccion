<?php
session_start();
require_once '../server/config.php';

// Verificar si la tabla codigos_otp existe
$checkOtpTable = $conn->query("SHOW TABLES LIKE 'codigos_otp'");
if ($checkOtpTable->num_rows === 0) {
    // Crear la tabla codigos_otp si no existe
    $sql = "CREATE TABLE codigos_otp (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        codigo VARCHAR(6) NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_expiracion DATETIME NOT NULL,
        usado BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )";
    if ($conn->query($sql)) {
        echo "Tabla 'codigos_otp' creada exitosamente.<br>";
    } else {
        echo "Error al crear tabla 'codigos_otp': " . $conn->error . "<br>";
    }
} else {
    echo "La tabla 'codigos_otp' ya existe.<br>";
}

// Verificar si la columna 'email' existe en usuarios (por si acaso)
$checkEmail = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'email'");
if ($checkEmail->num_rows === 0) {
    // Verificar si existe 'correo' en su lugar
    $checkCorreo = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'correo'");
    if ($checkCorreo->num_rows > 0) {
        echo "La tabla usuarios usa 'correo' en lugar de 'email'.<br>";
    }
} else {
    echo "La columna 'email' existe en la tabla usuarios.<br>";
}

echo "<br>Verificación completada. <a href='login.php'>Ir al login</a>";
?> 