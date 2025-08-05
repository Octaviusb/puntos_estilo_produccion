<?php
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('Error de conexión (' . $conn->connect_errno . ') ' . $conn->connect_error);
}
?>
