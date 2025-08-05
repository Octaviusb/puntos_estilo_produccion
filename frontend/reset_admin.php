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

// Nueva contraseña para el administrador
$admin_email = "obuitragocamelo@yahoo.es";
$new_password = "Admin123!";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Actualizar la contraseña
$sql = "UPDATE usuarios SET contraseña = ? WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashed_password, $admin_email);

if ($stmt->execute()) {
    echo "✓ Contraseña actualizada exitosamente\n";
    echo "Nuevas credenciales:\n";
    echo "Email: " . $admin_email . "\n";
    echo "Contraseña: " . $new_password . "\n";
} else {
    echo "Error al actualizar la contraseña: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?> 