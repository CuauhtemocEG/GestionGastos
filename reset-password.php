<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Gestión de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <?php
        include 'config.php';
        include 'auth.php';
        
        $message = '';
        $error = '';
        $step = $_GET['step'] ?? 'request';
        $token = $_GET['token'] ?? '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($step === 'request') {
                $email = $_POST['email'] ?? '';
                
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $resetToken = $auth->generatePasswordResetToken($email);
                    if ($resetToken) {
                        // Aquí enviarías el email con el token
                        // Por ahora, mostraremos el link directo
                        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?step=reset&token=" . $resetToken;
                        $message = "Se ha generado un enlace de recuperación. <br><br>
                                   <strong>Enlace de recuperación:</strong><br>
                                   <a href='$resetLink' class='text-indigo-600 hover:text-indigo-800 break-all'>$resetLink</a><br><br>
                                   <small class='text-gray-600'>En un entorno de producción, este enlace se enviaría por email.</small>";
                    } else {
                        $error = 'No se encontró una cuenta con ese email.';
                    }
                } else {
                    $error = 'Por favor ingresa un email válido.';
                }
            } elseif ($step === 'reset') {
                $newPassword = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (strlen($newPassword) < 6) {
                    $error = 'La contraseña debe tener al menos 6 caracteres.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Las contraseñas no coinciden.';
                } else {
                    if ($auth->resetPassword($token, $newPassword)) {
                        $message = 'Contraseña actualizada exitosamente. <a href="login.php" class="text-indigo-600 hover:text-indigo-800">Iniciar sesión</a>';
                    } else {
                        $error = 'Token inválido o expirado.';
                    }
                }
            }
        }
        ?>
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Recuperar Contraseña</h1>
            <p class="text-gray-600">
                <?= $step === 'request' ? 'Ingresa tu email para recuperar tu contraseña' : 'Ingresa tu nueva contraseña' ?>
            </p>
        </div>
        
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($step === 'request'): ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="tu@email.com">
                </div>
                
                <button type="submit" 
                        class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium">
                    Enviar Enlace de Recuperación
                </button>
            </form>
        <?php elseif ($step === 'reset'): ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Mínimo 6 caracteres">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmar Contraseña
                    </label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required
                           minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Confirma tu contraseña">
                </div>
                
                <button type="submit" 
                        class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium">
                    Actualizar Contraseña
                </button>
            </form>
        <?php endif; ?>
        
        <div class="mt-6 text-center">
            <a href="login.php" class="text-indigo-600 hover:text-indigo-800 text-sm">
                ← Volver al Login
            </a>
        </div>
    </div>
</body>
</html>
