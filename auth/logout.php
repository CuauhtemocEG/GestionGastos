<?php
session_start();

// Cerrar sesión en la base de datos si existe
if (isset($_SESSION['session_id'])) {
    include '../config.php';
    $sql = "UPDATE sesiones SET fecha_fin = NOW() WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $_SESSION['session_id']);
    $stmt->execute();
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: login.php');
exit();
?>
