<?php
// Mostrar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$host = 'localhost';
$usuario = 'kallijag_stage';
$clave = 'uNtiL.horSe@5';
$baseDeDatos = 'kallijag_inventory_stage';

try {
    $conexion = new mysqli($host, $usuario, $clave, $baseDeDatos);
    
    if ($conexion->connect_error) {
        die("Conexión fallida: " . $conexion->connect_error);
    }
    
    // Configurar charset
    $conexion->set_charset("utf8");
    
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>