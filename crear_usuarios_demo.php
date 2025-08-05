<?php
// Crear usuarios para demo
require_once 'server/config.php';

// Admin demo
$admin_email = 'demo@puntosestilo.com';
$admin_password = password_hash('demo2025', PASSWORD_DEFAULT);
$admin_name = 'Administrador Demo';

// Usuario demo
$user_email = 'usuario@demo.com';
$user_password = password_hash('usuario123', PASSWORD_DEFAULT);
$user_name = 'Usuario Demo';

// Crear admin demo
$sql = "INSERT INTO usuarios (nombre, correo, contraseña, rol, puntos, estado, fecha_registro) VALUES (?, ?, ?, 'admin', 0, 1, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $admin_name, $admin_email, $admin_password);
if ($stmt->execute()) {
    echo "✅ Admin demo creado: $admin_email / demo2025<br>";
} else {
    echo "⚠️ Admin demo ya existe<br>";
}

// Crear usuario demo
$sql = "INSERT INTO usuarios (nombre, correo, contraseña, rol, puntos, estado, fecha_registro) VALUES (?, ?, ?, 'usuario', 500, 1, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $user_name, $user_email, $user_password);
if ($stmt->execute()) {
    echo "✅ Usuario demo creado: $user_email / usuario123<br>";
} else {
    echo "⚠️ Usuario demo ya existe<br>";
}

echo "<br><h3>🎯 Credenciales para la demo:</h3>";
echo "<p><strong>Admin:</strong> demo@puntosestilo.com / demo2025</p>";
echo "<p><strong>Usuario:</strong> usuario@demo.com / usuario123</p>";
echo "<br><p><a href='frontend/'>Ir al sistema</a></p>";
?>