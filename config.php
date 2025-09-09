<?php
// Mostrar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$host = 'localhost';
$usuario = 'kallijag_stage';
$clave = 'uNtiL.horSe@5';
$baseDeDatos = 'kallijag_pos_stage'; // Nombre correcto de la base de datos

try {
    $conexion = new mysqli($host, $usuario, $clave, $baseDeDatos);
    
    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }
    
    // Configurar charset
    $conexion->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para verificar autenticación
function verificarAutenticacion() {
    // Excluir páginas de auth del chequeo
    $current_file = basename($_SERVER['PHP_SELF']);
    $auth_pages = ['login.php', 'reset-password.php', 'logout.php'];
    
    if (in_array($current_file, $auth_pages)) {
        return true;
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: auth/login.php');
        exit();
    }
    
    // Verificar timeout de sesión (24 horas)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
        session_destroy();
        header('Location: auth/login.php?timeout=1');
        exit();
    }
    
    return true;
}

// Verificar autenticación automáticamente
verificarAutenticacion();

// Procesar logout
if (isset($_GET['logout'])) {
    header('Location: auth/logout.php');
    exit();
}
?>