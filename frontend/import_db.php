<?php
$servername = "localhost";
$username = "root";
$password = "";

// Crear conexión
$conn = new mysqli($servername, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Crear la base de datos
$sql = "CREATE DATABASE IF NOT EXISTS mi_proyecto";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada exitosamente\n";
} else {
    echo "Error creando la base de datos: " . $conn->error . "\n";
}

// Seleccionar la base de datos
$conn->select_db("mi_proyecto");

// Leer el archivo SQL
$sql_file = file_get_contents('db/mi_proyecto.sql');

// Ejecutar las consultas SQL
if ($conn->multi_query($sql_file)) {
    echo "Base de datos importada exitosamente\n";
} else {
    echo "Error importando la base de datos: " . $conn->error . "\n";
}

$conn->close();
?> 