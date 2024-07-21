<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Puntos</title>
    <link rel="stylesheet" href="../css/cargar.css">

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

    <nav>
        <ul>
            <li><a href="/index.php">Inicio</a></li>
            <li><a href="cargar_puntos.php">Cargar Puntos</a></li>
            <li><a href="descontar_puntos.php">Descontar Puntos</a></li>
            <!-- Otros enlaces del menú -->
        </ul>
    </nav>

    <main>
        <h2>Cargar Puntos a los Usuarios</h2>
        <?php if (isset($mensaje)): ?>
            <p><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <form method="POST" action="cargar_puntos.php">
            <label for="user_id">ID del Usuario:</label>
            <input type="number" id="user_id" name="user_id" required><br>
            
            <label for="puntos">Puntos a Cargar:</label>
            <input type="number" id="puntos" name="puntos" required><br>
            
            <button type="submit">Cargar Puntos</button>
        </form>
    </main>
</body>
</html>
