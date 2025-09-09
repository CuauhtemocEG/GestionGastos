<?php
// Script para crear usuario administrador de prueba
include 'config.php';

// Crear usuario administrador
$username = 'admin';
$email = 'admin@gastosapp.com';
$password = 'admin123'; // Cambia esta contraseña
$nombre_completo = 'Administrador del Sistema';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Verificar si el usuario ya existe
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "El usuario administrador ya existe.\n";
} else {
    // Insertar usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios (username, email, password_hash, nombre_completo, activo) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param('ssss', $username, $email, $password_hash, $nombre_completo);
    
    if ($stmt->execute()) {
        echo "Usuario administrador creado exitosamente!\n";
        echo "Usuario: $username\n";
        echo "Email: $email\n";
        echo "Contraseña: $password\n";
        echo "\n¡IMPORTANTE: Cambia la contraseña después del primer login!\n";
    } else {
        echo "Error al crear el usuario: " . $conexion->error . "\n";
    }
}

// Crear otro usuario de prueba
$username2 = 'usuario';
$email2 = 'usuario@gastosapp.com';
$password2 = 'usuario123';
$nombre_completo2 = 'Usuario de Prueba';
$password_hash2 = password_hash($password2, PASSWORD_DEFAULT);

$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
$stmt->bind_param('ss', $username2, $email2);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "El usuario de prueba ya existe.\n";
} else {
    $stmt = $conexion->prepare("INSERT INTO usuarios (username, email, password_hash, nombre_completo, activo) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param('ssss', $username2, $email2, $password_hash2, $nombre_completo2);
    
    if ($stmt->execute()) {
        echo "Usuario de prueba creado exitosamente!\n";
        echo "Usuario: $username2\n";
        echo "Email: $email2\n";
        echo "Contraseña: $password2\n";
    } else {
        echo "Error al crear el usuario de prueba: " . $conexion->error . "\n";
    }
}

echo "\n¡Setup completado! Ahora puedes usar el sistema de login.\n";
?>
