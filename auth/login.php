<?php
// Iniciar sesión solo si no existe una activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $sql = "SELECT id, email, password_hash, nombre_completo, activo FROM usuarios WHERE email = ? AND activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Verificar contraseña - primero con hash, luego texto plano para migración
            $password_valid = false;
            
            if (password_verify($password, $user['password_hash'])) {
                // Contraseña con hash válida
                $password_valid = true;
            } elseif ($user['password_hash'] === $password) {
                // Contraseña en texto plano (para migración)
                $password_valid = true;
                
                // Actualizar a hash automáticamente
                $nuevo_hash = password_hash($password, PASSWORD_BCRYPT);
                $sql_update = "UPDATE usuarios SET password_hash = ? WHERE id = ?";
                $stmt_update = $conexion->prepare($sql_update);
                $stmt_update->bind_param('si', $nuevo_hash, $user['id']);
                $stmt_update->execute();
            }
            
            if ($password_valid) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                $_SESSION['rol'] = 'admin'; // Valor por defecto
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                
                // Actualizar último login en la tabla usuarios
                $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                
                header('Location: ../index.php');
                exit();
            } else {
                $error = 'Credenciales incorrectas.';
            }
        } else {
            $error = 'Usuario no encontrado o inactivo.';
        }
    }
}

// Procesar recuperación de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'recovery') {
    $email = trim($_POST['recovery_email'] ?? '');
    
    if (empty($email)) {
        $error = 'Por favor, ingresa tu email.';
    } else {
        $sql = "SELECT id, nombre_completo FROM usuarios WHERE email = ? AND activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Generar token de recuperación
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $sql = "INSERT INTO recuperacion_password (usuario_id, token, expira, usado) VALUES (?, ?, ?, 0)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('iss', $user['id'], $token, $expira);
            $stmt->execute();
            
            // Aquí normalmente enviarías un email
            // Por ahora, mostraremos el link
            $recovery_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/reset-password.php?token=" . $token;
            $success = "Se ha generado un enlace de recuperación: <br><small><a href='$recovery_link' target='_blank'>$recovery_link</a></small>";
        } else {
            $error = 'Email no encontrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8" x-data="{ showRecovery: false }">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Sistema de Gastos</h1>
            <p class="text-gray-600" x-text="showRecovery ? 'Recuperar Contraseña' : 'Iniciar Sesión'"></p>
        </div>

        <!-- Mensajes -->
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <strong>Éxito:</strong> <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Mensajes de timeout -->
        <?php if (isset($_GET['timeout'])): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4" role="alert">
                <strong>Sesión expirada:</strong> Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.
            </div>
        <?php endif; ?>

        <!-- Formulario de Login -->
        <form method="POST" x-show="!showRecovery" x-transition class="space-y-6">
            <input type="hidden" name="action" value="login">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="tu@email.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="••••••••">
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-purple-700 transition duration-200">
                Iniciar Sesión
            </button>
        </form>

        <!-- Formulario de Recuperación -->
        <form method="POST" x-show="showRecovery" x-transition class="space-y-6">
            <input type="hidden" name="action" value="recovery">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="recovery_email" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="tu@email.com">
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:from-green-600 hover:to-blue-600 transition duration-200">
                Enviar Enlace de Recuperación
            </button>
        </form>

        <!-- Toggle entre formularios -->
        <div class="mt-6 text-center">
            <button @click="showRecovery = !showRecovery" 
                    class="text-blue-600 hover:text-blue-800 font-medium" 
                    x-text="showRecovery ? '← Volver al Login' : '¿Olvidaste tu contraseña?'">
            </button>
        </div>

        <!-- Demo credentials -->
        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600 font-medium mb-2">Credenciales de demo:</p>
            <p class="text-xs text-gray-500">Email: admin@gastosapp.com</p>
            <p class="text-xs text-gray-500">Contraseña: admin123</p>
        </div>
    </div>
</body>
</html>
