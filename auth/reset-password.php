<?php
// Iniciar sesión solo si no existe una activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

$error = '';
$success = '';

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $sql = "SELECT r.id, r.usuario_id, u.email, u.nombre_completo 
            FROM recuperacion_password r 
            JOIN usuarios u ON r.usuario_id = u.id 
            WHERE r.token = ? AND r.expira > NOW() AND r.usado = 0";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($recovery = $result->fetch_assoc()) {
        // Token válido
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($new_password) || empty($confirm_password)) {
                $error = 'Por favor, completa todos los campos.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (strlen($new_password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                // Actualizar contraseña
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $sql = "UPDATE usuarios SET password_hash = ? WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('si', $hashed_password, $recovery['usuario_id']);
                $stmt->execute();
                
                // Marcar token como usado
                $sql = "UPDATE recuperacion_password SET usado = 1 WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('i', $recovery['id']);
                $stmt->execute();
                
                $success = 'Contraseña actualizada correctamente. Puedes iniciar sesión.';
            }
        }
    } else {
        $error = 'Token inválido o expirado.';
    }
} else {
    $error = 'Token no proporcionado.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2v6m0 0V9a2 2 0 00-2-2M9 7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V9a2 2 0 00-2-2"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Restablecer Contraseña</h1>
            <?php if (isset($recovery)): ?>
                <p class="text-gray-600">Para: <?= htmlspecialchars($recovery['email']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Mensajes -->
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
                <div class="mt-4">
                    <a href="login.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Ir al Login
                    </a>
                </div>
            </div>
        <?php elseif (isset($recovery)): ?>
            <!-- Formulario de nueva contraseña -->
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                    <input type="password" name="new_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Mínimo 6 caracteres">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contraseña</label>
                    <input type="password" name="confirm_password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Repite la contraseña">
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:from-green-600 hover:to-blue-600 transition duration-200">
                    Actualizar Contraseña
                </button>
            </form>
        <?php endif; ?>

        <!-- Volver al login -->
        <div class="mt-6 text-center">
            <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Volver al Login
            </a>
        </div>
    </div>
</body>
</html>
