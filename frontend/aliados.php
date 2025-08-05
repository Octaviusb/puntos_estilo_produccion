<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Verificar si existe la tabla aliados, si no, crearla
$checkTable = $conn->query("SHOW TABLES LIKE 'aliados'");
if ($checkTable->num_rows == 0) {
    $createTable = "CREATE TABLE aliados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        imagen VARCHAR(255),
        categoria VARCHAR(50),
        descuento VARCHAR(50),
        direccion TEXT,
        telefono VARCHAR(20),
        email VARCHAR(100),
        estado BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTable);
    
    // Insertar aliados de ejemplo
    $aliadosEjemplo = [
        ['Cine Colombia', 'Disfruta de las mejores películas con descuentos especiales', 'img/cine_colombia.jpg', 'Entretenimiento', '20% descuento', 'Centro Comercial Plaza Central', '555-0101', 'info@cinecolombia.com'],
        ['Restaurante El Buen Sabor', 'La mejor comida tradicional con beneficios exclusivos', 'img/restaurante.jpg', 'Gastronomía', '15% descuento', 'Calle 15 #23-45', '555-0202', 'contacto@buensabor.com'],
        ['Gimnasio Fitness Pro', 'Mantén tu salud con descuentos en membresías', 'img/gimnasio.jpg', 'Salud', '25% descuento', 'Avenida Principal #67-89', '555-0303', 'info@fitnesspro.com'],
        ['Tienda de Ropa Moda Express', 'Las últimas tendencias con precios especiales', 'img/ropa.jpg', 'Moda', '30% descuento', 'Centro Comercial Galerías', '555-0404', 'ventas@modaexpress.com']
    ];
    
    $stmt = $conn->prepare("INSERT INTO aliados (nombre, descripcion, imagen, categoria, descuento, direccion, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($aliadosEjemplo as $aliado) {
        $stmt->bind_param("ssssssss", $aliado[0], $aliado[1], $aliado[2], $aliado[3], $aliado[4], $aliado[5], $aliado[6], $aliado[7]);
        $stmt->execute();
    }
}

// Obtener aliados
$sql = "SELECT * FROM aliados WHERE estado = TRUE ORDER BY categoria, nombre";
$aliados = $conn->query($sql);

// Obtener categorías únicas
$sqlCategorias = "SELECT DISTINCT categoria FROM aliados WHERE estado = TRUE ORDER BY categoria";
$categorias = $conn->query($sqlCategorias);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aliados - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <a href="perfil.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver al Perfil</a>
        <h2>Nuestros Aliados</h2>
        
        <div class="intro-section">
            <p>Disfruta de descuentos y beneficios exclusivos en nuestros aliados comerciales. 
            Muestra tu código de usuario y acumula puntos en cada compra.</p>
        </div>
        
        <!-- Filtros por Categoría -->
        <div class="filters-section">
            <h3>Filtrar por Categoría</h3>
            <div class="category-filters">
                <button class="filter-btn active" data-category="todos">Todos</button>
                <?php while ($categoria = $categorias->fetch_assoc()): ?>
                    <button class="filter-btn" data-category="<?php echo htmlspecialchars($categoria['categoria']); ?>">
                        <?php echo htmlspecialchars($categoria['categoria']); ?>
                    </button>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Lista de Aliados -->
        <div class="allies-grid">
            <?php while ($aliado = $aliados->fetch_assoc()): ?>
                <div class="ally-card" data-category="<?php echo htmlspecialchars($aliado['categoria']); ?>">
                    <div class="ally-image">
                        <img src="<?php echo htmlspecialchars($aliado['imagen'] ?: 'img/default-ally.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($aliado['nombre']); ?>">
                        <div class="category-badge"><?php echo htmlspecialchars($aliado['categoria']); ?></div>
                    </div>
                    <div class="ally-content">
                        <h3><?php echo htmlspecialchars($aliado['nombre']); ?></h3>
                        <p class="description"><?php echo htmlspecialchars($aliado['descripcion']); ?></p>
                        <div class="discount-badge">
                            <span class="discount-text"><?php echo htmlspecialchars($aliado['descuento']); ?></span>
                        </div>
                        <div class="ally-info">
                            <div class="info-item">
                                <strong>Dirección:</strong>
                                <span><?php echo htmlspecialchars($aliado['direccion']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Teléfono:</strong>
                                <span><?php echo htmlspecialchars($aliado['telefono']); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Email:</strong>
                                <span><?php echo htmlspecialchars($aliado['email']); ?></span>
                            </div>
                        </div>
                        <div class="ally-actions">
                            <button class="btn btn-small" onclick="showDetails('<?php echo htmlspecialchars($aliado['nombre']); ?>')">
                                Ver Detalles
                            </button>
                            <button class="btn btn-small btn-secondary" onclick="getDirections('<?php echo htmlspecialchars($aliado['direccion']); ?>')">
                                Cómo Llegar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Información Adicional -->
        <div class="info-section">
            <h3>¿Cómo Funciona?</h3>
            <div class="how-it-works">
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Visita un Aliado</h4>
                    <p>Acude a cualquiera de nuestros aliados comerciales.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>Muestra tu Código</h4>
                    <p>Presenta tu código de usuario al momento de pagar.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Disfruta el Descuento</h4>
                    <p>Recibe el descuento y acumula puntos automáticamente.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    // Filtros por categoría
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Actualizar botones activos
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrar tarjetas
            document.querySelectorAll('.ally-card').forEach(card => {
                if (category === 'todos' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    function showDetails(nombre) {
        alert('Detalles de ' + nombre + '\n\nPróximamente: Información detallada, horarios, promociones especiales y más.');
    }
    
    function getDirections(direccion) {
        const url = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(direccion)}`;
        window.open(url, '_blank');
    }
    </script>

    <style>
    .intro-section {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .intro-section p {
        color: var(--text-color);
        font-size: 1.1rem;
        margin: 0;
    }
    
    .filters-section {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }
    
    .filters-section h3 {
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    
    .category-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        border: 2px solid var(--secondary-color);
        background: transparent;
        color: var(--secondary-color);
        border-radius: 20px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: bold;
    }
    
    .filter-btn:hover,
    .filter-btn.active {
        background: var(--secondary-color);
        color: white;
    }
    
    .allies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .ally-card {
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
    }
    
    .ally-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }
    
    .ally-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .ally-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .category-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--secondary-color);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    
    .ally-content {
        padding: 1.5rem;
    }
    
    .ally-content h3 {
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
    }
    
    .description {
        color: var(--text-color);
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    
    .discount-badge {
        background: var(--success-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        display: inline-block;
        margin-bottom: 1rem;
        font-weight: bold;
    }
    
    .ally-info {
        margin-bottom: 1rem;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .info-item strong {
        color: var(--primary-color);
    }
    
    .info-item span {
        color: var(--text-color);
    }
    
    .ally-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .info-section {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .info-section h3 {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .how-it-works {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
    }
    
    .step {
        text-align: center;
        padding: 1rem;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        background: var(--secondary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0 auto 1rem auto;
    }
    
    .step h4 {
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .step p {
        color: var(--text-muted);
        margin: 0;
    }
    
    @media (max-width: 768px) {
        .allies-grid {
            grid-template-columns: 1fr;
        }
        
        .category-filters {
            justify-content: center;
        }
        
        .ally-actions {
            flex-direction: column;
        }
    }
    </style>
</body>
</html> 