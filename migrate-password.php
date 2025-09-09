<?php
require_once 'config.php';

echo "<h2>🔄 Migración de Contraseñas a Hash Seguro</h2>";

// Email del usuario existente
$email = 'cencarnacion@kallijaguar-inventory.com';
$password_texto_plano = 'password';

// Verificar si el usuario existe
$sql = "SELECT id, email, password_hash FROM usuarios WHERE email = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "✅ Usuario encontrado: " . $user['email'] . "<br>";
    
    // Verificar si la contraseña actual es un hash o texto plano
    if (password_verify($password_texto_plano, $user['password_hash'])) {
        echo "ℹ️ La contraseña ya está hasheada correctamente.<br>";
    } else {
        // La contraseña probablemente está en texto plano, vamos a hashearla
        $nuevo_hash = password_hash($password_texto_plano, PASSWORD_BCRYPT);
        
        $sql_update = "UPDATE usuarios SET password_hash = ? WHERE id = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param('si', $nuevo_hash, $user['id']);
        
        if ($stmt_update->execute()) {
            echo "✅ Contraseña actualizada a hash seguro.<br>";
            echo "Email: $email<br>";
            echo "Password: $password_texto_plano<br>";
            echo "Hash generado: " . substr($nuevo_hash, 0, 30) . "...<br>";
        } else {
            echo "❌ Error al actualizar contraseña: " . $conexion->error . "<br>";
        }
    }
} else {
    echo "❌ Usuario no encontrado con email: $email<br>";
    echo "Usuarios disponibles en la base de datos:<br>";
    
    // Mostrar todos los usuarios
    $sql_all = "SELECT id, email, nombre_completo FROM usuarios";
    $result_all = $conexion->query($sql_all);
    
    echo "<table border='1' style='margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Nombre</th></tr>";
    while ($row = $result_all->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . ($row['nombre_completo'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><hr><br>";
echo "<a href='auth/login.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Login</a>";
echo "<br><br>";
echo "<a href='https://pos.kallijaguar-inventory.com/auth/login.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Login en Producción</a>";

mysqli_close($conexion);
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: #f8fafc;
}
table {
    border-collapse: collapse;
    width: 100%;
}
th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
}
</style>
