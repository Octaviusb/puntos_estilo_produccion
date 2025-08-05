<?php
session_start();
require_once '../server/config.php';

// Verificar si el usuario es admin
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $nombre = sanitize($_POST['nombre']);
                $descripcion = sanitize($_POST['descripcion']);
                $imagen = sanitize($_POST['imagen']);
                $puntos = (int)$_POST['puntos'];
                $destacado = isset($_POST['destacado']) ? 1 : 0;
                $stock = (int)$_POST['stock'];
                $sql = "INSERT INTO productos (nombre, descripcion, imagen, puntos, destacado, stock) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiii", $nombre, $descripcion, $imagen, $puntos, $destacado, $stock);
                $stmt->execute();
                break;
            case 'delete':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM productos WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
            case 'edit':
                $id = (int)$_POST['id'];
                $nombre = sanitize($_POST['nombre']);
                $descripcion = sanitize($_POST['descripcion']);
                $imagen = sanitize($_POST['imagen']);
                $puntos = (int)$_POST['puntos'];
                $destacado = isset($_POST['destacado']) ? 1 : 0;
                $stock = (int)$_POST['stock'];
                $sql = "UPDATE productos SET nombre=?, descripcion=?, imagen=?, puntos=?, destacado=?, stock=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiiii", $nombre, $descripcion, $imagen, $puntos, $destacado, $stock, $id);
                $stmt->execute();
                break;
        }
    }
}

// Obtener productos
$productos = $conn->query("SELECT * FROM productos ORDER BY fecha_creacion DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <style>
        .btn-secondary {
            color: #222 !important;
        }
    </style>
</head>
<body>
<?php include 'includes/nav.php'; ?>
<main class="dashboard-container">
    <a href="administracion.php" class="btn btn-secondary" style="margin-bottom: 1.5rem; display: inline-block;">‹ Volver a Administración</a>
    <h2>Gestión de Productos</h2>
    <div class="users-header">
        <h3>Lista de Productos</h3>
        <button onclick="document.getElementById('createModal').style.display='flex'" class="btn">Crear Producto</button>
    </div>
    <div class="users-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Imagen</th>
                    <th>Puntos</th>
                    <th>Destacado</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($p['descripcion']); ?></td>
                    <td><img src="<?php echo htmlspecialchars($p['imagen']); ?>" alt="img" style="width:40px;"></td>
                    <td><?php echo $p['puntos']; ?></td>
                    <td><?php echo $p['destacado'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo $p['stock']; ?></td>
                    <td>
                        <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="btn-small">Editar</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar producto?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="btn-small btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Crear -->
<div id="createModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Crear Producto</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="input-group"><label>Nombre:</label><input name="nombre" required></div>
            <div class="input-group"><label>Descripción:</label><input name="descripcion"></div>
            <div class="input-group"><label>Imagen (ruta o URL):</label><input name="imagen"></div>
            <div class="input-group"><label>Puntos:</label><input name="puntos" type="number" min="0" required></div>
            <div class="input-group"><label>Destacado:</label><input name="destacado" type="checkbox"></div>
            <div class="input-group"><label>Stock:</label><input name="stock" type="number" min="0" required></div>
            <div class="modal-actions">
                <button type="submit" class="btn">Crear</button>
                <button type="button" onclick="closeModal('createModal')" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h3>Editar Producto</h3>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="input-group"><label>Nombre:</label><input name="nombre" id="edit_nombre" required></div>
            <div class="input-group"><label>Descripción:</label><input name="descripcion" id="edit_descripcion"></div>
            <div class="input-group"><label>Imagen (ruta o URL):</label><input name="imagen" id="edit_imagen"></div>
            <div class="input-group"><label>Puntos:</label><input name="puntos" id="edit_puntos" type="number" min="0" required></div>
            <div class="input-group"><label>Destacado:</label><input name="destacado" id="edit_destacado" type="checkbox"></div>
            <div class="input-group"><label>Stock:</label><input name="stock" id="edit_stock" type="number" min="0" required></div>
            <div class="modal-actions">
                <button type="submit" class="btn">Guardar</button>
                <button type="button" onclick="closeModal('editModal')" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function editProduct(p) {
    document.getElementById('edit_id').value = p.id;
    document.getElementById('edit_nombre').value = p.nombre;
    document.getElementById('edit_descripcion').value = p.descripcion;
    document.getElementById('edit_imagen').value = p.imagen;
    document.getElementById('edit_puntos').value = p.puntos;
    document.getElementById('edit_destacado').checked = p.destacado == 1;
    document.getElementById('edit_stock').value = p.stock;
    document.getElementById('editModal').style.display = 'flex';
}
// Cerrar modal al hacer clic fuera
['createModal','editModal'].forEach(function(id){
    document.getElementById(id).addEventListener('click', function(e){
        if(e.target === this) closeModal(id);
    });
});
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html> 