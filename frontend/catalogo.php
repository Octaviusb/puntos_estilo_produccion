<?php
session_start();
require_once '../server/config.php';
require_once '../server/email_service.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';
$emailService = new EmailService();

$userId = $_SESSION['user']['id'];

// Obtener productos del catálogo
$sql = "SELECT * FROM productos WHERE estado = 1 ORDER BY puntos ASC";
$productos = $conn->query($sql);

// Obtener puntos del usuario
$stmt = $conn->prepare("SELECT puntos FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$puntosUsuario = $user['puntos'] ?? 0;

// Procesar canje de producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'canjear') {
    $productoId = (int)$_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    $metodoPago = $_POST['metodo_pago'];
    $puntosExcedentes = (int)$_POST['puntos_excedentes'];
    
    // Obtener datos del producto
    $sql = "SELECT * FROM productos WHERE id = ? AND estado = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productoId);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    
    if ($producto && $producto['stock'] >= $cantidad) {
        $puntosNecesarios = $producto['puntos'] * $cantidad;
        
        // Verificar si tiene suficientes puntos
        if ($puntosUsuario >= $puntosNecesarios) {
            // Iniciar transacción
            $conn->begin_transaction();
            
            try {
                // Descontar puntos del usuario
                $nuevosPuntos = $puntosUsuario - $puntosNecesarios;
                $sql = "UPDATE usuarios SET puntos = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $nuevosPuntos, $_SESSION['user']['id']);
                $stmt->execute();
                
                // Actualizar stock del producto
                $nuevoStock = $producto['stock'] - $cantidad;
                $sql = "UPDATE productos SET stock = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $nuevoStock, $productoId);
                $stmt->execute();
                
                // Registrar transacción de redención
                $sql = "INSERT INTO transacciones (usuario_id, tipo, puntos, descripcion) VALUES (?, 'redencion', ?, ?)";
                $stmt = $conn->prepare($sql);
                $descripcion = "Canje: {$producto['nombre']} x{$cantidad}";
                $stmt->bind_param("iis", $_SESSION['user']['id'], $puntosNecesarios, $descripcion);
                $stmt->execute();
                
                // Crear ticket de canje
                $ticketId = uniqid('TKT');
                $sql = "INSERT INTO tickets_canje (id, usuario_id, producto_id, cantidad, puntos_usados, metodo_pago, puntos_excedentes, fecha) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siiissi", $ticketId, $_SESSION['user']['id'], $productoId, $cantidad, $puntosNecesarios, $metodoPago, $puntosExcedentes);
                $stmt->execute();
                
                // Actualizar puntos en sesión
                $_SESSION['user']['puntos'] = $nuevosPuntos;
                
                // Enviar notificación por email
                $usuarioActualizado = array_merge($_SESSION['user'], ['puntos' => $nuevosPuntos]);
                $emailService->enviarNotificacionCanje($usuarioActualizado, $producto, $cantidad, $puntosNecesarios, $ticketId);
                
                $conn->commit();
                
                $message = "¡Canje exitoso! Has canjeado {$cantidad} {$producto['nombre']} por {$puntosNecesarios} puntos. Se ha enviado una confirmación a tu email.";
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error al procesar el canje: " . $e->getMessage();
            }
        } else {
            $error = "No tienes suficientes puntos. Necesitas {$puntosNecesarios} puntos y tienes {$puntosUsuario}.";
        }
    } else {
        $error = "Producto no disponible o stock insuficiente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        
        <div class="catalog-header">
            <h2>Catálogo de Productos</h2>
            <div class="user-points-display">
                <span>Tus puntos: <strong><?php echo number_format($puntosUsuario); ?> pts</strong></span>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filters-section">
            <form class="filters-form">
                <div class="filter-group">
                    <label for="orden">Ordenar por:</label>
                    <select id="orden" onchange="ordenarProductos(this.value)">
                        <option value="puntos">Puntos (menor a mayor)</option>
                        <option value="puntos-desc">Puntos (mayor a menor)</option>
                        <option value="nombre">Nombre A-Z</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Productos -->
        <div class="products-grid" id="products-grid">
            <?php if ($productos->num_rows > 0): ?>
                <?php while ($producto = $productos->fetch_assoc()): ?>
                    <div class="product-card" data-puntos="<?php echo $producto['puntos']; ?>" data-nombre="<?php echo strtolower($producto['nombre']); ?>">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <?php if ($producto['destacado']): ?>
                                <span class="featured-badge">Destacado</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($producto['descripcion'] ?: 'Sin descripción'); ?></p>
                            <div class="product-details">
                                <span class="points"><?php echo number_format($producto['puntos']); ?> puntos</span>
                                <?php if ($producto['stock'] > 0): ?>
                                    <span class="stock">Stock: <?php echo $producto['stock']; ?></span>
                                <?php else: ?>
                                    <span class="stock-out">Sin stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-actions">
                                <?php if ($puntosUsuario >= $producto['puntos'] && $producto['stock'] > 0): ?>
                                    <button type="button" class="btn btn-primary" onclick="abrirModalCanje(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>', <?php echo $producto['puntos']; ?>, <?php echo $producto['stock']; ?>)">
                                        Canjear
                                    </button>
                                <?php elseif ($producto['stock'] <= 0): ?>
                                    <button class="btn btn-disabled" disabled>Sin stock</button>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>
                                        Necesitas <?php echo number_format($producto['puntos'] - $puntosUsuario); ?> pts más
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No hay productos disponibles en el catálogo.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal de Canje -->
    <div id="modalCanje" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h3>Canjear Producto</h3>
            <form method="POST" id="formCanje">
                <input type="hidden" name="action" value="canjear">
                <input type="hidden" name="producto_id" id="modalProductoId">
                <input type="hidden" name="puntos_excedentes" value="0">
                
                <div class="form-group">
                    <label>Producto:</label>
                    <p id="modalProductoNombre" style="font-weight: bold; color: var(--primary-color);"></p>
                </div>
                
                <div class="form-group">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" value="1" required>
                </div>
                
                <div class="form-group">
                    <label for="metodo_pago">Método de Pago:</label>
                    <select id="metodo_pago" name="metodo_pago" required>
                        <option value="">Selecciona un método</option>
                        <option value="puntos">Solo Puntos</option>
                        <option value="mixto">Puntos + Dinero</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Puntos a usar:</label>
                    <p id="modalPuntosUsar" style="font-weight: bold; color: var(--secondary-color);"></p>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Canje</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function ordenarProductos(criterio) {
            const grid = document.getElementById('products-grid');
            const productos = Array.from(grid.children);
            
            productos.sort((a, b) => {
                switch(criterio) {
                    case 'puntos':
                        return parseInt(a.dataset.puntos) - parseInt(b.dataset.puntos);
                    case 'puntos-desc':
                        return parseInt(b.dataset.puntos) - parseInt(a.dataset.puntos);
                    case 'nombre':
                        return a.dataset.nombre.localeCompare(b.dataset.nombre);
                    default:
                        return 0;
                }
            });
            
            // Limpiar y reordenar
            productos.forEach(producto => grid.appendChild(producto));
        }

        function abrirModalCanje(productoId, nombre, puntos, stock) {
            document.getElementById('modalProductoId').value = productoId;
            document.getElementById('modalProductoNombre').textContent = nombre;
            document.getElementById('cantidad').max = stock;
            actualizarPuntosUsar();
            document.getElementById('modalCanje').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalCanje').style.display = 'none';
        }

        function actualizarPuntosUsar() {
            const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
            const puntosPorUnidad = parseInt(document.getElementById('modalProductoId').value.split(',')[2]) || 0;
            const puntosUsar = cantidad * puntosPorUnidad;
            document.getElementById('modalPuntosUsar').textContent = puntosUsar.toLocaleString() + ' puntos';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalCanje');
            if (event.target === modal) {
                cerrarModal();
            }
        }

        // Actualizar puntos cuando cambie la cantidad
        document.getElementById('cantidad').addEventListener('change', actualizarPuntosUsar);
    </script>

    <style>
    .catalog-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background: var(--white);
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .user-points-display {
        background: var(--secondary-color);
        color: var(--white);
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: bold;
    }
    
    .filters-section {
        background: var(--white);
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }
    
    .filters-form {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    
    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filter-group label {
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .filter-group select {
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: var(--white);
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }
    
    .product-card {
        background: var(--white);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }
    
    .product-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-5px);
    }
    
    .product-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .featured-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: var(--accent-color);
        color: var(--white);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .product-info {
        padding: 1.5rem;
    }
    
    .product-info h3 {
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }
    
    .product-description {
        color: var(--text-muted);
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    
    .product-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .points {
        font-weight: bold;
        color: var(--secondary-color);
        font-size: 1.1rem;
    }
    
    .stock {
        color: var(--success-color);
        font-size: 0.9rem;
    }
    
    .stock-out {
        color: var(--accent-color);
        font-size: 0.9rem;
        font-weight: bold;
    }
    
    .product-actions {
        text-align: center;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
        grid-column: 1 / -1;
    }
    
    @media (max-width: 768px) {
        .catalog-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .filters-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html> 