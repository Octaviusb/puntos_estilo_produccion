<?php
require_once '../server/config.php';

// Redirigir si no está logueado
if (!isLoggedIn()) {
    // Para AJAX, podrías devolver un error JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit();
}

$user_id = $_SESSION['user']['id'];

$response = ['success' => false, 'error' => 'Error desconocido'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['avatar'];
    
    // --- Validación del archivo ---
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        $response['error'] = 'Tipo de archivo no permitido.';
        echo json_encode($response);
        exit();
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5 MB
        $response['error'] = 'El archivo es demasiado grande (máximo 5 MB).';
        echo json_encode($response);
        exit();
    }
    
    // --- Mover el archivo ---
    $upload_dir = 'img/avatars/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        
        // --- Actualizar base de datos ---
        // Primero, obtén el avatar antiguo para borrarlo si existe
        $stmt_get = $conn->prepare("SELECT avatar FROM usuarios WHERE id = ?");
        $stmt_get->bind_param("i", $user_id);
        $stmt_get->execute();
        $old_avatar = $stmt_get->get_result()->fetch_assoc()['avatar'];
        $stmt_get->close();

        // Actualiza con la nueva ruta
        $stmt_update = $conn->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
        $stmt_update->bind_param("si", $destination, $user_id);
        
        if ($stmt_update->execute()) {
            $stmt_update->close();
            
            // Borra el archivo de avatar antiguo si no es el default
            if ($old_avatar && $old_avatar !== 'img/default-avatar.jpg' && file_exists($old_avatar)) {
                unlink($old_avatar);
            }
            
            // Actualiza la sesión
            $_SESSION['user']['avatar'] = $destination;
            
            $response = [
                'success' => true,
                'path' => $destination
            ];
            
        } else {
            $response['error'] = 'Error al actualizar la base de datos.';
        }
        
    } else {
        $response['error'] = 'Error al mover el archivo subido.';
    }
    
} else {
    $response['error'] = 'No se recibió ningún archivo o hubo un error en la subida.';
}

header('Content-Type: application/json');
echo json_encode($response);
?> 