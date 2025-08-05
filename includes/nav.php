<?php
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
?>
<header>
    <img src="img/logoPe.jpg" alt="logotipo" width="100" id="logo">
    <h1>Puntos Estilo</h1>
    <div class="user-info">
        <span>Usuario: <?php echo htmlspecialchars($_SESSION['user']['nombre']); ?></span>
        <a href="logout.php">Cerrar Sesión</a>
    </div>
</header>

<nav class="main-nav">
    <ul>
        <li><a href="dashboard.php">Inicio</a></li>
        <li><a href="perfil.php">Mi Perfil</a></li>
        <?php if ($_SESSION['user']['rol'] === 'admin'): ?>
        <li><a href="administracion.php">Administración</a></li>
        <?php endif; ?>
        <li><a href="mis-canjes.php">Mis Canjes</a></li>
        <li><a href="catalogo.php">Catálogo</a></li>
        <li><a href="contacto.php">Contacto</a></li>
    </ul>
</nav> 