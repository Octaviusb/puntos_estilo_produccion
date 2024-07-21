<?php
include_once 'includes/db_connect.php';

function getUserByEmail($conn, $email) {
    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function validateUser($conn, $email, $password) {
    $user = getUserByEmail($conn, $email);
    if ($user && password_verify($password, $user['contraseña'])) {
        return $user;
    }
    return null;
}

// Función para obtener los puntos de un empleado
function getEmployeePoints($employee_id) {
    global $conn;
    $query = "SELECT puntos FROM empleados WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $stmt->bind_result($puntos);
    if ($stmt->fetch()) {
        return $puntos;
    } else {
        return 0; // Valor predeterminado si no se encuentra el registro
    }
}

// Función para obtener los puntos próximos a vencer de un empleado
function getExpiringPoints($employee_id) {
    global $conn;
    $sql = "SELECT fecha_vencimiento, puntos FROM puntos WHERE usuario_id = ? AND fecha_vencimiento > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener el catálogo de beneficios
function getBenefits() {
    global $conn;
    $sql = "SELECT nombre, descripcion FROM beneficios";
    $result = $conn->query($sql);
    $beneficios = [];
    while ($row = $result->fetch_assoc()) {
        $beneficios[] = $row;
    }
    return $beneficios;
}

// Función para enviar notificación por correo electrónico
function sendEmailNotification($email, $message) {
    $subject = "Notificación de puntos";
    $headers = "From: notificaciones@puntosestilo.com";

    $success = mail($email, $subject, $message, $headers);

    if ($success) {
        echo "Correo electrónico enviado exitosamente a $email";
    } else {
        echo "Error al enviar el correo electrónico";
    }
}

// Función para obtener el mensaje de ayuda
function getHelp() {
    return "¿Necesitas ayuda con un bono no entregado? Contacta con soporte";
}

// Función para obtener los métodos de canje
function getRedemptionMethods() {
    return [
        'Mercancía',
        'Experiencias',
        'Tarjetas de regalo'
    ];
}

// Función para asignación automática de puntos
function asignacionAutomaticaPuntos($conn, $userId) {
    $sql = "SELECT id, descripcion, puntos FROM reglas_asignacion WHERE activa = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Aquí verificaríamos si se cumple la regla y asignaríamos los puntos
            // Por ejemplo, si la regla es "Completar Curso de Ventas" y el usuario ha completado el curso, asignamos los puntos
            if ($row['descripcion'] === "Completar Curso de Ventas") {
                $sqlAsignacion = "INSERT INTO puntos (usuario_id, puntos, fecha_asignacion) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($sqlAsignacion);
                $stmt->bind_param("ii", $userId, $row['puntos']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Función para generar un código OTP
function generarOTP($longitud = 6) {
    $caracteres = '0123456789';
    $codigo_otp = '';
    $max = strlen($caracteres) - 1;
    for ($i = 0; $i < $longitud; $i++) {
        $codigo_otp .= $caracteres[random_int(0, $max)];
    }
    return $codigo_otp;
}

// Función para verificar un código OTP
function verificarOTP($otp_ingresado, $otp_guardado) {
    return $otp_ingresado === $otp_guardado;
}

// Función para eliminar puntos vencidos
function deleteExpiredPoints() {
    global $conn;
    $sql = "DELETE FROM puntos WHERE fecha_vencimiento < NOW()";
    return $conn->query($sql);
}

// Función para autorizar la creación de un perfil
function authorizeProfileCreation($profileId) {
    global $conn;
    $sql = "UPDATE perfiles SET autorizado = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $profileId);
    return $stmt->execute();
}
?>
