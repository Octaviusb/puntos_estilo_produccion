<?php
session_start();
/*include_once '../includes/db_connect.php';
include_once '../includes/funciones.php';*/

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['user']['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <img src="../img/logoPe.jpg" alt="logotipo" width="100" id="logo">
        <h1>Puntos Estilo</h1>
        <div>
            <?php if (isset($_SESSION['user']) && $_SESSION['user'] !== null): ?>
                <span>Usuario: <?php echo htmlspecialchars($_SESSION['user']['nombre']); ?></span>
                <a href="/logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <span>Usuario no identificado</span>
            <?php endif; ?>
        </div>
    </header>    
    
    <section id="solicitudes">
        <h2>Solicitudes</h2>
        <!-- Contenido de Solicitudes aquí -->
    </section>
</body>
</html>
