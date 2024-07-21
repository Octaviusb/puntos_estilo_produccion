<?php
session_start();
include_once 'includes/db_connect.php';
include_once 'includes/funciones.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar si el usuario tiene permisos de administrador
if ($_SESSION['user']['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogo</title>
</head>
<body>
    <h1>Catálogo</h1>
</body>
</html>