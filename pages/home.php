<?php
global $conexion;

// Obtener filtros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipoFiltro = $_GET['tipo'] ?? 'todos';
$metodoFiltro = $_GET['metodo'] ?? 'todos';
$sucursalFiltro = $_GET['sucursal'] ?? 'todas';

// Construir WHERE para filtros
$whereGastos = "WHERE Fecha BETWEEN ? AND ?";
$wherePagos = "WHERE fecha BETWEEN ? AND ?";
$params = [$fechaInicio, $fechaFin];
$types = "ss";

if ($tipoFiltro !== 'todos') {
    $whereGastos .= " AND Tipo = ?";
    $params[] = $tipoFiltro;
    $types .= "s";
}

if ($metodoFiltro !== 'todos') {
    $whereGastos .= " AND Metodo = ?";
    $params[] = $metodoFiltro;
    $types .= "s";
}

// Estadísticas principales
$sql = "SELECT 
    COUNT(*) as total_gastos,
    SUM(Monto) as total_monto_gastos,
    AVG(Monto) as promedio_gasto,
    MAX(Monto) as gasto_mayor,
    MIN(Monto) as gasto_menor
FROM Gastos $whereGastos";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stats_gastos = $stmt->get_result()->fetch_assoc();

// Estadísticas de pagos
$sql = "SELECT 
    COUNT(*) as total_pagos,
    SUM(monto) as total_monto_pagos,
    AVG(monto) as promedio_pago
FROM Pagos $wherePagos";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$stats_pagos = $stmt->get_result()->fetch_assoc();

// Gastos por categoría
$sql = "SELECT Tipo, COUNT(*) as cantidad, SUM(Monto) as total 
        FROM Gastos $whereGastos GROUP BY Tipo ORDER BY total DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$gastos_por_categoria = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Gastos por método
$sql = "SELECT Metodo, COUNT(*) as cantidad, SUM(Monto) as total 
        FROM Gastos $whereGastos GROUP BY Metodo ORDER BY total DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$gastos_por_metodo = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Trend mensual (últimos 6 meses)
$sql = "SELECT 
    DATE_FORMAT(Fecha, '%Y-%m') as mes,
    SUM(Monto) as total_gastos,
    COUNT(*) as cantidad_gastos
FROM Gastos 
WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(Fecha, '%Y-%m') 
ORDER BY mes ASC";
$trend_mensual = $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);

// Gastos recientes
$sql = "SELECT * FROM Gastos $whereGastos ORDER BY Fecha DESC, ID DESC LIMIT 10";
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$gastos_recientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pagos recientes
$sql = "SELECT * FROM Pagos $wherePagos ORDER BY fecha DESC, id DESC LIMIT 10";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$pagos_recientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Top 5 gastos más altos
$sql = "SELECT * FROM Gastos $whereGastos ORDER BY Monto DESC LIMIT 5";
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$top_gastos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Preparar datos para gráficos
$categorias_labels = array_column($gastos_por_categoria, 'Tipo');
$categorias_data = array_column($gastos_por_categoria, 'total');

$metodos_labels = array_column($gastos_por_metodo, 'Metodo');
$metodos_data = array_column($gastos_por_metodo, 'total');

$trend_labels = array_column($trend_mensual, 'mes');
$trend_data = array_column($trend_mensual, 'total_gastos');

// Balance
$balance = ($stats_pagos['total_monto_pagos'] ?? 0) - ($stats_gastos['total_monto_gastos'] ?? 0);
?>

<!-- Dashboard Moderno -->
<div class="space-y-6">
    <!-- Header con filtros avanzados -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Financiero</h1>
                <p class="text-gray-600">Análisis de gastos y pagos del <?= date('d/m/Y', strtotime($fechaInicio)) ?> al <?= date('d/m/Y', strtotime($fechaFin)) ?></p>
            </div>
            <div class="mt-4 lg:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $balance >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    Balance: $<?= number_format($balance, 2) ?>
                </span>
            </div>
        </div>

        <!-- Filtros avanzados -->
        <form method="GET" class="bg-gray-50 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="<?= $fechaInicio ?>" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="<?= $fechaFin ?>" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select name="tipo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos" <?= $tipoFiltro === 'todos' ? 'selected' : '' ?>>Todas</option>
                        <option value="Fijo" <?= $tipoFiltro === 'Fijo' ? 'selected' : '' ?>>Fijo</option>
                        <option value="Central" <?= $tipoFiltro === 'Central' ? 'selected' : '' ?>>Central</option>
                        <option value="Mercado" <?= $tipoFiltro === 'Mercado' ? 'selected' : '' ?>>Mercado</option>
                        <option value="Mantenimiento" <?= $tipoFiltro === 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                        <option value="Inversiones" <?= $tipoFiltro === 'Inversiones' ? 'selected' : '' ?>>Inversiones</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Método</label>
                    <select name="metodo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos" <?= $metodoFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="Efectivo" <?= $metodoFiltro === 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                        <option value="Tarjeta" <?= $metodoFiltro === 'Tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="?page=home" class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-gray-200 text-gray-700 hover:bg-gray-300">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Limpiar Filtros
                </a>
                <button type="button" onclick="exportToPDF()" class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-green-600 text-white hover:bg-green-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                    </svg>
                    Exportar PDF
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Gastos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Gastos</dt>
                        <dd class="text-lg font-medium text-gray-900">$<?= number_format($stats_gastos['total_monto_gastos'] ?? 0, 2) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-2">
                <div class="flex items-center text-sm text-gray-600">
                    <span><?= $stats_gastos['total_gastos'] ?? 0 ?> transacciones</span>
                </div>
            </div>
        </div>

        <!-- Total Pagos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Ingresos</dt>
                        <dd class="text-lg font-medium text-gray-900">$<?= number_format($stats_pagos['total_monto_pagos'] ?? 0, 2) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-2">
                <div class="flex items-center text-sm text-gray-600">
                    <span><?= $stats_pagos['total_pagos'] ?? 0 ?> transacciones</span>
                </div>
            </div>
        </div>

        <!-- Promedio Gasto -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Promedio Gasto</dt>
                        <dd class="text-lg font-medium text-gray-900">$<?= number_format($stats_gastos['promedio_gasto'] ?? 0, 2) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-2">
                <div class="flex items-center text-sm text-gray-600">
                    <span>Mayor: $<?= number_format($stats_gastos['gasto_mayor'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Balance -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 <?= $balance >= 0 ? 'bg-green-100' : 'bg-red-100' ?> rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 <?= $balance >= 0 ? 'text-green-600' : 'text-red-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Balance</dt>
                        <dd class="text-lg font-medium <?= $balance >= 0 ? 'text-green-600' : 'text-red-600' ?>">$<?= number_format(abs($balance), 2) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="mt-2">
                <div class="flex items-center text-sm text-gray-600">
                    <span class="<?= $balance >= 0 ? 'text-green-600' : 'text-red-600' ?>"><?= $balance >= 0 ? 'Positivo' : 'Negativo' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Profesionales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico de Tendencia Mensual -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tendencia de Gastos (6 meses)</h3>
                <div class="flex space-x-2">
                    <button onclick="changeTrendPeriod('6')" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded">6M</button>
                    <button onclick="changeTrendPeriod('12')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded">1A</button>
                </div>
            </div>
            <canvas id="trendChart" width="400" height="200"></canvas>
        </div>

        <!-- Gráfico de Categorías -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Gastos por Categoría</h3>
                <div class="text-sm text-gray-500">
                    Total: $<?= number_format(array_sum($categorias_data), 2) ?>
                </div>
            </div>
            <canvas id="categoryChart" width="400" height="200"></canvas>
        </div>

        <!-- Gráfico de Métodos de Pago -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Métodos de Pago</h3>
                <div class="text-sm text-gray-500">
                    <?= count($metodos_labels) ?> métodos
                </div>
            </div>
            <canvas id="methodChart" width="400" height="200"></canvas>
        </div>

        <!-- Top 5 Gastos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Top 5 Gastos Mayores</h3>
                <span class="text-sm text-gray-500">Período actual</span>
            </div>
            <div class="space-y-3">
                <?php foreach ($top_gastos as $index => $gasto): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            <?= $index + 1 ?>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($gasto['Descripcion']) ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($gasto['Tipo']) ?> • <?= date('d/m/Y', strtotime($gasto['Fecha'])) ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">$<?= number_format($gasto['Monto'], 2) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($gasto['Metodo']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($top_gastos)): ?>
                <div class="text-center text-gray-500 py-4">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>No hay gastos en el período seleccionado</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Tarjeta Gastos del Mes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-credit-card text-red-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Gastos del Mes</h3>
                <p class="text-2xl font-semibold text-gray-900">
                    $<?php echo number_format($stats_gastos['total_monto_gastos'] ?? 0, 2); ?>
                </p>
                <p class="text-xs text-gray-500"><?php echo $stats_gastos['total_gastos'] ?? 0; ?> transacciones</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta Pagos del Mes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Ingresos del Mes</h3>
                <p class="text-2xl font-semibold text-gray-900">
                    $<?php echo number_format($stats_pagos['total_monto_pagos'] ?? 0, 2); ?>
                </p>
                <p class="text-xs text-gray-500"><?php echo $stats_pagos['total_pagos'] ?? 0; ?> pagos</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta Balance -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-balance-scale text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Balance del Mes</h3>
                <?php 
                $balance = ($stats_pagos['total_monto_pagos'] ?? 0) - ($stats_gastos['total_monto_gastos'] ?? 0);
                $balance_class = $balance >= 0 ? 'text-green-600' : 'text-red-600';
                ?>
                <p class="text-2xl font-semibold <?php echo $balance_class; ?>">
                    $<?php echo number_format($balance, 2); ?>
                </p>
                <p class="text-xs text-gray-500">
                    <?php echo $balance >= 0 ? 'Positivo' : 'Negativo'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Tarjeta Acciones Rápidas -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center">
            <h3 class="text-sm font-medium text-gray-500 mb-4">Acciones Rápidas</h3>
            <div class="space-y-2">
                <a href="<?php echo $router->getUrl('add-gasto'); ?>" 
                   class="block w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition-colors text-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo Gasto
                </a>
                <a href="<?php echo $router->getUrl('add-pago'); ?>" 
                   class="block w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition-colors text-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo Pago
                </a>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Gastos Recientes -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-credit-card mr-2 text-red-600"></i>
                    Gastos Recientes
                </h3>
                <a href="<?php echo $router->getUrl('gastos'); ?>" 
                   class="text-blue-600 hover:text-blue-800 text-sm">
                    Ver todos →
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($gastos_recientes)): ?>
                <p class="text-gray-500 text-center py-4">No hay gastos registrados</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($gastos_recientes as $gasto): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($gasto['Descripcion']); ?></p>
                                <p class="text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($gasto['Fecha'])); ?> •
                                    <span class="inline-block px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs">
                                        <?php echo $gasto['Tipo']; ?>
                                    </span>
                                    <span class="inline-block px-2 py-1 bg-blue-200 text-blue-700 rounded-full text-xs">
                                        <?php echo $gasto['Metodo']; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-red-600">-$<?php echo number_format($gasto['Monto'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagos Recientes -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-money-bill mr-2 text-green-600"></i>
                    Pagos Recientes
                </h3>
                <a href="<?php echo $router->getUrl('pagos'); ?>" 
                   class="text-blue-600 hover:text-blue-800 text-sm">
                    Ver todos →
                </a>
            </div>
        </div>
        <div class="p-6">
            <?php if (empty($pagos_recientes)): ?>
                <p class="text-gray-500 text-center py-4">No hay pagos registrados</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pagos_recientes as $pago): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900"><?php echo htmlspecialchars($pago['descripcion']); ?></p>
                                <p class="text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($pago['fecha'])); ?> •
                                    <span class="inline-block px-2 py-1 bg-blue-200 text-blue-700 rounded-full text-xs">
                                        <?php echo $pago['Metodo']; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-green-600">+$<?php echo number_format($pago['monto'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Gráfico de Resumen -->
<div class="mt-8 bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
        Resumen Visual del Mes
    </h3>
    <div class="h-64">
        <canvas id="resumenChart"></canvas>
    </div>
</div>

<script>
// Crear gráfico de resumen
const ctx = document.getElementById('resumenChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Gastos', 'Ingresos'],
        datasets: [{
            data: [
                <?php echo $stats_gastos['total_monto_gastos'] ?? 0; ?>,
                <?php echo $stats_pagos['total_monto_pagos'] ?? 0; ?>
            ],
            backgroundColor: [
                '#DC2626',  // Rojo para gastos
                '#059669'   // Verde para ingresos
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<!-- JavaScript para Gráficos Profesionales -->
<script>
// Configuración global de Chart.js
Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
Chart.defaults.color = '#6B7280';

// Datos desde PHP
const categoriesData = <?= json_encode($categorias_data) ?>;
const categoriesLabels = <?= json_encode($categorias_labels) ?>;
const methodsData = <?= json_encode($metodos_data) ?>;
const methodsLabels = <?= json_encode($metodos_labels) ?>;
const trendData = <?= json_encode($trend_data) ?>;
const trendLabels = <?= json_encode($trend_labels) ?>;

// Colores profesionales
const colors = {
    primary: '#3B82F6',
    secondary: '#8B5CF6',
    success: '#10B981',
    warning: '#F59E0B',
    danger: '#EF4444',
    info: '#06B6D4',
    dark: '#374151',
    light: '#F3F4F6'
};

const chartColors = [
    '#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444', 
    '#06B6D4', '#F97316', '#84CC16', '#EC4899', '#6366F1'
];

// Gráfico de Tendencia
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendLabels.map(label => {
            const [year, month] = label.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleDateString('es-ES', { month: 'short', year: '2-digit' });
        }),
        datasets: [{
            label: 'Gastos',
            data: trendData,
            borderColor: colors.primary,
            backgroundColor: colors.primary + '20',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.primary,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1F2937',
                titleColor: '#F9FAFB',
                bodyColor: '#F9FAFB',
                borderColor: colors.primary,
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return `Gastos: $${context.parsed.y.toLocaleString('es-ES', {minimumFractionDigits: 2})}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                border: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: '#F3F4F6'
                },
                border: {
                    display: false
                },
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString('es-ES');
                    }
                }
            }
        }
    }
});

// Gráfico de Categorías (Doughnut)
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: categoriesLabels,
        datasets: [{
            data: categoriesData,
            backgroundColor: chartColors.slice(0, categoriesLabels.length),
            borderWidth: 0,
            hoverBorderWidth: 2,
            hoverBorderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: '#1F2937',
                titleColor: '#F9FAFB',
                bodyColor: '#F9FAFB',
                borderColor: colors.primary,
                borderWidth: 1,
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return `${context.label}: $${context.parsed.toLocaleString('es-ES', {minimumFractionDigits: 2})} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Gráfico de Métodos de Pago (Bar)
const methodCtx = document.getElementById('methodChart').getContext('2d');
const methodChart = new Chart(methodCtx, {
    type: 'bar',
    data: {
        labels: methodsLabels,
        datasets: [{
            data: methodsData,
            backgroundColor: chartColors.slice(0, methodsLabels.length).map(color => color + '80'),
            borderColor: chartColors.slice(0, methodsLabels.length),
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#1F2937',
                titleColor: '#F9FAFB',
                bodyColor: '#F9FAFB',
                borderColor: colors.primary,
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return `${context.label}: $${context.parsed.y.toLocaleString('es-ES', {minimumFractionDigits: 2})}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                border: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: '#F3F4F6'
                },
                border: {
                    display: false
                },
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString('es-ES');
                    }
                }
            }
        }
    }
});

// Función para cambiar período de tendencia
function changeTrendPeriod(months) {
    // Aquí podrías hacer una llamada AJAX para obtener nuevos datos
    console.log(`Cambiando período a ${months} meses`);
    
    // Ejemplo de actualización visual
    document.querySelectorAll('[onclick*="changeTrendPeriod"]').forEach(btn => {
        btn.className = btn.className.replace('bg-blue-100 text-blue-700', 'bg-gray-100 text-gray-700');
    });
    event.target.className = event.target.className.replace('bg-gray-100 text-gray-700', 'bg-blue-100 text-blue-700');
}

// Función para exportar a PDF
function exportToPDF() {
    // Implementar exportación a PDF
    alert('Función de exportación a PDF en desarrollo');
}

// Animaciones de entrada
document.addEventListener('DOMContentLoaded', function() {
    // Animar KPI cards
    const cards = document.querySelectorAll('.grid > div');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Actualización en tiempo real (opcional)
function refreshDashboard() {
    location.reload();
}

// Auto-refresh cada 5 minutos (opcional)
// setInterval(refreshDashboard, 300000);
</script>

<!-- Script para alertas de gastos -->
<script>
// Verificar alertas de gastos altos
const totalGastos = <?= $stats_gastos['total_monto_gastos'] ?? 0 ?>;
const promedioGasto = <?= $stats_gastos['promedio_gasto'] ?? 0 ?>;
const gastoMayor = <?= $stats_gastos['gasto_mayor'] ?? 0 ?>;

// Alerta si el gasto del período es muy alto
if (totalGastos > 50000) {
    setTimeout(() => {
        if (confirm('⚠️ Gastos altos detectados!\n\nEl total de gastos ($' + totalGastos.toLocaleString('es-ES', {minimumFractionDigits: 2}) + ') supera el umbral recomendado.\n\n¿Deseas ver un análisis detallado?')) {
            window.location.href = '?page=resumen&analisis=alto';
        }
    }, 2000);
}
</script>

</div>
