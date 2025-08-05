<?php
require_once '../server/config.php';

echo "<h2>Reset de Contraseña de Administrador</h2>";

// Verificar si el usuario administrador existe
$sql = "SELECT id, nombre, correo, rol FROM usuarios WHERE correo = 'obuitragocamelo@yahoo.es'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p>✓ Usuario administrador encontrado:</p>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Nombre: " . $user['nombre'] . "</li>";
    echo "<li>Email: " . $user['correo'] . "</li>";
    echo "<li>Rol: " . $user['rol'] . "</li>";
    echo "</ul>";
    
    // Resetear contraseña
    $newPassword = 'Admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $updateSql = "UPDATE usuarios SET contraseña = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $hashedPassword, $user['id']);
    
    if ($stmt->execute()) {
        echo "<p>✓ Contraseña actualizada exitosamente</p>";
        echo "<p><strong>Nuevas credenciales:</strong></p>";
        echo "<ul>";
        echo "<li>Email: obuitragocamelo@yahoo.es</li>";
        echo "<li>Contraseña: " . $newPassword . "</li>";
        echo "</ul>";
        
        // Verificar que la contraseña se guardó correctamente
        $verifySql = "SELECT contraseña FROM usuarios WHERE id = ?";
        $verifyStmt = $conn->prepare($verifySql);
        $verifyStmt->bind_param("i", $user['id']);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $storedHash = $verifyResult->fetch_assoc()['contraseña'];
        
        if (password_verify($newPassword, $storedHash)) {
            echo "<p>✓ Verificación de contraseña exitosa</p>";
        } else {
            echo "<p>✗ Error en la verificación de contraseña</p>";
        }
        
    } else {
        echo "<p>✗ Error actualizando contraseña: " . $stmt->error . "</p>";
    }
    
} else {
    echo "<p>✗ Usuario administrador no encontrado</p>";
    
    // Crear el usuario administrador
    $adminEmail = 'obuitragocamelo@yahoo.es';
    $adminPassword = 'Admin123';
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $createSql = "INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, 'admin')";
    $createStmt = $conn->prepare($createSql);
    $createStmt->bind_param("sss", 'Administrador', $adminEmail, $hashedPassword);
    
    if ($createStmt->execute()) {
        echo "<p>✓ Usuario administrador creado exitosamente</p>";
        echo "<p><strong>Credenciales:</strong></p>";
        echo "<ul>";
        echo "<li>Email: " . $adminEmail . "</li>";
        echo "<li>Contraseña: " . $adminPassword . "</li>";
        echo "</ul>";
    } else {
        echo "<p>✗ Error creando usuario administrador: " . $createStmt->error . "</p>";
    }
}

echo "<p><a href='login.php'>Ir al login</a></p>";
?> 