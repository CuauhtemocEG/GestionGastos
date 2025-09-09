<?php
// Usar el GastosManager global
global $manager;

// Configurar filtros para el dashboard
$filtros_config = [
    'mostrar_fecha' => true,
    'mostrar_tipo' => false,
    'mostrar_metodo' => false,
    'mostrar_monto' => false,
    'mostrar_descripcion' => false,
    'mostrar_categoria' => false,
    'mostrar_exportar' => true,
    'titulo' => 'Período de Análisis'
];

// Obtener filtros de la URL
$filtros = [
    'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
    'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
    'limite' => 1000 // Sin límite para análisis
];

// Obtener datos
$gastos = $manager->obtenerGastosFiltrados($filtros);
$pagos = $manager->obtenerPagosFiltrados($filtros);

// Calcular métricas principales
$total_gastos = array_sum(array_column($gastos, 'Monto'));
$total_pagos = array_sum(array_column($pagos, 'monto'));
$balance = $total_pagos - $total_gastos;

$count_gastos = count($gastos);
$count_pagos = count($pagos);

// Análisis por categorías de gastos
$gastos_por_categoria = [];
foreach ($gastos as $gasto) {
    $categoria = $gasto['Tipo'] ?: 'Sin categoría';
    if (!isset($gastos_por_categoria[$categoria])) {
        $gastos_por_categoria[$categoria] = ['monto' => 0, 'cantidad' => 0];
    }
    $gastos_por_categoria[$categoria]['monto'] += $gasto['Monto'];
    $gastos_por_categoria[$categoria]['cantidad']++;
}

// Análisis por método de pago
$gastos_por_metodo = [];
foreach ($gastos as $gasto) {
    $metodo = $gasto['Metodo'];
    if (!isset($gastos_por_metodo[$metodo])) {
        $gastos_por_metodo[$metodo] = 0;
    }
    $gastos_por_metodo[$metodo] += $gasto['Monto'];
}

// Análisis temporal (últimos 7 días)
$datos_temporales = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $gastos_dia = array_filter($gastos, function($g) use ($fecha) {
        return $g['Fecha'] === $fecha;
    });
    $pagos_dia = array_filter($pagos, function($p) use ($fecha) {
        return $p['fecha'] === $fecha;
    });
    
    $datos_temporales[] = [
        'fecha' => $fecha,
        'gastos' => array_sum(array_column($gastos_dia, 'Monto')),
        'pagos' => array_sum(array_column($pagos_dia, 'monto'))
    ];
}

// Top 5 gastos más altos
$top_gastos = $gastos;
usort($top_gastos, function($a, $b) {
    return $b['Monto'] <=> $a['Monto'];
});
$top_gastos = array_slice($top_gastos, 0, 5);

// Promedio diario
$dias_periodo = max(1, (strtotime($filtros['fecha_fin']) - strtotime($filtros['fecha_inicio'])) / 86400 + 1);
$promedio_gastos_dia = $total_gastos / $dias_periodo;
$promedio_pagos_dia = $total_pagos / $dias_periodo;
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        <i class="fas fa-tachometer-alt mr-2 text-blue-600"></i>
        Dashboard Financiero
    </h1>
    <p class="text-gray-600 mt-1">Análisis completo de tus finanzas personales</p>
</div>

<!-- Componente de Filtros -->
<?php include 'includes/filtros-component.php'; ?>

<!-- Métricas Principales -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-credit-card text-2xl text-red-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Gastos</p>
                <p class="text-2xl font-bold text-red-600">$<?php echo number_format($total_gastos, 2); ?></p>
                <p class="text-xs text-gray-400"><?php echo $count_gastos; ?> transacciones</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-money-bill text-2xl text-green-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Ingresos</p>
                <p class="text-2xl font-bold text-green-600">$<?php echo number_format($total_pagos, 2); ?></p>
                <p class="text-xs text-gray-400"><?php echo $count_pagos; ?> transacciones</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-balance-scale text-2xl <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Balance</p>
                <p class="text-2xl font-bold <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    $<?php echo number_format($balance, 2); ?>
                </p>
                <p class="text-xs text-gray-400">
                    <?php echo $balance >= 0 ? 'Positivo' : 'Negativo'; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-chart-line text-2xl text-blue-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Promedio Diario</p>
                <p class="text-lg font-bold text-gray-900">$<?php echo number_format($promedio_gastos_dia, 2); ?></p>
                <p class="text-xs text-gray-400">gastos por día</p>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Evolución Temporal -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-line mr-2 text-blue-600"></i>
            Evolución Últimos 7 Días
        </h3>
        <div class="chart-container">
            <canvas id="evolutionChart"></canvas>
        </div>
    </div>

    <!-- Gastos por Categoría -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-pie mr-2 text-purple-600"></i>
            Gastos por Categoría
        </h3>
        <?php if (!empty($gastos_por_categoria)): ?>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-chart-pie text-4xl mb-2"></i>
                <p>No hay datos para mostrar</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Análisis Detallado -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Gastos -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-trophy mr-2 text-yellow-600"></i>
            Top 5 Gastos Más Altos
        </h3>
        <?php if (!empty($top_gastos)): ?>
            <div class="space-y-3">
                <?php foreach ($top_gastos as $index => $gasto): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                    <div class="flex items-center">
                        <span class="w-6 h-6 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold flex items-center justify-center mr-3">
                            <?php echo $index + 1; ?>
                        </span>
                        <div>
                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($gasto['Descripcion']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('d/m/Y', strtotime($gasto['Fecha'])); ?></p>
                        </div>
                    </div>
                    <span class="font-bold text-red-600">$<?php echo number_format($gasto['Monto'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">No hay gastos para mostrar</p>
        <?php endif; ?>
    </div>

    <!-- Análisis por Método de Pago -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-credit-card mr-2 text-green-600"></i>
            Gastos por Método de Pago
        </h3>
        <?php if (!empty($gastos_por_metodo)): ?>
            <div class="space-y-3">
                <?php foreach ($gastos_por_metodo as $metodo => $monto): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($metodo); ?></span>
                    <div class="flex items-center">
                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-blue-600 h-2 rounded-full" 
                                 style="width: <?php echo ($monto / max($gastos_por_metodo)) * 100; ?>%"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-900">$<?php echo number_format($monto, 2); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">No hay datos para mostrar</p>
        <?php endif; ?>
    </div>
</div>

<!-- Recomendaciones -->
<div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
    <h3 class="text-lg font-semibold mb-4">
        <i class="fas fa-lightbulb mr-2"></i>
        Recomendaciones Financieras
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if ($balance < 0): ?>
        <div class="bg-white bg-opacity-20 rounded p-3">
            <i class="fas fa-exclamation-triangle text-yellow-300 mb-2"></i>
            <p class="text-sm"><strong>Balance Negativo:</strong> Considera reducir gastos o aumentar ingresos.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($promedio_gastos_dia > $promedio_pagos_dia): ?>
        <div class="bg-white bg-opacity-20 rounded p-3">
            <i class="fas fa-chart-line text-red-300 mb-2"></i>
            <p class="text-sm"><strong>Gastos Altos:</strong> Tu promedio de gastos excede tus ingresos diarios.</p>
        </div>
        <?php endif; ?>
        
        <div class="bg-white bg-opacity-20 rounded p-3">
            <i class="fas fa-piggy-bank text-green-300 mb-2"></i>
            <p class="text-sm"><strong>Tip de Ahorro:</strong> Intenta ahorrar al menos el 20% de tus ingresos.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Asegurar que los contenedores de gráficos mantengan su altura */
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.chart-container canvas {
    position: absolute;
    left: 0;
    top: 0;
    pointer-events: auto;
}
</style>
<script>
// Gráfico de evolución temporal
const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
const evolutionChart = new Chart(evolutionCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($d) { return date('d/m', strtotime($d['fecha'])); }, $datos_temporales)); ?>,
        datasets: [{
            label: 'Gastos',
            data: <?php echo json_encode(array_column($datos_temporales, 'gastos')); ?>,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
        }, {
            label: 'Ingresos',
            data: <?php echo json_encode(array_column($datos_temporales, 'pagos')); ?>,
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
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
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfico de categorías
<?php if (!empty($gastos_por_categoria)): ?>
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($gastos_por_categoria)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($gastos_por_categoria, 'monto')); ?>,
            backgroundColor: [
                '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', 
                '#8b5cf6', '#ec4899', '#6b7280', '#14b8a6', '#f59e0b'
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
                    padding: 20,
                    usePointStyle: true
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': $' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Función para redimensionar gráficos cuando sea necesario
function resizeCharts() {
    if (typeof evolutionChart !== 'undefined') {
        evolutionChart.resize();
    }
    <?php if (!empty($gastos_por_categoria)): ?>
    if (typeof categoryChart !== 'undefined') {
        categoryChart.resize();
    }
    <?php endif; ?>
}

// Redimensionar cuando cambie el tamaño de la ventana
window.addEventListener('resize', resizeCharts);

// Asegurar que los gráficos se muestren correctamente al cargar
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(resizeCharts, 100);
});
</script>
