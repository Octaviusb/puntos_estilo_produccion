<?php
require_once '../../includes/db_connect.php';

echo "<h2>Verificando tabla codigos_otp en benefits_system</h2>";

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
        echo "Tabla 'codigos_otp' creada exitosamente en benefits_system.<br>";
    } else {
        echo "Error al crear tabla 'codigos_otp': " . $conn->error . "<br>";
    }
} else {
    echo "La tabla 'codigos_otp' ya existe en benefits_system.<br>";
}

// Mostrar algunos usuarios de ejemplo
echo "<h3>Usuarios disponibles en benefits_system:</h3>";
$sql = "SELECT id, email, nombre, rol FROM usuarios WHERE activo = 1 LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Email</th><th>Nombre</th><th>Rol</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['rol'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No hay usuarios activos en benefits_system.<br>";
}

$conn->close();
echo "<br><a href='login.php'>Ir al login</a>";
?> 