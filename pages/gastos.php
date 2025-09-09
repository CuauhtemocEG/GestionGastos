<?php
// Obtener filtros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipoFiltro = $_GET['tipo'] ?? 'todos';
$metodoFiltro = $_GET['metodo'] ?? 'todos';

// Construir consulta con filtros
$sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN ? AND ?";
$params = [$fechaInicio, $fechaFin];
$types = 'ss';

if ($tipoFiltro !== 'todos') {
    $sql .= " AND Tipo = ?";
    $params[] = $tipoFiltro;
    $types .= 's';
}

if ($metodoFiltro !== 'todos') {
    $sql .= " AND Metodo = ?";
    $params[] = $metodoFiltro;
    $types .= 's';
}

$sql .= " ORDER BY Fecha DESC LIMIT 50";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$gastos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener estadísticas
$sql_stats = "SELECT 
    COUNT(*) as total_gastos,
    SUM(Monto) as total_monto,
    AVG(Monto) as promedio_monto
FROM Gastos WHERE Fecha BETWEEN ? AND ?";

$params_stats = [$fechaInicio, $fechaFin];
$types_stats = 'ss';

if ($tipoFiltro !== 'todos') {
    $sql_stats .= " AND Tipo = ?";
    $params_stats[] = $tipoFiltro;
    $types_stats .= 's';
}

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
            <i class="fas fa-credit-card mr-2 text-red-600"></i>
            Gestión de Gastos
        </h1>
        <a href="<?php echo $router->getUrl('add-gasto'); ?>" 
           class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
            <i class="fas fa-plus mr-1"></i> Nuevo Gasto
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <input type="hidden" name="page" value="gastos">
        
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select name="tipo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="todos" <?php echo $tipoFiltro === 'todos' ? 'selected' : ''; ?>>Todos</option>
                <option value="Fijo" <?php echo $tipoFiltro === 'Fijo' ? 'selected' : ''; ?>>Fijo</option>
                <option value="Central" <?php echo $tipoFiltro === 'Central' ? 'selected' : ''; ?>>Central</option>
                <option value="Mercado" <?php echo $tipoFiltro === 'Mercado' ? 'selected' : ''; ?>>Mercado</option>
                <option value="Mantenimiento" <?php echo $tipoFiltro === 'Mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                <option value="Inversiones" <?php echo $tipoFiltro === 'Inversiones' ? 'selected' : ''; ?>>Inversiones</option>
            </select>
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
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calculator text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Gastos</h3>
                <p class="text-2xl font-semibold text-gray-900">
                    <?php echo $estadisticas['total_gastos'] ?? 0; ?>
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

<!-- Tabla de Gastos -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Lista de Gastos</h3>
        <p class="text-sm text-gray-500">Mostrando los últimos 50 gastos según los filtros aplicados</p>
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
                        Tipo
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
                <?php if (empty($gastos)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron gastos con los filtros aplicados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gastos as $gasto): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('d/m/Y', strtotime($gasto['Fecha'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($gasto['Descripcion']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php 
                                    switch($gasto['Tipo']) {
                                        case 'Fijo': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'Central': echo 'bg-purple-100 text-purple-800'; break;
                                        case 'Mercado': echo 'bg-green-100 text-green-800'; break;
                                        case 'Mantenimiento': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Inversiones': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo $gasto['Tipo']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo $gasto['Metodo'] === 'Tarjeta' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800'; ?>">
                                    <i class="fas <?php echo $gasto['Metodo'] === 'Tarjeta' ? 'fa-credit-card' : 'fa-money-bill'; ?> mr-1"></i>
                                    <?php echo $gasto['Metodo']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                $<?php echo number_format($gasto['Monto'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
