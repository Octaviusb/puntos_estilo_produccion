<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario
$userId = $_SESSION['user']['id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <h2>Panel de Control</h2>
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Mis Puntos</h3>
                <p class="points"><?php echo number_format($user['puntos'] ?? 0); ?> pts</p>
                <p class="welcome">¡Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['nombre']); ?>!</p>
            </div>

            <div class="dashboard-card">
                <h3>Últimas Transacciones</h3>
                <?php
                // Verificar si la tabla transacciones existe
                $tableExists = $conn->query("SHOW TABLES LIKE 'transacciones'");
                if ($tableExists->num_rows > 0):
                    $sql = "SELECT * FROM transacciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 3";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0):
                        while ($trans = $result->fetch_assoc()):
                ?>
                    <div class="transaction">
                        <span class="type"><?php echo ucfirst($trans['tipo']); ?></span>
                        <span class="points"><?php echo number_format($trans['puntos']); ?> pts</span>
                        <span class="date"><?php echo date('d/m/Y', strtotime($trans['fecha'])); ?></span>
                    </div>
                <?php 
                        endwhile;
                    else:
                ?>
                        <p>No hay transacciones recientes</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Módulo de transacciones no disponible</p>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h3>Beneficios Destacados</h3>
                <?php
                // Verificar si la tabla productos existe
                $tableExists = $conn->query("SHOW TABLES LIKE 'productos'");
                if ($tableExists->num_rows > 0):
                    $sql = "SELECT * FROM productos WHERE destacado = 1 LIMIT 3";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0):
                        while ($producto = $result->fetch_assoc()):
                ?>
                    <div class="product">
                        <img src="<?php echo htmlspecialchars($producto['imagen'] ?? 'img/default-product.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <p><?php echo htmlspecialchars($producto['nombre']); ?></p>
                        <span class="points"><?php echo number_format($producto['puntos']); ?> pts</span>
                    </div>
                <?php 
                        endwhile;
                    else:
                ?>
                        <p>No hay productos destacados</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Módulo de productos no disponible</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
</body>
</html> 