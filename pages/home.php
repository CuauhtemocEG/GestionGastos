<?php
// Usar el GastosManager global
global $manager;

// Obtener filtros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipoFiltro = $_GET['tipo'] ?? 'todos';
$metodoFiltro = $_GET['metodo'] ?? 'todos';
$sucursalFiltro = $_GET['sucursal'] ?? 'todas';

// Preparar filtros para GastosManager
$filtros = [
    'fecha_inicio' => $fechaInicio,
    'fecha_fin' => $fechaFin,
    'tipo' => $tipoFiltro !== 'todos' ? $tipoFiltro : null,
    'metodo' => $metodoFiltro !== 'todos' ? $metodoFiltro : null
];

// Obtener estadísticas usando GastosManager
$estadisticas = $manager->obtenerEstadisticas($filtros);
$gastos_filtrados = $manager->obtenerGastosFiltrados($filtros);
$pagos_filtrados = $manager->obtenerPagosFiltrados($filtros);

// Extraer datos de las estadísticas
$stats_gastos = [
    'total_gastos' => count($gastos_filtrados),
    'total_monto_gastos' => array_sum(array_column($gastos_filtrados, 'Monto')),
    'promedio_gasto' => count($gastos_filtrados) > 0 ? array_sum(array_column($gastos_filtrados, 'Monto')) / count($gastos_filtrados) : 0,
    'gasto_mayor' => count($gastos_filtrados) > 0 ? max(array_column($gastos_filtrados, 'Monto')) : 0,
    'gasto_menor' => count($gastos_filtrados) > 0 ? min(array_column($gastos_filtrados, 'Monto')) : 0
];

$stats_pagos = [
    'total_pagos' => count($pagos_filtrados),
    'total_monto_pagos' => array_sum(array_column($pagos_filtrados, 'monto')),
    'promedio_pago' => count($pagos_filtrados) > 0 ? array_sum(array_column($pagos_filtrados, 'monto')) / count($pagos_filtrados) : 0
];

// Gastos por categoría desde estadísticas
$gastos_por_categoria = [];
foreach ($gastos_filtrados as $gasto) {
    $tipo = $gasto['Tipo'];
    if (!isset($gastos_por_categoria[$tipo])) {
        $gastos_por_categoria[$tipo] = ['Tipo' => $tipo, 'cantidad' => 0, 'total' => 0];
    }
    $gastos_por_categoria[$tipo]['cantidad']++;
    $gastos_por_categoria[$tipo]['total'] += $gasto['Monto'];
}
$gastos_por_categoria = array_values($gastos_por_categoria);
usort($gastos_por_categoria, function($a, $b) { return $b['total'] <=> $a['total']; });

// Gastos por método desde estadísticas
$gastos_por_metodo = [];
foreach ($gastos_filtrados as $gasto) {
    $metodo = $gasto['Metodo'];
    if (!isset($gastos_por_metodo[$metodo])) {
        $gastos_por_metodo[$metodo] = ['Metodo' => $metodo, 'cantidad' => 0, 'total' => 0];
    }
    $gastos_por_metodo[$metodo]['cantidad']++;
    $gastos_por_metodo[$metodo]['total'] += $gasto['Monto'];
}
$gastos_por_metodo = array_values($gastos_por_metodo);
usort($gastos_por_metodo, function($a, $b) { return $b['total'] <=> $a['total']; });

// Trend mensual (con período dinámico)
$trend_months = $_GET['trend_months'] ?? 6;
$sql = "SELECT 
    DATE_FORMAT(Fecha, '%Y-%m') as mes,
    SUM(Monto) as total_gastos,
    COUNT(*) as cantidad_gastos
FROM Gastos 
WHERE Fecha >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
GROUP BY DATE_FORMAT(Fecha, '%Y-%m') 
ORDER BY mes ASC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $trend_months);
$stmt->execute();
$trend_mensual = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Gastos recientes (limitados)
$gastos_recientes = array_slice($gastos_filtrados, 0, 10);

// Pagos recientes (limitados) 
$pagos_recientes = array_slice($pagos_filtrados, 0, 10);

// Top 5 gastos más altos
$top_gastos = $gastos_filtrados;
usort($top_gastos, function($a, $b) { return $b['Monto'] <=> $a['Monto']; });
$top_gastos = array_slice($top_gastos, 0, 5);

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

        <!-- Incluir componente de filtros unificado -->
        <?php
        // Configuración específica para el dashboard home
        $filtros_config = [
            'mostrar_fecha' => true,
            'mostrar_tipo' => true,
            'mostrar_metodo' => true,
            'mostrar_monto' => false,
            'mostrar_descripcion' => false,
            'mostrar_categoria' => false,
            'mostrar_exportar' => true,
            'accion_form' => '?page=home',
            'titulo' => 'Filtros del Dashboard'
        ];
        include 'includes/filtros-component.php'; 
        ?>
    </div>

    <!-- KPI Cards Mejoradas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Total Gastos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dashboard-card hover:border-red-200 transition-all">
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
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <?= $stats_gastos['total_gastos'] ?? 0 ?> transacciones
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Pagos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dashboard-card hover:border-green-200 transition-all">
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
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <?= $stats_pagos['total_pagos'] ?? 0 ?> transacciones
                    </span>
                </div>
            </div>
        </div>

        <!-- Promedio Gasto -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 dashboard-card hover:border-blue-200 transition-all">
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

        <!-- Acciones Rápidas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <h3 class="text-sm font-medium text-gray-500 mb-4">Acciones Rápidas</h3>
                <div class="space-y-2">
                    <a href="?page=add-gasto" 
                       class="block w-full bg-red-600 text-white py-2 px-3 rounded-md hover:bg-red-700 transition-colors text-sm">
                        <i class="fas fa-plus mr-1"></i> Nuevo Gasto
                    </a>
                    <a href="?page=add-pago" 
                       class="block w-full bg-green-600 text-white py-2 px-3 rounded-md hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-plus mr-1"></i> Nuevo Pago
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Profesionales Optimizados -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Gráfico de Tendencia Mensual -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Tendencia de Gastos</h3>
                <div class="flex space-x-2">
                    <button onclick="changeTrendPeriod('6')" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors <?= $trend_months == 6 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">6M</button>
                    <button onclick="changeTrendPeriod('12')" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors <?= $trend_months == 12 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">1A</button>
                </div>
            </div>
            <div class="relative" style="height: 300px;">
                <?php if (!empty($trend_data) && array_sum($trend_data) > 0): ?>
                    <canvas id="trendChart" class="w-full h-full"></canvas>
                <?php else: ?>
                    <div class="flex items-center justify-center h-full bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No hay datos para mostrar</p>
                            <p class="text-gray-400 text-xs">Agrega algunos gastos para ver la tendencia</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico de Categorías -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Gastos por Categoría</h3>
                <div class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
                    Total: $<?= number_format(array_sum($categorias_data), 2) ?>
                </div>
            </div>
            <div class="relative" style="height: 300px;">
                <?php if (!empty($categorias_data) && array_sum($categorias_data) > 0): ?>
                    <canvas id="categoryChart" class="w-full h-full"></canvas>
                <?php else: ?>
                    <div class="flex items-center justify-center h-full bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No hay categorías para mostrar</p>
                            <p class="text-gray-400 text-xs">Agrega gastos para ver la distribución</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico de Métodos de Pago -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Métodos de Pago</h3>
                <div class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
                    <?= count($metodos_labels) ?> métodos
                </div>
            </div>
            <div class="relative" style="height: 300px;">
                <?php if (!empty($metodos_data) && array_sum($metodos_data) > 0): ?>
                    <canvas id="methodChart" class="w-full h-full"></canvas>
                <?php else: ?>
                    <div class="flex items-center justify-center h-full bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <p class="text-gray-500 text-sm">No hay métodos para mostrar</p>
                            <p class="text-gray-400 text-xs">Los métodos aparecerán conforme agregues gastos</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen Financiero Inteligente -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Resumen Financiero</h3>
                <button onclick="window.location.href='?page=resumen'" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Ver completo →
                </button>
            </div>
            
            <?php 
            // Cálculos inteligentes
            $ahorro_potencial = $stats_gastos['total_monto_gastos'] * 0.15; // 15% de ahorro potencial
            $gasto_diario_promedio = $stats_gastos['total_monto_gastos'] / max(1, (strtotime($fechaFin) - strtotime($fechaInicio)) / 86400);
            $dias_restantes = max(0, (strtotime(date('Y-m-t')) - strtotime(date('Y-m-d'))) / 86400);
            $proyeccion_mes = $gasto_diario_promedio * date('t');
            ?>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-900">Gasto Diario Promedio</p>
                            <p class="text-xl font-bold text-blue-700">$<?= number_format($gasto_diario_promedio, 2) ?></p>
                        </div>
                        <div class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-4 rounded-lg border border-purple-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-900">Proyección Mensual</p>
                            <p class="text-xl font-bold text-purple-700">$<?= number_format($proyeccion_mes, 2) ?></p>
                        </div>
                        <div class="w-10 h-10 bg-purple-200 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-4 rounded-lg border border-green-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-900">Ahorro Potencial</p>
                            <p class="text-xl font-bold text-green-700">$<?= number_format($ahorro_potencial, 2) ?></p>
                        </div>
                        <div class="w-10 h-10 bg-green-200 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-lg border border-amber-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-amber-900">Eficiencia</p>
                            <p class="text-xl font-bold text-amber-700"><?= $balance >= 0 ? '✓' : '⚠' ?> <?= $balance >= 0 ? 'Positivo' : 'Negativo' ?></p>
                        </div>
                        <div class="w-10 h-10 bg-amber-200 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Consejos inteligentes -->
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm font-medium text-gray-900 mb-1">💡 Consejo del día:</p>
                <?php if ($balance < 0): ?>
                    <p class="text-sm text-gray-700">Tus gastos superan tus ingresos. Considera revisar las categorías con mayor gasto y buscar oportunidades de ahorro.</p>
                <?php elseif ($gasto_diario_promedio > 1000): ?>
                    <p class="text-sm text-gray-700">Tu gasto diario promedio es alto ($<?= number_format($gasto_diario_promedio, 2) ?>). Podrías ahorrar $<?= number_format($ahorro_potencial, 2) ?> optimizando gastos.</p>
                <?php else: ?>
                    <p class="text-sm text-gray-700">¡Excelente control financiero! Mantienes un buen balance entre ingresos y gastos.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sección de Actividad Reciente y Top Gastos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 5 Gastos Optimizado -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Top 5 Gastos Mayores</h3>
                <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">Período actual</span>
            </div>
            
            <?php if (!empty($top_gastos)): ?>
                <div class="space-y-3">
                    <?php foreach ($top_gastos as $index => $gasto): ?>
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-sm transition-all">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 font-bold text-sm">
                                <?= $index + 1 ?>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($gasto['Descripcion']) ?></p>
                                <div class="flex items-center space-x-3 text-sm text-gray-500">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <?= htmlspecialchars($gasto['Tipo']) ?>
                                    </span>
                                    <span class="inline-flex items-center">
                                        <?php 
                                        $metodo_icon = '';
                                        $metodo_color = '';
                                        switch($gasto['Metodo']) {
                                            case 'Efectivo':
                                                $metodo_icon = '💵';
                                                $metodo_color = 'text-green-600';
                                                break;
                                            case 'Tarjeta':
                                                $metodo_icon = '💳';
                                                $metodo_color = 'text-indigo-600';
                                                break;
                                            case 'Transferencia':
                                                $metodo_icon = '🏦';
                                                $metodo_color = 'text-blue-600';
                                                break;
                                            default:
                                                $metodo_icon = '💰';
                                                $metodo_color = 'text-gray-600';
                                        }
                                        ?>
                                        <span class="mr-1"><?= $metodo_icon ?></span>
                                        <span class="<?= $metodo_color ?>"><?= htmlspecialchars($gasto['Metodo']) ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-red-600">$<?= number_format($gasto['Monto'], 2) ?></p>
                            <p class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($gasto['Fecha'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Total de estos 5 gastos:</span>
                        <span class="font-medium text-gray-900">$<?= number_format(array_sum(array_column($top_gastos, 'Monto')), 2) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-gray-500">No hay gastos registrados</p>
                    <p class="text-gray-400 text-sm">Agrega algunos gastos para ver el ranking</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actividad Reciente -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Actividad Reciente</h3>
                <button onclick="window.location.href='?page=gastos'" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Ver todo →
                </button>
            </div>
            
            <?php 
            $actividad_reciente = array_merge(
                array_map(function($gasto) { 
                    return [
                        'tipo' => 'gasto',
                        'descripcion' => $gasto['Descripcion'] ?? 'Sin descripción',
                        'monto' => $gasto['Monto'] ?? 0,
                        'fecha' => $gasto['Fecha'] ?? date('Y-m-d'),
                        'metodo' => $gasto['Metodo'] ?? 'Sin método',
                        'categoria' => $gasto['Tipo'] ?? 'Sin categoría'
                    ];
                }, array_slice($gastos_recientes, 0, 5)),
                array_map(function($pago) { 
                    return [
                        'tipo' => 'pago',
                        'descripcion' => $pago['descripcion'] ?? 'Sin descripción',
                        'monto' => $pago['monto'] ?? 0,
                        'fecha' => $pago['fecha'] ?? date('Y-m-d'),
                        'metodo' => $pago['Metodo'] ?? 'Sin método',
                        'categoria' => 'Ingreso'
                    ];
                }, array_slice($pagos_recientes, 0, 3))
            );
            
            // Ordenar por fecha (con validación)
            usort($actividad_reciente, function($a, $b) {
                $fecha_a = $a['fecha'] ?? '1970-01-01';
                $fecha_b = $b['fecha'] ?? '1970-01-01';
                
                // Validar que las fechas sean válidas
                $timestamp_a = $fecha_a ? strtotime($fecha_a) : 0;
                $timestamp_b = $fecha_b ? strtotime($fecha_b) : 0;
                
                return $timestamp_b - $timestamp_a;
            });
            $actividad_reciente = array_slice($actividad_reciente, 0, 8);
            ?>
            
            <?php if (!empty($actividad_reciente)): ?>
                <div class="space-y-3">
                    <?php foreach ($actividad_reciente as $item): ?>
                    <div class="flex items-center justify-between p-3 rounded-lg border activity-item <?= $item['tipo'] === 'gasto' ? 'border-red-100 bg-red-50 hover:bg-red-100' : 'border-green-100 bg-green-50 hover:bg-green-100' ?>">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 <?= $item['tipo'] === 'gasto' ? 'bg-red-100' : 'bg-green-100' ?> rounded-full flex items-center justify-center">
                                <?php if ($item['tipo'] === 'gasto'): ?>
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($item['descripcion'] ?? 'Sin descripción') ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($item['categoria'] ?? 'Sin categoría') ?> • <?= htmlspecialchars($item['metodo'] ?? 'Sin método') ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold <?= $item['tipo'] === 'gasto' ? 'text-red-600' : 'text-green-600' ?>">
                                <?= $item['tipo'] === 'gasto' ? '-' : '+' ?>$<?= number_format($item['monto'] ?? 0, 2) ?>
                            </p>
                            <p class="text-xs text-gray-500"><?= 
                                ($item['fecha'] && $item['fecha'] !== '1970-01-01') 
                                    ? date('d/m', strtotime($item['fecha'])) 
                                    : 'Sin fecha' 
                            ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500">No hay actividad reciente</p>
                    <p class="text-gray-400 text-sm">La actividad aparecerá aquí conforme agregues transacciones</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript para Gráficos Profesionales -->
<script>
// Verificar que Chart.js esté cargado
if (typeof Chart === 'undefined') {
    console.error('Chart.js no está cargado');
} else {
    // Configuración global de Chart.js
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#6B7280';
    
    // Función para esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Gráfico de Tendencia (solo si hay datos y el canvas existe)
        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas && trendData.length > 0 && trendData.some(d => d > 0)) {
            const trendCtx = trendCanvas.getContext('2d');
            new Chart(trendCtx, {
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
        }
        
        // Gráfico de Categorías (solo si hay datos y el canvas existe)
        const categoryCanvas = document.getElementById('categoryChart');
        if (categoryCanvas && categoriesData.length > 0 && categoriesData.some(d => d > 0)) {
            const categoryCtx = categoryCanvas.getContext('2d');
            new Chart(categoryCtx, {
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
        }
        
        // Gráfico de Métodos de Pago (solo si hay datos y el canvas existe)
        const methodCanvas = document.getElementById('methodChart');
        if (methodCanvas && methodsData.length > 0 && methodsData.some(d => d > 0)) {
            const methodCtx = methodCanvas.getContext('2d');
            new Chart(methodCtx, {
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
        }
        
        // Animaciones de entrada para las cards
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
}

// Función para cambiar período de tendencia
function changeTrendPeriod(months) {
    const params = new URLSearchParams(window.location.search);
    params.set('trend_months', months);
    
    // Actualizar visualmente los botones
    document.querySelectorAll('[onclick*="changeTrendPeriod"]').forEach(btn => {
        btn.className = btn.className.replace('bg-blue-600 text-white shadow-sm', 'bg-gray-100 text-gray-700 hover:bg-gray-200');
    });
    event.target.className = event.target.className.replace('bg-gray-100 text-gray-700 hover:bg-gray-200', 'bg-blue-600 text-white shadow-sm');
    
    // Recargar la página con nuevos parámetros
    window.location.href = '?' + params.toString();
}

// Función para exportar a PDF
function exportToPDF() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    params.set('page', 'home');
    window.open('includes/exportar.php?' + params.toString(), '_blank');
}

// Script para alertas de gastos inteligentes
setTimeout(() => {
    const totalGastos = <?= $stats_gastos['total_monto_gastos'] ?? 0 ?>;
    const balance = <?= $balance ?>;
    
    if (totalGastos > 50000) {
        if (confirm('⚠️ Gastos altos detectados!\n\nEl total de gastos ($' + totalGastos.toLocaleString('es-ES', {minimumFractionDigits: 2}) + ') supera el umbral recomendado.\n\n¿Deseas ver un análisis detallado?')) {
            window.location.href = '?page=resumen&analisis=alto';
        }
    } else if (balance < -10000) {
        if (confirm('💰 Balance negativo detectado!\n\nTus gastos superan significativamente tus ingresos.\n\n¿Deseas revisar estrategias de ahorro?')) {
            window.location.href = '?page=resumen&balance=negativo';
        }
    }
}, 3000);
</script>
