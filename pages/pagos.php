<?php
// Obtener filtros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$metodoFiltro = $_GET['metodo'] ?? 'todos';

// Construir consulta con filtros
$sql = "SELECT * FROM Pagos WHERE fecha BETWEEN ? AND ?";
$params = [$fechaInicio, $fechaFin];
$types = 'ss';

if ($metodoFiltro !== 'todos') {
    $sql .= " AND Metodo = ?";
    $params[] = $metodoFiltro;
    $types .= 's';
}

$sql .= " ORDER BY fecha DESC LIMIT 50";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$pagos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener estadísticas
$sql_stats = "SELECT 
    COUNT(*) as total_pagos,
    SUM(monto) as total_monto,
    AVG(monto) as promedio_monto
FROM Pagos WHERE fecha BETWEEN ? AND ?";

$params_stats = [$fechaInicio, $fechaFin];
$types_stats = 'ss';

if ($metodoFiltro !== 'todos') {
    $sql_stats .= " AND Metodo = ?";
    $params_stats[] = $metodoFiltro;
    $types_stats .= 's';
}

$stmt_stats = $conexion->prepare($sql_stats);
$stmt_stats->bind_param($types_stats, ...$params_stats);
$stmt_stats->execute();
$estadisticas = $stmt_stats->get_result()->fetch_assoc();
?>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-money-bill mr-2 text-green-600"></i>
            Gestión de Pagos
        </h1>
        <a href="<?php echo $router->getUrl('add-pago'); ?>" 
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
            <i class="fas fa-plus mr-1"></i> Nuevo Pago
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="hidden" name="page" value="pagos">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" value="<?php echo $fechaInicio; ?>" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input type="date" name="fecha_fin" value="<?php echo $fechaFin; ?>" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Método</label>
            <select name="metodo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="todos" <?php echo $metodoFiltro === 'todos' ? 'selected' : ''; ?>>Todos</option>
                <option value="Tarjeta" <?php echo $metodoFiltro === 'Tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                <option value="Efectivo" <?php echo $metodoFiltro === 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
        </div>
    </form>
</div>

<!-- Estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calculator text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Pagos</h3>
                <p class="text-2xl font-semibold text-gray-900">
                    <?php echo $estadisticas['total_pagos'] ?? 0; ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Monto Total</h3>
                <p class="text-2xl font-semibold text-gray-900">
                    $<?php echo number_format($estadisticas['total_monto'] ?? 0, 2); ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Promedio</h3>
                <p class="text-2xl font-semibold text-gray-900">
                    $<?php echo number_format($estadisticas['promedio_monto'] ?? 0, 2); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Pagos -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Lista de Pagos</h3>
        <p class="text-sm text-gray-500">Mostrando los últimos 50 pagos según los filtros aplicados</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Fecha
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Descripción
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Método
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Monto
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron pagos con los filtros aplicados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagos as $pago): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('d/m/Y', strtotime($pago['fecha'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($pago['descripcion']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo $pago['Metodo'] === 'Tarjeta' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800'; ?>">
                                    <i class="fas <?php echo $pago['Metodo'] === 'Tarjeta' ? 'fa-credit-card' : 'fa-money-bill'; ?> mr-1"></i>
                                    <?php echo $pago['Metodo']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                $<?php echo number_format($pago['monto'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
