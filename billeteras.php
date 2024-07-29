<?php
// db.php - Conexión a la base de datos
$servername = 'localhost';
$username = 'obuitragocamelo';
$password = 'Obc19447/*';
$dbname = "mi_proyecto";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// create_billetera.php - Script para crear una nueva billetera
$nombre = $_POST['nombre'];
$puntos = $_POST['puntos'];

$sql = "INSERT INTO billeteras (nombre, puntos) VALUES ('$nombre', '$puntos')";

if ($conn->query($sql) === TRUE) {
    echo "Nueva billetera creada exitosamente";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
