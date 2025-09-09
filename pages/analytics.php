<?php
global $manager;

// Configurar filtros
$filtros = [
    'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
    'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
];

// Obtener datos
$gastos = $manager->obtenerGastosFiltrados($filtros);
$pagos = $manager->obtenerPagosFiltrados($filtros);

// Calcular métricas
$total_gastos = array_sum(array_column($gastos, 'Monto'));
$total_pagos = array_sum(array_column($pagos, 'monto'));
$balance = $total_pagos - $total_gastos;

// Análisis de tendencias (últimos 6 meses)
$tendencias = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i month"));
    $mes_inicio = $mes . '-01';
    $mes_fin = date('Y-m-t', strtotime($mes_inicio));
    
    $filtros_mes = [
        'fecha_inicio' => $mes_inicio,
        'fecha_fin' => $mes_fin
    ];
    
    $gastos_mes = $manager->obtenerGastosFiltrados($filtros_mes);
    $pagos_mes = $manager->obtenerPagosFiltrados($filtros_mes);
    
    $tendencias[] = [
        'mes' => $mes,
        'mes_nombre' => date('M Y', strtotime($mes_inicio)),
        'gastos' => array_sum(array_column($gastos_mes, 'Monto')),
        'pagos' => array_sum(array_column($pagos_mes, 'monto')),
        'balance' => array_sum(array_column($pagos_mes, 'monto')) - array_sum(array_column($gastos_mes, 'Monto'))
    ];
}

// Categorías más costosas
$gastos_por_categoria = [];
foreach ($gastos as $gasto) {
    $categoria = $gasto['Tipo'] ?? 'Sin categoría';
    if (!isset($gastos_por_categoria[$categoria])) {
        $gastos_por_categoria[$categoria] = 0;
    }
    $gastos_por_categoria[$categoria] += $gasto['Monto'];
}
arsort($gastos_por_categoria);

// Proyecciones (basado en promedio mensual)
$promedio_gastos_mensual = $total_gastos;
$promedio_pagos_mensual = $total_pagos;
$proyeccion_anual = [
    'gastos' => $promedio_gastos_mensual * 12,
    'pagos' => $promedio_pagos_mensual * 12,
    'balance' => ($promedio_pagos_mensual * 12) - ($promedio_gastos_mensual * 12)
];
?>

<div class="space-y-8">
    <!-- Header del Dashboard -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg text-white">
        <div class="p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">📊 Analytics Dashboard</h1>
                    <p class="text-purple-100 mt-2">Análisis inteligente y proyecciones financieras</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-purple-200">Período actual</div>
                    <div class="text-lg font-semibold"><?= date('d M', strtotime($filtros['fecha_inicio'])) ?> - <?= date('d M Y', strtotime($filtros['fecha_fin'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="page" value="analytics">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="<?= $filtros['fecha_inicio'] ?>" 
                       class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="<?= $filtros['fecha_fin'] ?>" 
                       class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                Actualizar
            </button>
        </form>
    </div>

    <!-- KPIs Principales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-arrow-up text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Ingresos Totales</p>
                    <p class="text-2xl font-semibold text-gray-900">$<?= number_format($total_pagos, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-arrow-down text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Gastos Totales</p>
                    <p class="text-2xl font-semibold text-gray-900">$<?= number_format($total_gastos, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-<?= $balance >= 0 ? 'blue' : 'orange' ?>-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-<?= $balance >= 0 ? 'blue' : 'orange' ?>-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-balance-scale text-<?= $balance >= 0 ? 'blue' : 'orange' ?>-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Balance</p>
                    <p class="text-2xl font-semibold text-<?= $balance >= 0 ? 'green' : 'red' ?>-600">
                        $<?= number_format($balance, 0, ',', '.') ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tasa de Ahorro</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?= $total_pagos > 0 ? number_format(($balance / $total_pagos) * 100, 1) : 0 ?>%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Tendencias -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Tendencia de Balance -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                Tendencia de Balance (6 meses)
            </h3>
            <div style="position: relative; height: 300px;">
                <canvas id="tendenciaChart"></canvas>
            </div>
        </div>

        <!-- Distribución por Categorías -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-pie-chart mr-2 text-green-600"></i>
                Gastos por Categoría
            </h3>
            <div style="position: relative; height: 300px;">
                <canvas id="categoriasChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Proyecciones y Análisis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Proyecciones Anuales -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-crystal-ball mr-2 text-purple-600"></i>
                Proyecciones Anuales
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Ingresos proyectados:</span>
                    <span class="font-semibold text-green-600">$<?= number_format($proyeccion_anual['pagos'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Gastos proyectados:</span>
                    <span class="font-semibold text-red-600">$<?= number_format($proyeccion_anual['gastos'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-gray-800 font-medium">Balance proyectado:</span>
                    <span class="font-bold text-<?= $proyeccion_anual['balance'] >= 0 ? 'green' : 'red' ?>-600">
                        $<?= number_format($proyeccion_anual['balance'], 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Top Categorías -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-trophy mr-2 text-yellow-600"></i>
                Categorías con Mayor Gasto
            </h3>
            <div class="space-y-3">
                <?php $i = 1; foreach (array_slice($gastos_por_categoria, 0, 5) as $categoria => $monto): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium mr-3">
                                <?= $i ?>
                            </span>
                            <span class="text-gray-700"><?= htmlspecialchars($categoria) ?></span>
                        </div>
                        <span class="font-semibold text-gray-900">$<?= number_format($monto, 0, ',', '.') ?></span>
                    </div>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Insights y Recomendaciones -->
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200 p-6">
        <h3 class="text-lg font-semibold text-yellow-800 mb-4">
            <i class="fas fa-lightbulb mr-2"></i>
            Insights y Recomendaciones
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if ($balance < 0): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-red-800">Balance Negativo</h4>
                            <p class="text-sm text-red-700 mt-1">Tus gastos superan tus ingresos. Considera reducir gastos en <?= array_key_first($gastos_por_categoria) ?>.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-green-800">Balance Positivo</h4>
                            <p class="text-sm text-green-700 mt-1">¡Excelente! Estás ahorrando <?= number_format(($balance / $total_pagos) * 100, 1) ?>% de tus ingresos.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (count($gastos_por_categoria) > 0): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-chart-pie text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-blue-800">Categoría Principal</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                <?= array_key_first($gastos_por_categoria) ?> representa el 
                                <?= number_format((reset($gastos_por_categoria) / $total_gastos) * 100, 1) ?>% de tus gastos.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts para gráficos -->
<script>
// Datos para gráficos
const tendenciasData = <?= json_encode($tendencias) ?>;
const categoriasData = <?= json_encode($gastos_por_categoria) ?>;

// Gráfico de tendencias
const ctx1 = document.getElementById('tendenciaChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: tendenciasData.map(item => item.mes_nombre),
        datasets: [{
            label: 'Balance',
            data: tendenciasData.map(item => item.balance),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfico de categorías
const ctx2 = document.getElementById('categoriasChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: Object.keys(categoriasData).slice(0, 6),
        datasets: [{
            data: Object.values(categoriasData).slice(0, 6),
            backgroundColor: [
                '#ef4444',
                '#f97316',
                '#eab308',
                '#22c55e',
                '#3b82f6',
                '#8b5cf6'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            }
        }
    }
});
</script>