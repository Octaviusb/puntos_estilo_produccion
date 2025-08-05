<?php
require_once 'db_connect.php';

/**
 * Obtiene el total de puntos de un usuario
 */
function getTotalPuntosUsuario($usuario_id) {
    global $conn;
    $sql = "SELECT COALESCE(SUM(puntos), 0) as total 
            FROM puntos_usuario 
            WHERE usuario_id = ? 
            AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Asigna puntos a un usuario
 */
function asignarPuntos($usuario_id, $puntos, $logro_id = null, $descripcion = null, $fecha_expiracion = null) {
    global $conn;
    $sql = "INSERT INTO puntos_usuario (usuario_id, logro_id, puntos, descripcion, fecha_expiracion) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $usuario_id, $logro_id, $puntos, $descripcion, $fecha_expiracion);
    return $stmt->execute();
}

/**
 * Obtiene el historial de puntos de un usuario
 */
function getHistorialPuntos($usuario_id) {
    global $conn;
    $sql = "SELECT pu.*, l.nombre as logro_nombre 
            FROM puntos_usuario pu 
            LEFT JOIN logros l ON pu.logro_id = l.id 
            WHERE pu.usuario_id = ? 
            ORDER BY pu.fecha_asignacion DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $historial = [];
    while ($row = $result->fetch_assoc()) {
        $historial[] = $row;
    }
    return $historial;
}

/**
 * Verifica si un usuario tiene suficientes puntos para canjear un beneficio
 */
function verificarPuntosSuficientes($usuario_id, $puntos_requeridos) {
    $puntos_disponibles = getTotalPuntosUsuario($usuario_id);
    return $puntos_disponibles >= $puntos_requeridos;
}

/**
 * Realiza el canje de puntos por un beneficio
 */
function canjearPuntos($usuario_id, $beneficio_id, $puntos_canjeados) {
    global $conn;
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Verificar stock del beneficio
        $sql = "SELECT stock FROM beneficios WHERE id = ? AND activo = 1 FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $beneficio_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $beneficio = $result->fetch_assoc();
        
        if (!$beneficio || ($beneficio['stock'] != -1 && $beneficio['stock'] <= 0)) {
            throw new Exception("Beneficio no disponible");
        }
        
        // Verificar puntos suficientes
        if (!verificarPuntosSuficientes($usuario_id, $puntos_canjeados)) {
            throw new Exception("Puntos insuficientes");
        }
        
        // Registrar el canje
        $sql = "INSERT INTO canjes (usuario_id, beneficio_id, puntos_canjeados) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $usuario_id, $beneficio_id, $puntos_canjeados);
        $stmt->execute();
        
        // Descontar puntos
        $sql = "INSERT INTO puntos_usuario (usuario_id, puntos, descripcion) 
                VALUES (?, ?, 'Canje de puntos por beneficio')";
        $stmt = $conn->prepare($sql);
        $puntos_negativos = -$puntos_canjeados;
        $stmt->bind_param("ii", $usuario_id, $puntos_negativos);
        $stmt->execute();
        
        // Actualizar stock si no es ilimitado
        if ($beneficio['stock'] != -1) {
            $sql = "UPDATE beneficios SET stock = stock - 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $beneficio_id);
            $stmt->execute();
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Obtiene los beneficios disponibles
 */
function getBeneficiosDisponibles() {
    global $conn;
    $sql = "SELECT * FROM beneficios WHERE activo = 1 AND (stock = -1 OR stock > 0)";
    $result = $conn->query($sql);
    $beneficios = [];
    while ($row = $result->fetch_assoc()) {
        $beneficios[] = $row;
    }
    return $beneficios;
}

/**
 * Obtiene los puntos próximos a vencer
 */
function getPuntosPorVencer($usuario_id, $dias = 30) {
    global $conn;
    $sql = "SELECT * FROM puntos_usuario 
            WHERE usuario_id = ? 
            AND fecha_expiracion IS NOT NULL 
            AND fecha_expiracion BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
            ORDER BY fecha_expiracion ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $dias);
    $stmt->execute();
    $result = $stmt->get_result();
    $puntos = [];
    while ($row = $result->fetch_assoc()) {
        $puntos[] = $row;
    }
    return $puntos;
} 