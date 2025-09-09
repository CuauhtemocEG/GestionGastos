<?php
// Iniciar sesión solo si no existe una activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Registrar logout en logs si es necesario (opcional)
if (isset($_SESSION['user_id'])) {
    // Aquí podrías agregar logging si lo necesitas
    // Por ahora solo limpiamos la sesión
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
