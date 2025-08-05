<?php
// Configuración para Railway
$database_url = getenv('DATABASE_URL');

if ($database_url) {
    $url = parse_url($database_url);
    $servername = $url['host'];
    $username = $url['user'];
    $password = $url['pass'];
    $dbname = ltrim($url['path'], '/');
    $port = $url['port'] ?? 3306;
} else {
    // Fallback para desarrollo local
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mi_proyecto";
    $port = 3306;
}

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");
?>