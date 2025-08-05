<?php
// CORRECCIONES DE SEGURIDAD PARA PUNTOS ESTILO

// 1. FUNCIÓN PARA SANITIZAR SALIDA (Anti-XSS)
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// 2. FUNCIÓN PARA VALIDAR ARCHIVOS
function validateFilePath($path) {
    // Prevenir directory traversal
    $path = str_replace(['../', '..\\', '../', '..\\'], '', $path);
    
    // Solo permitir caracteres seguros
    if (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $path)) {
        return false;
    }
    
    return $path;
}

// 3. FUNCIÓN PARA VALIDAR ENTRADA
function validateInput($input, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT);
        case 'string':
            return filter_var($input, FILTER_SANITIZE_STRING);
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// 4. FUNCIÓN PARA GENERAR TOKEN CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 5. FUNCIÓN PARA VALIDAR TOKEN CSRF
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 6. FUNCIÓN PARA LOGGING SEGURO
function secureLog($message, $level = 'INFO') {
    $logFile = 'logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = "[$timestamp] [$level] IP: $ip | User-Agent: $userAgent | Message: $message" . PHP_EOL;
    
    // Crear directorio si no existe
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// 7. FUNCIÓN PARA VALIDAR SESIÓN
function validateSession() {
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        secureLog('Intento de acceso sin sesión válida', 'WARNING');
        header('Location: login.php');
        exit();
    }
    
    // Regenerar ID de sesión periódicamente
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// 8. FUNCIÓN PARA VALIDAR PERMISOS DE ADMIN
function requireAdmin() {
    validateSession();
    if (!isset($_SESSION['user']['rol']) || $_SESSION['user']['rol'] !== 'admin') {
        secureLog('Intento de acceso no autorizado a área de admin por usuario: ' . $_SESSION['user']['correo'], 'WARNING');
        header('Location: perfil.php');
        exit();
    }
}

// 9. FUNCIÓN PARA PREPARAR CONSULTAS SQL SEGURAS
function prepareQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        secureLog('Error preparando consulta SQL: ' . $conn->error, 'ERROR');
        return false;
    }
    
    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }
    
    return $stmt;
}

// 10. CONFIGURACIÓN DE HEADERS DE SEGURIDAD
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
}

// Aplicar headers de seguridad automáticamente
setSecurityHeaders();
?>