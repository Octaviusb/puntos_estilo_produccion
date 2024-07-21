<?php
include_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
    
    $sql = "INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, 'user')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nombre, $correo, $contraseña);
    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error al registrar el usuario";
    }
}
?>
