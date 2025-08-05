<?php
// Configuración para InfinityFree
// CAMBIAR ESTAS CREDENCIALES POR LAS DE TU CUENTA INFINITYFREE

define('DB_HOST', 'sql113.infinityfree.com'); // Tu host
define('DB_USER', 'if0_39631209'); // Tu usuario
define('DB_PASS', 'Ener19447'); // Tu contraseña
define('DB_NAME', 'if0_39631209_puntosestilo'); // Tu base de datos

// Incluir funciones de seguridad
require_once __DIR__ . '/../SECURITY_FIXES.php';

// Conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");

// Configuración de la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores (deshabilitar en producción)
error_reporting(0);
ini_set('display_errors', 0);

// Función para sanitizar datos
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === 'admin';
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}
?>