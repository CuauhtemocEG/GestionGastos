<?php
global $conexion;

$success_message = '';
$error_message = '';

// Procesar cambios de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (!empty($nombre) && !empty($email)) {
                $sql = "UPDATE usuarios SET nombre_completo = ?, email = ? WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('ssi', $nombre, $email, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $_SESSION['nombre_completo'] = $nombre;
                    $_SESSION['email'] = $email;
                    $success_message = 'Perfil actualizado correctamente';
                } else {
                    $error_message = 'Error al actualizar el perfil';
                }
            }
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (strlen($new_password) < 6) {
                $error_message = 'La nueva contraseña debe tener al menos 6 caracteres';
            } elseif ($new_password !== $confirm_password) {
                $error_message = 'Las contraseñas no coinciden';
            } else {
                // Verificar contraseña actual
                $sql = "SELECT password_hash FROM usuarios WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if (password_verify($current_password, $user['password_hash'])) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE usuarios SET password_hash = ? WHERE id = ?";
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param('si', $new_hash, $_SESSION['user_id']);
                    
                    if ($stmt->execute()) {
                        $success_message = 'Contraseña cambiada correctamente';
                    } else {
                        $error_message = 'Error al cambiar la contraseña';
                    }
                } else {
                    $error_message = 'Contraseña actual incorrecta';
                }
            }
            break;
            
        case 'export_data':
            // Lógica de exportación
            $fecha_inicio = $_POST['export_fecha_inicio'] ?? date('Y-m-01');
            $fecha_fin = $_POST['export_fecha_fin'] ?? date('Y-m-d');
            
            // Aquí iría la lógica de exportación
            $success_message = 'Exportación completada (funcionalidad en desarrollo)';
            break;
    }
}

// Obtener datos del usuario
$sql = "SELECT nombre_completo, email FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Agregar fecha de creación por defecto si no existe
$user_data['fecha_creacion'] = date('Y-m-d'); // Fecha actual por defecto

// Estadísticas del usuario con manejo de errores
$stats = [
    'total_gastos' => 0,
    'total_pagos' => 0,
    'suma_gastos' => 0,
    'suma_pagos' => 0
];

try {
    // Verificar si las tablas existen antes de consultar
    $tables_check = $conexion->query("SHOW TABLES LIKE 'Gastos'");
    if ($tables_check && $tables_check->num_rows > 0) {
        $gastos_result = $conexion->query("SELECT COUNT(*) as total, COALESCE(SUM(Monto), 0) as suma FROM Gastos");
        if ($gastos_result) {
            $gastos_data = $gastos_result->fetch_assoc();
            $stats['total_gastos'] = $gastos_data['total'];
            $stats['suma_gastos'] = $gastos_data['suma'];
        }
    }
    
    $tables_check = $conexion->query("SHOW TABLES LIKE 'Pagos'");
    if ($tables_check && $tables_check->num_rows > 0) {
        $pagos_result = $conexion->query("SELECT COUNT(*) as total, COALESCE(SUM(monto), 0) as suma FROM Pagos");
        if ($pagos_result) {
            $pagos_data = $pagos_result->fetch_assoc();
            $stats['total_pagos'] = $pagos_data['total'];
            $stats['suma_pagos'] = $pagos_data['suma'];
        }
    }
} catch (Exception $e) {
    // En caso de error, mantener los valores por defecto
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
}
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cog text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
                <p class="text-gray-600">Administra tu cuenta y preferencias del sistema</p>
            </div>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if ($success_message): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-check-circle text-green-400 mt-0.5 mr-3"></i>
                <p class="text-green-700"><?= htmlspecialchars($success_message) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-400 mt-0.5 mr-3"></i>
                <p class="text-red-700"><?= htmlspecialchars($error_message) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Información del Perfil -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user mr-2 text-blue-600"></i>
                Información del Perfil
            </h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_profile">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($user_data['nombre_completo']) ?>" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Miembro desde</label>
                    <input type="text" value="Registro no disponible" 
                           class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50" readonly>
                    <p class="text-xs text-gray-500 mt-1">Esta información se agregará en futuras actualizaciones</p>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">
                    Actualizar Perfil
                </button>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-key mr-2 text-green-600"></i>
                Cambiar Contraseña
            </h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="change_password">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña Actual</label>
                    <input type="password" name="current_password" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                    <input type="password" name="new_password" minlength="6"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirm_password" minlength="6"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" required>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition duration-200">
                    Cambiar Contraseña
                </button>
            </form>
        </div>
    </div>

    <!-- Estadísticas de la Cuenta -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
            Estadísticas de tu Cuenta
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-receipt text-red-600 text-xl"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_gastos']) ?></div>
                <div class="text-sm text-gray-600">Gastos Registrados</div>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-credit-card text-green-600 text-xl"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_pagos']) ?></div>
                <div class="text-sm text-gray-600">Pagos Registrados</div>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">$<?= number_format($stats['suma_gastos'], 0, ',', '.') ?></div>
                <div class="text-sm text-gray-600">Total Gastado</div>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900">$<?= number_format($stats['suma_pagos'], 0, ',', '.') ?></div>
                <div class="text-sm text-gray-600">Total Ingresos</div>
            </div>
        </div>
    </div>

    <!-- Herramientas Avanzadas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Exportar Datos -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-download mr-2 text-indigo-600"></i>
                Exportar Datos
            </h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="export_data">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" name="export_fecha_inicio" value="<?= date('Y-m-01') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" name="export_fecha_fin" value="<?= date('Y-m-d') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">
                    Exportar a Excel
                </button>
            </form>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info-circle mr-2 text-gray-600"></i>
                Información del Sistema
            </h2>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Versión:</span>
                    <span class="font-medium">2.0.0 Professional</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Última actualización:</span>
                    <span class="font-medium"><?= date('d/m/Y') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Base de datos:</span>
                    <span class="font-medium">MySQL 8.0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Estado:</span>
                    <span class="text-green-600 font-medium">🟢 Operativo</span>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t">
                <a href="?logout=1" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-200 text-center inline-block">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</div>
