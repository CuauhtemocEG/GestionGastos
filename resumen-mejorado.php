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
    'metodo' => $_GET['metodo'] ?? 'todos'
];

// Obtener datos
$estadisticasGastos = $gastosManager->obtenerEstadisticas($filtros);
$pagosFiltrados = $gastosManager->obtenerPagosFiltrados($filtros);

// Calcular totales
$totalPagos = 0;
foreach ($pagosFiltrados as $pago) {
    $totalPagos += $pago['monto'];
}

$totalGastos = $estadisticasGastos['total_gastos'];
$saldo = $totalPagos - $totalGastos;

// Preparar datos para gráficos
$gastosPorTipo = [];
$gastosPorMetodo = [];

foreach ($estadisticasGastos['estadisticas_detalladas'] as $detalle) {
    // Por tipo
    if (!isset($gastosPorTipo[$detalle['Tipo']])) {
        $gastosPorTipo[$detalle['Tipo']] = 0;
    }
    $gastosPorTipo[$detalle['Tipo']] += $detalle['total_monto'];
    
    // Por método
    if (!isset($gastosPorMetodo[$detalle['Metodo']])) {
        $gastosPorMetodo[$detalle['Metodo']] = 0;
    }
    $gastosPorMetodo[$detalle['Metodo']] += $detalle['total_monto'];
}

// Datos para gráfico mensual (últimos 12 meses)
$datosMenuales = [];
for ($i = 11; $i >= 0; $i--) {
    $fecha = date('Y-m', strtotime("-$i months"));
    $fechaInicio = $fecha . '-01';
    $fechaFin = date('Y-m-t', strtotime($fechaInicio));
    
    $filtrosMes = [
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'tipo' => 'todos',
        'metodo' => 'todos'
    ];
    
    $gastosMes = $gastosManager->obtenerEstadisticas($filtrosMes);
    $pagosMes = $gastosManager->obtenerPagosFiltrados($filtrosMes);
    
    $totalPagosMes = 0;
    foreach ($pagosMes as $pago) {
        $totalPagosMes += $pago['monto'];
    }
    
    $datosMenuales[] = [
        'mes' => date('M Y', strtotime($fechaInicio)),
        'gastos' => $gastosMes['total_gastos'],
        'pagos' => $totalPagosMes
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen Financiero - Gestión de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Navegación -->
    <nav class="bg-indigo-700 px-4 sm:px-8 py-4 shadow-lg">
        <div class="flex items-center justify-between">
            <span class="text-2xl font-bold text-white flex items-center gap-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                GastosApp - Resumen
            </span>
            <div class="flex items-center gap-4">
                <span class="text-white">Hola, <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
                <a href="?logout=1" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                    Cerrar Sesión
                </a>
            </div>
        </div>
        <div class="flex flex-wrap gap-4 mt-4">
            <a href="dashboard.php" class="text-white hover:bg-indigo-600 px-4 py-2 rounded transition">Dashboard</a>
            <a href="addExpenses.php" class="text-white hover:bg-indigo-600 px-4 py-2 rounded transition">Agregar Gasto</a>
            <a href="pagos-mejorado.php" class="text-white hover:bg-indigo-600 px-4 py-2 rounded transition">Pagos</a>
            <a href="resumen-mejorado.php" class="text-white bg-indigo-800 px-4 py-2 rounded">Resumen</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Filtros -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-bold mb-4">Filtros del Resumen</h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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
                
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Ingresos</h3>
                        <p class="text-2xl font-bold text-green-600">$<?= number_format($totalPagos, 2) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Gastos</h3>
                        <p class="text-2xl font-bold text-red-600">$<?= number_format($totalGastos, 2) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full <?= $saldo >= 0 ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600' ?>">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Saldo</h3>
                        <p class="text-2xl font-bold <?= $saldo >= 0 ? 'text-blue-600' : 'text-orange-600' ?>">
                            $<?= number_format($saldo, 2) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">% Gastos</h3>
                        <p class="text-2xl font-bold text-purple-600">
                            <?= $totalPagos > 0 ? number_format(($totalGastos / $totalPagos) * 100, 1) : '0' ?>%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Gráfico de Gastos por Tipo -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Gastos por Tipo</h3>
                <canvas id="gastosPorTipoChart"></canvas>
            </div>
            
            <!-- Gráfico de Gastos por Método -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Gastos por Método de Pago</h3>
                <canvas id="gastosPorMetodoChart"></canvas>
            </div>
        </div>

        <!-- Gráfico de Tendencia -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Tendencia Mensual (Últimos 12 Meses)</h3>
            <canvas id="tendenciaMensualChart"></canvas>
        </div>

        <!-- Tabla Detallada -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800">Detalle por Categoría</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transacciones</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% del Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($estadisticasGastos['estadisticas_detalladas'] as $detalle): ?>
                        <?php 
                        $porcentaje = $totalGastos > 0 ? ($detalle['total_monto'] / $totalGastos) * 100 : 0;
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php 
                                    switch($detalle['Tipo']) {
                                        case 'Fijo': echo 'bg-red-100 text-red-800'; break;
                                        case 'Central': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Mercado': echo 'bg-purple-100 text-purple-800'; break;
                                        case 'Mantenimiento': echo 'bg-orange-100 text-orange-800'; break;
                                        case 'Inversiones': echo 'bg-indigo-100 text-indigo-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?= $detalle['Tipo'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full <?= $detalle['Metodo'] === 'Tarjeta' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= $detalle['Metodo'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= number_format($detalle['total_transacciones']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                $<?= number_format($detalle['total_monto'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                $<?= number_format($detalle['promedio_monto'], 2) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: <?= $porcentaje ?>%"></div>
                                    </div>
                                    <?= number_format($porcentaje, 1) ?>%
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botones de Exportación -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="exportar.php?<?= http_build_query(array_merge($filtros, ['tipo_export' => 'resumen'])) ?>" 
               class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                Exportar Resumen
            </a>
            <a href="exportar.php?<?= http_build_query(array_merge($filtros, ['tipo_export' => 'comparativo'])) ?>" 
               class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                Exportar Comparativo Mensual
            </a>
        </div>
    </div>

    <script>
        // Datos para los gráficos
        const gastosPorTipo = <?= json_encode($gastosPorTipo) ?>;
        const gastosPorMetodo = <?= json_encode($gastosPorMetodo) ?>;
        const datosMenuales = <?= json_encode($datosMenuales) ?>;

        // Configuración de colores
        const coloresTipo = {
            'Fijo': '#EF4444',
            'Central': '#F59E0B',
            'Mercado': '#8B5CF6',
            'Mantenimiento': '#F97316',
            'Inversiones': '#6366F1'
        };

        const coloresMetodo = {
            'Tarjeta': '#3B82F6',
            'Efectivo': '#10B981'
        };

        // Gráfico de Gastos por Tipo
        new Chart(document.getElementById('gastosPorTipoChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(gastosPorTipo),
                datasets: [{
                    data: Object.values(gastosPorTipo),
                    backgroundColor: Object.keys(gastosPorTipo).map(tipo => coloresTipo[tipo] || '#6B7280')
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de Gastos por Método
        new Chart(document.getElementById('gastosPorMetodoChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(gastosPorMetodo),
                datasets: [{
                    data: Object.values(gastosPorMetodo),
                    backgroundColor: Object.keys(gastosPorMetodo).map(metodo => coloresMetodo[metodo] || '#6B7280')
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de Tendencia Mensual
        new Chart(document.getElementById('tendenciaMensualChart'), {
            type: 'line',
            data: {
                labels: datosMenuales.map(item => item.mes),
                datasets: [
                    {
                        label: 'Pagos',
                        data: datosMenuales.map(item => item.pagos),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.1
                    },
                    {
                        label: 'Gastos',
                        data: datosMenuales.map(item => item.gastos),
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    </script>
</body>
</html>
