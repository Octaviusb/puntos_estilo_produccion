<?php
function getEmployeePoints($employee_id) {
    global $conn;
    // Suma todos los puntos asignados
    $query_earned = "SELECT SUM(puntos) AS total_earned_points FROM puntos_usuario WHERE usuario_id = ?";
    $stmt_earned = $conn->prepare($query_earned);
    $stmt_earned->bind_param('i', $employee_id);
    $stmt_earned->execute();
    $result_earned = $stmt_earned->get_result();
    $earned_points = $result_earned->fetch_assoc()['total_earned_points'] ?? 0;

    // Suma todos los puntos canjeados (aprobados o completados)
    $query_redeemed = "SELECT SUM(puntos_canjeados) AS total_redeemed_points FROM canjes WHERE usuario_id = ? AND (estado = 'aprobado' OR estado = 'completado')";
    $stmt_redeemed = $conn->prepare($query_redeemed);
    $stmt_redeemed->bind_param('i', $employee_id);
    $stmt_redeemed->execute();
    $result_redeemed = $stmt_redeemed->get_result();
    $redeemed_points = $result_redeemed->fetch_assoc()['total_redeemed_points'] ?? 0;

    return $earned_points - $redeemed_points;
}

function getRedeemedPoints($employee_id) {
    global $conn;
    $query = "SELECT b.nombre AS name, c.puntos_canjeados AS points_redeemed, c.fecha_canje AS redemption_date
              FROM canjes c
              JOIN beneficios b ON c.beneficio_id = b.id
              WHERE c.usuario_id = ? AND (c.estado = 'aprobado' OR c.estado = 'completado')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getExpiringPoints($employee_id) {
    global $conn;
    $query = "SELECT puntos, fecha_expiracion AS expiration_date
              FROM puntos_usuario
              WHERE usuario_id = ? AND fecha_expiracion IS NOT NULL AND fecha_expiracion > NOW()
              ORDER BY fecha_expiracion ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getBenefits() {
    global $conn;
    $query = "SELECT nombre, descripcion, puntos_requeridos AS points_value, fecha_expiracion, estado, created_at FROM beneficios";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getHelp() {
    return "Si tienes algún problema, contacta con soporte en soporte@example.com";
}

function getRedemptionMethods() {
    return ["Método 1", "Método 2", "Método 3"];
}

function validateEmployeeData($data) {
    $errors = [];

    if (empty($data['first_name']) || empty($data['last_name'])) {
        $errors[] = "El nombre y el apellido son obligatorios.";
    }

    if (empty($data['document_number'])) {
        $errors[] = "El número de documento es obligatorio.";
    }

    if (empty($data['birth_date'])) {
        $errors[] = "La fecha de nacimiento es obligatoria.";
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El correo electrónico no es válido.";
    }

    if (empty($data['phone_number'])) {
        $errors[] = "El número de celular es obligatorio.";
    }

    if (empty($data['city'])) {
        $errors[] = "La ciudad de residencia es obligatoria.";
    }

    return $errors;
}

function getAllEmployees() {
    global $conn;
    $query = "SELECT id, first_name, last_name FROM employees";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function checkAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
}

/**
 * Obtiene todos los usuarios del sistema
 */
function getAllUsers() {
    global $conn;
    $sql = "SELECT id, nombre, email FROM usuarios WHERE activo = 1 ORDER BY nombre";
    $result = $conn->query($sql);
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    return $usuarios;
}

/**
 * Obtiene los detalles de un usuario por su ID
 */
function getUserDetails($user_id) {
    global $conn;
    $query = "SELECT id, nombre, email, telefono, direccion, fecha_nacimiento, rol, imagen_perfil FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
