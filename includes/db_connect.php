<?php
$servername = "localhost";
$username = "root";  // Usuario común en XAMPP/WAMP
$password = "";      // Contraseña común en XAMPP/WAMP
$dbname = "benefits_system";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error . "\n" .
        "Por favor, verifica que:\n" .
        "1. MySQL está instalado y corriendo\n" .
        "2. Las credenciales son correctas\n" .
        "3. El servicio de MySQL está activo\n");
}
?>
