<?php
// Verificar que el usuario sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header('Location: ?page=home');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones (activar/desactivar usuario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    if ($action === 'toggle_status' && $user_id > 0) {
        // No permitir desactivar al propio admin
        if ($user_id === $_SESSION['user_id']) {
            $mensaje = 'No puedes desactivar tu propia cuenta de administrador';
            $tipo_mensaje = 'error';
        } else {
            $sql = "UPDATE usuarios SET activo = NOT activo WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('i', $user_id);
            
            if ($stmt->execute()) {
                $mensaje = 'Estado del usuario actualizado exitosamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar el usuario: ' . $conexion->error;
                $tipo_mensaje = 'error';
            }
            $stmt->close();
        }
    }
}

// Obtener filtros
$filtro_estado = $_GET['estado'] ?? 'todos';
$busqueda = $_GET['busqueda'] ?? '';

// Construir consulta con filtros
$where = "1 = 1";
$params = [];
$types = "";

if ($filtro_estado !== 'todos') {
    $where .= " AND activo = ?";
    $params[] = ($filtro_estado === 'activos') ? 1 : 0;
    $types .= "i";
}

if (!empty($busqueda)) {
    $where .= " AND (username LIKE ? OR email LIKE ? OR nombre_completo LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "sss";
}

// Obtener usuarios
$sql = "SELECT * FROM usuarios WHERE $where ORDER BY creado_en DESC";
$stmt = $conexion->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Estadísticas
$total_usuarios = count($usuarios);
$usuarios_activos = count(array_filter($usuarios, function($u) { return $u['activo']; }));
$usuarios_inactivos = $total_usuarios - $usuarios_activos;

// Usuarios registrados en los últimos 30 días
$sql_recientes = "SELECT COUNT(*) as count FROM usuarios WHERE creado_en >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result_recientes = $conexion->query($sql_recientes);
$usuarios_recientes = $result_recientes->fetch_assoc()['count'];
?>

<!-- Panel de Administración de Usuarios -->
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Administración de Usuarios</h1>
                    <p class="text-gray-600">Gestiona los usuarios del sistema</p>
                </div>
            </div>
            <div class="mt-4 lg:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Panel de Admin
                </span>
            </div>
        </div>
    </div>

    <!-- Mostrar mensajes -->
    <?php if ($mensaje): ?>
    <div class="rounded-md p-4 <?= $tipo_mensaje === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' ?>">
        <div class="flex">
            <div class="flex-shrink-0">
                <?php if ($tipo_mensaje === 'success'): ?>
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                <?php else: ?>
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium <?= $tipo_mensaje === 'success' ? 'text-green-800' : 'text-red-800' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Usuarios -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Usuarios</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $total_usuarios ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Usuarios Activos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Usuarios Activos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $usuarios_activos ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Usuarios Inactivos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Usuarios Inactivos</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $usuarios_inactivos ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Nuevos (30 días) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Nuevos (30 días)</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $usuarios_recientes ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <svg class="w-5 h-5 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
            </svg>
            Filtros y Búsqueda
        </h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="page" value="admin-usuarios">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="estado" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    <option value="todos" <?= $filtro_estado === 'todos' ? 'selected' : '' ?>>Todos los usuarios</option>
                    <option value="activos" <?= $filtro_estado === 'activos' ? 'selected' : '' ?>>Solo activos</option>
                    <option value="inactivos" <?= $filtro_estado === 'inactivos' ? 'selected' : '' ?>>Solo inactivos</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" 
                       placeholder="Nombre, email o username..."
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                Lista de Usuarios (<?= count($usuarios) ?> resultados)
            </h2>
        </div>
        
        <?php if (empty($usuarios)): ?>
        <div class="text-center py-12">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="text-gray-500 text-lg mb-2">No se encontraron usuarios</p>
            <p class="text-gray-400">Intenta ajustar los filtros de búsqueda</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Último Login
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Registrado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r <?= $usuario['username'] === 'admin' ? 'from-purple-400 to-purple-600' : 'from-blue-400 to-blue-600' ?> flex items-center justify-center">
                                        <span class="text-white font-medium text-sm">
                                            <?= strtoupper(substr($usuario['nombre_completo'] ?: $usuario['username'], 0, 2)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($usuario['nombre_completo'] ?: $usuario['username']) ?>
                                        </div>
                                        <?php if ($usuario['username'] === 'admin'): ?>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Admin
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @<?= htmlspecialchars($usuario['username']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($usuario['email']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $usuario['activo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3"/>
                                </svg>
                                <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($usuario['ultimo_login']): ?>
                                <div class="flex flex-col">
                                    <span><?= date('d/m/Y', strtotime($usuario['ultimo_login'])) ?></span>
                                    <span class="text-xs text-gray-400"><?= date('H:i', strtotime($usuario['ultimo_login'])) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 italic">Nunca</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex flex-col">
                                <span><?= date('d/m/Y', strtotime($usuario['creado_en'])) ?></span>
                                <span class="text-xs text-gray-400"><?= date('H:i', strtotime($usuario['creado_en'])) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?php if ($usuario['id'] === $_SESSION['user_id']): ?>
                                <span class="text-gray-400 italic">Tu cuenta</span>
                            <?php else: ?>
                                <form method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas <?= $usuario['activo'] ? 'desactivar' : 'activar' ?> este usuario?')">
                                    <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <button type="submit" class="<?= $usuario['activo'] ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' ?> font-medium transition-colors">
                                        <?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
