<?php
include 'config.php';
include 'auth.php';
include 'GastosManager.php';

// Verificar autenticación
$auth->requireLogin();

// Obtener filtros
$filtros = [
    'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
    'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
    'tipo' => $_GET['tipo'] ?? 'todos',
    'metodo' => $_GET['metodo'] ?? 'todos',
    'monto_min' => $_GET['monto_min'] ?? '',
    'monto_max' => $_GET['monto_max'] ?? '',
    'descripcion' => $_GET['descripcion'] ?? '',
    'orden' => $_GET['orden'] ?? 'fecha_desc',
    'limite' => $_GET['limite'] ?? 25,
    'pagina' => $_GET['pagina'] ?? 1
];

// Manejo de logout
if (isset($_GET['logout'])) {
    $auth->logout();
}

// Obtener datos
$gastos = $gastosManager->obtenerGastosFiltrados($filtros);
$estadisticas = $gastosManager->obtenerEstadisticas($filtros);
$totalRegistros = $gastosManager->contarTotalRegistros('Gastos', $filtros);
$totalPaginas = ceil($totalRegistros / $filtros['limite']);

// Manejar edición de gastos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_gasto_id'])) {
    $id = intval($_POST['edit_gasto_id']);
    $descripcion = $_POST['edit_gasto_descripcion'];
    $monto = floatval($_POST['edit_gasto_monto']);
    $fecha = $_POST['edit_gasto_fecha'];
    $metodo = $_POST['edit_gasto_metodo'];
    $tipo = $_POST['edit_gasto_tipo'];
    
    $stmt = $conexion->prepare("UPDATE Gastos SET Descripcion=?, Monto=?, Fecha=?, Metodo=?, Tipo=? WHERE ID=?");
    $stmt->bind_param('sdsssi', $descripcion, $monto, $fecha, $metodo, $tipo, $id);
    $stmt->execute();
    $stmt->close();
    
    // Mantener filtros en la redirección
    $queryString = http_build_query($filtros);
    header('Location: dashboard.php?' . $queryString);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Navegación -->
    <nav class="bg-indigo-700 px-4 sm:px-8 py-4 shadow-lg">
        <div class="flex items-center justify-between">
            <span class="text-2xl font-bold text-white flex items-center gap-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10"></circle>
                </svg>
                GastosApp
            </span>
            <div class="flex items-center gap-4">
                <span class="text-white">Hola, <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
                <a href="?logout=1" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                    Cerrar Sesión
                </a>
            </div>
        </div>
        <div class="flex flex-wrap gap-4 mt-4">
            <a href="dashboard.php" class="text-white bg-indigo-800 px-4 py-2 rounded">Dashboard</a>
            <a href="addExpenses.php" class="text-white hover:bg-indigo-600 px-4 py-2 rounded transition">Agregar Gasto</a>
            <a href="pagos-mejorado.php" class="text-white hover:bg-indigo-600 px-4 py-2 rounded transition">Pagos</a>
            <a href="resumen-mejorado.php" class="text-white hover:bg-indigo-600 px-4 py-2 rounded transition">Resumen</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Tarjetas de Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Total Gastos</h3>
                <p class="text-3xl font-bold text-indigo-600">$<?= number_format($estadisticas['total_gastos'], 2) ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Registros</h3>
                <p class="text-3xl font-bold text-green-600"><?= number_format($totalRegistros) ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Promedio</h3>
                <p class="text-3xl font-bold text-orange-600">
                    $<?= $totalRegistros > 0 ? number_format($estadisticas['total_gastos'] / $totalRegistros, 2) : '0.00' ?>
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm">Páginas</h3>
                <p class="text-3xl font-bold text-purple-600"><?= $totalPaginas ?></p>
            </div>
        </div>

        <!-- Panel de Filtros -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-bold mb-4">Filtros Avanzados</h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="<?= $filtros['fecha_inicio'] ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="<?= $filtros['fecha_fin'] ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                        <option value="todos" <?= $filtros['tipo'] === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="Fijo" <?= $filtros['tipo'] === 'Fijo' ? 'selected' : '' ?>>Fijo</option>
                        <option value="Central" <?= $filtros['tipo'] === 'Central' ? 'selected' : '' ?>>Central</option>
                        <option value="Mercado" <?= $filtros['tipo'] === 'Mercado' ? 'selected' : '' ?>>Mercado</option>
                        <option value="Mantenimiento" <?= $filtros['tipo'] === 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                        <option value="Inversiones" <?= $filtros['tipo'] === 'Inversiones' ? 'selected' : '' ?>>Inversiones</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Método</label>
                    <select name="metodo" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                        <option value="todos" <?= $filtros['metodo'] === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="Tarjeta" <?= $filtros['metodo'] === 'Tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                        <option value="Efectivo" <?= $filtros['metodo'] === 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto Mínimo</label>
                    <input type="number" name="monto_min" value="<?= $filtros['monto_min'] ?>" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto Máximo</label>
                    <input type="number" name="monto_max" value="<?= $filtros['monto_max'] ?>" step="0.01"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <input type="text" name="descripcion" value="<?= htmlspecialchars($filtros['descripcion']) ?>" 
                           placeholder="Buscar en descripción..."
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
                    <select name="orden" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                        <option value="fecha_desc" <?= $filtros['orden'] === 'fecha_desc' ? 'selected' : '' ?>>Fecha (Más reciente)</option>
                        <option value="fecha_asc" <?= $filtros['orden'] === 'fecha_asc' ? 'selected' : '' ?>>Fecha (Más antigua)</option>
                        <option value="monto_desc" <?= $filtros['orden'] === 'monto_desc' ? 'selected' : '' ?>>Monto (Mayor)</option>
                        <option value="monto_asc" <?= $filtros['orden'] === 'monto_asc' ? 'selected' : '' ?>>Monto (Menor)</option>
                        <option value="descripcion" <?= $filtros['orden'] === 'descripcion' ? 'selected' : '' ?>>Descripción (A-Z)</option>
                    </select>
                </div>
                
                <div class="md:col-span-2 lg:col-span-4 flex gap-4">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">
                        Aplicar Filtros
                    </button>
                    <a href="dashboard.php" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition">
                        Limpiar Filtros
                    </a>
                    <a href="exportar.php?<?= http_build_query($filtros) ?>" 
                       class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
                        Exportar Excel
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabla de Gastos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold">Gastos (<?= number_format($totalRegistros) ?> registros)</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($gastos as $gasto): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y', strtotime($gasto['Fecha'])) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?= htmlspecialchars($gasto['Descripcion']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                $<?= number_format($gasto['Monto'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?= $gasto['Metodo'] === 'Tarjeta' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= $gasto['Metodo'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                    switch($gasto['Tipo']) {
                                        case 'Fijo': echo 'bg-red-100 text-red-800'; break;
                                        case 'Central': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Mercado': echo 'bg-purple-100 text-purple-800'; break;
                                        case 'Mantenimiento': echo 'bg-orange-100 text-orange-800'; break;
                                        case 'Inversiones': echo 'bg-indigo-100 text-indigo-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?= $gasto['Tipo'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button onclick="editarGasto(<?= htmlspecialchars(json_encode($gasto)) ?>)" 
                                        class="text-indigo-600 hover:text-indigo-900">Editar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Página <?= $filtros['pagina'] ?> de <?= $totalPaginas ?>
                    </p>
                    <div class="flex space-x-2">
                        <?php if ($filtros['pagina'] > 1): ?>
                            <a href="?<?= http_build_query(array_merge($filtros, ['pagina' => $filtros['pagina'] - 1])) ?>" 
                               class="px-3 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Anterior</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $filtros['pagina'] - 2); $i <= min($totalPaginas, $filtros['pagina'] + 2); $i++): ?>
                            <a href="?<?= http_build_query(array_merge($filtros, ['pagina' => $i])) ?>" 
                               class="px-3 py-2 <?= $i == $filtros['pagina'] ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-700 hover:bg-gray-400' ?> rounded">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($filtros['pagina'] < $totalPaginas): ?>
                            <a href="?<?= http_build_query(array_merge($filtros, ['pagina' => $filtros['pagina'] + 1])) ?>" 
                               class="px-3 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Siguiente</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para editar gasto -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <form method="POST" id="editForm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Gasto</h3>
                
                <input type="hidden" name="edit_gasto_id" id="edit_gasto_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <input type="text" name="edit_gasto_descripcion" id="edit_gasto_descripcion" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Monto</label>
                    <input type="number" name="edit_gasto_monto" id="edit_gasto_monto" step="0.01" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Fecha</label>
                    <input type="date" name="edit_gasto_fecha" id="edit_gasto_fecha" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Método</label>
                    <select name="edit_gasto_metodo" id="edit_gasto_metodo" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Efectivo">Efectivo</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tipo</label>
                    <select name="edit_gasto_tipo" id="edit_gasto_tipo" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="Fijo">Fijo</option>
                        <option value="Central">Central</option>
                        <option value="Mercado">Mercado</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Inversiones">Inversiones</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="cerrarModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editarGasto(gasto) {
            document.getElementById('edit_gasto_id').value = gasto.ID;
            document.getElementById('edit_gasto_descripcion').value = gasto.Descripcion;
            document.getElementById('edit_gasto_monto').value = gasto.Monto;
            document.getElementById('edit_gasto_fecha').value = gasto.Fecha;
            document.getElementById('edit_gasto_metodo').value = gasto.Metodo;
            document.getElementById('edit_gasto_tipo').value = gasto.Tipo;
            document.getElementById('editModal').classList.remove('hidden');
        }
        
        function cerrarModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    </script>
</body>
</html>
