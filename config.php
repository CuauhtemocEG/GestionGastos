<?php
session_start();

$host = 'localhost:3306';
$usuario = 'kallijag_stage';
$clave = 'uNtiL.horSe@5';
$baseDeDatos = 'kallijag_inventory_stage';

$conexion = new mysqli($host, $usuario, $clave, $baseDeDatos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Configurar charset
$conexion->set_charset("utf8");

// Incluir sistema de autenticación
require_once 'auth.php';
require_once 'GastosManager.php';

// Verificar si el usuario está autenticado (excepto para páginas de login)
$paginasPublicas = ['login.php', 'reset-password.php'];
$paginaActual = basename($_SERVER['PHP_SELF']);

if (!in_array($paginaActual, $paginasPublicas) && !$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Manejar logout
if (isset($_GET['logout'])) {
    $auth->logout();
}
?>