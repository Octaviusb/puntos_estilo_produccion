<?php
require_once '../../includes/db_connect.php';

echo "<h2>Verificando estructura de la tabla usuarios en benefits_system</h2>";

// Verificar si la tabla usuarios existe
$checkTable = $conn->query("SHOW TABLES LIKE 'usuarios'");
if ($checkTable->num_rows === 0) {
    echo "La tabla 'usuarios' no existe en benefits_system.<br>";
    exit;
}

// Mostrar todas las columnas de la tabla usuarios
$sql = "DESCRIBE usuarios";
$result = $conn->query($sql);

if ($result) {
    echo "<h3>Columnas de la tabla usuarios:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Por defecto</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error al obtener la estructura de la tabla: " . $conn->error;
}

$conn->close();
?> 