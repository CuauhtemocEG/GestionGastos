<?php
$host = 'localhost';
$usuario = 'root'; // tu usuario de base de datos
$clave = 'root';       // tu contraseña de base de datos
$baseDeDatos = 'gastos_diarios';

$conexion = new mysqli($host, $usuario, $clave, $baseDeDatos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>