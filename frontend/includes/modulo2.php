<?php


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>modulo2</title>
</head>
<body>
<header>
        <img src="img/logoPe.jpg" alt="logotipo" width="100" id="logo">
        <h1>Puntos Estilo</h1>
        <div>
            <span>Usuario: <?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </header>
<h3>Módulo 2: Cargar Mensualmente Puntos</h3>
<!-- Formulario y lógica para cargar puntos -->

</body>
</html>