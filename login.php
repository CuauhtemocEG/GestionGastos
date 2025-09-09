<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestión de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-500 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">GastosApp</h1>
            <p class="text-gray-600">Inicia sesión para continuar</p>
        </div>
        
        <?php
        include 'config.php';
        include 'auth.php';
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($auth->login($username, $password)) {
                header('Location: index.php');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        }
        ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Usuario o Email
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Ingresa tu usuario o email">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Contraseña
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Ingresa tu contraseña">
            </div>
            
            <button type="submit" 
                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 font-medium">
                Iniciar Sesión
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="reset-password.php" class="text-indigo-600 hover:text-indigo-800 text-sm">
                ¿Olvidaste tu contraseña?
            </a>
        </div>
    </div>
</body>
</html>
