<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mi_proyecto";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Crear tabla usuarios
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
    terms TINYINT(1) NOT NULL DEFAULT 0,
    otp TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    celular VARCHAR(20) DEFAULT NULL,
    fecha_nacimiento DATE DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY correo (correo)
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla usuarios creada exitosamente\n";
} else {
    echo "Error creando la tabla usuarios: " . $conn->error . "\n";
}

// Insertar usuario administrador
$admin_email = "obuitragocamelo@yahoo.es";
$admin_password = password_hash("Admin123!", PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, correo, contraseña, rol) 
        VALUES ('Administrador', ?, ?, 'admin')
        ON DUPLICATE KEY UPDATE contraseña = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $admin_email, $admin_password, $admin_password);

if ($stmt->execute()) {
    echo "Usuario administrador creado/actualizado exitosamente\n";
    echo "Credenciales:\n";
    echo "Email: " . $admin_email . "\n";
    echo "Contraseña: Admin123!\n";
} else {
    echo "Error creando/actualizando el usuario administrador: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?> 