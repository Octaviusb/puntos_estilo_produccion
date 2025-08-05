<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener puntos de hoy (usando la tabla transacciones)
$stmt = $conn->prepare("SELECT SUM(puntos) as puntos_hoy FROM transacciones WHERE usuario_id = ? AND tipo = 'acumulacion' AND DATE(fecha) = CURDATE()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$puntos_hoy = $result->fetch_assoc()['puntos_hoy'] ?? 0;

// Obtener última acumulación
$stmt = $conn->prepare("SELECT * FROM transacciones WHERE usuario_id = ? AND tipo = 'acumulacion' ORDER BY fecha DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ultima_acumulacion = $result->fetch_assoc();

// Obtener última redención
$stmt = $conn->prepare("SELECT * FROM transacciones WHERE usuario_id = ? AND tipo = 'redencion' ORDER BY fecha DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ultima_redencion = $result->fetch_assoc();

// Obtener beneficios destacados
$beneficios = $conn->query("SELECT * FROM productos WHERE destacado = 1 LIMIT 4");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <h2>Mi Perfil</h2>
        
        <!-- Información del usuario -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="avatar-container">
                    <?php if (!empty($usuario['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="Avatar del Usuario" class="avatar" id="avatar-preview">
                    <?php else: ?>
                        <img src="img/default-avatar.jpg" alt="Avatar del Usuario" class="avatar" id="avatar-preview">
                    <?php endif; ?>
                    <label for="avatar-upload" class="btn-avatar">
                        <img src="img/edit-icon.svg" alt="Editar Avatar">
                    </label>
                    <input type="file" id="avatar-upload" class="file-input" accept="image/*">
                </div>
                <h3>¡Hola, <?php echo htmlspecialchars($usuario['nombre']); ?>! 👋</h3>
                <p class="points">Puntos de hoy: <?php echo number_format($puntos_hoy); ?> pts</p>
                <p>Puntos totales: <?php echo number_format($usuario['puntos'] ?? 0); ?> pts</p>
            </div>

            <div class="dashboard-card">
                <h3>Última acumulación</h3>
                <?php if ($ultima_acumulacion): ?>
                    <p><?php echo htmlspecialchars($ultima_acumulacion['descripcion']); ?></p>
                    <p class="points">+<?php echo number_format($ultima_acumulacion['puntos']); ?> puntos</p>
                    <small><?php echo date('d/m/Y H:i', strtotime($ultima_acumulacion['fecha'])); ?></small>
                <?php else: ?>
                    <p>Aún no has ganado puntos</p>
                    <p>Por cada compra en nuestras estaciones ganas puntos.</p>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h3>Última redención</h3>
                <?php if ($ultima_redencion): ?>
                    <p><?php echo htmlspecialchars($ultima_redencion['descripcion']); ?></p>
                    <p class="points">-<?php echo number_format($ultima_redencion['puntos']); ?> puntos</p>
                    <small><?php echo date('d/m/Y H:i', strtotime($ultima_redencion['fecha'])); ?></small>
                <?php else: ?>
                    <p>Aún no registramos redención</p>
                    <p>Te invitamos a canjear tus puntos en nuestro catálogo.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Enlaces rápidos -->
        <div class="dashboard-card">
            <h3>Accesos Rápidos</h3>
            <div class="quick-links">
                <a href="mis-consumos.php" class="btn btn-primary">Mis Consumos</a>
                <a href="mis-bonos.php" class="btn btn-secondary">Mis Bonos</a>
                <a href="mis-referidos.php" class="btn btn-success">Mis Referidos</a>
                <a href="retos.php" class="btn btn-warning">Retos</a>
                <a href="catalogo.php" class="btn btn-primary">Catálogo</a>
                <a href="aliados.php" class="btn btn-secondary">Aliados</a>
                <a href="mis_datos.php" class="btn btn-success">Mis Datos</a>
            </div>
        </div>

        <!-- Beneficios destacados -->
        <div class="dashboard-card">
            <h3>Beneficios Destacados <a href="catalogo.php" class="btn btn-small">Ver todos</a></h3>
            <div class="products-grid">
                <?php while($b = $beneficios->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($b['imagen']); ?>" alt="<?php echo htmlspecialchars($b['nombre']); ?>">
                    </div>
                    <div class="product-info">
                        <h4><?php echo htmlspecialchars($b['nombre']); ?></h4>
                        <p class="points"><?php echo number_format($b['puntos']); ?> puntos</p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Subida de avatar
        document.getElementById('avatar-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('avatar', file);
                
                fetch('upload_avatar.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('avatar-preview').src = data.avatar_url;
                        alert('Avatar actualizado correctamente');
                    } else {
                        alert('Error al actualizar avatar: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al subir el archivo');
                });
            }
        });
    </script>

    <style>
    .quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .avatar-container {
        position: relative;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--secondary-color);
        cursor: pointer;
        transition: var(--transition);
    }
    
    .avatar:hover {
        transform: scale(1.05);
    }
    
    .btn-avatar {
        position: absolute;
        bottom: 0;
        right: 0;
        background: var(--white);
        border: 2px solid var(--secondary-color);
        border-radius: 50%;
        padding: 5px;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .btn-avatar:hover {
        background: var(--secondary-color);
        transform: scale(1.1);
    }
    
    .btn-avatar img {
        width: 20px;
        height: 20px;
        display: block;
    }
    
    .file-input {
        display: none;
    }
    </style>
</body>
</html> 