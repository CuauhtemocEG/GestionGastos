<?php
// Verificar conexión a la base de datos
$database_connected = false;
$reportes_data = [];
$gastos_mensuales = [];
$comparativas = [];

try {
    if (isset($conexion) && $conexion) {
        // Obtener datos de gastos por mes
        $sql_mensuales = "SELECT 
                            DATE_FORMAT(fecha, '%Y-%m') as mes,
                            COUNT(*) as total_gastos,
                            SUM(monto) as total_monto,
                            AVG(monto) as promedio_monto
                          FROM gastos 
                          WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                          GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                          ORDER BY mes DESC";
        
        $result = $conexion->query($sql_mensuales);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $gastos_mensuales[] = $row;
            }
            $database_connected = true;
        }
        
        // Obtener comparativas por categoría
        $sql_categorias = "SELECT 
                            categoria,
                            COUNT(*) as total_gastos,
                            SUM(monto) as total_monto,
                            AVG(monto) as promedio_monto,
                            MIN(monto) as minimo_monto,
                            MAX(monto) as maximo_monto
                          FROM gastos 
                          GROUP BY categoria 
                          ORDER BY total_monto DESC";
        
        $result = $conexion->query($sql_categorias);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $comparativas[] = $row;
            }
        }
    }
} catch (Exception $e) {
    $database_connected = false;
}

// Datos de demostración si no hay conexión
if (!$database_connected || empty($gastos_mensuales)) {
    $gastos_mensuales = [
        ['mes' => '2025-09', 'total_gastos' => 67, 'total_monto' => 18750.00, 'promedio_monto' => 279.85],
        ['mes' => '2025-08', 'total_gastos' => 72, 'total_monto' => 21200.00, 'promedio_monto' => 294.44],
        ['mes' => '2025-07', 'total_gastos' => 58, 'total_monto' => 16800.00, 'promedio_monto' => 289.66],
        ['mes' => '2025-06', 'total_gastos' => 64, 'total_monto' => 19500.00, 'promedio_monto' => 304.69],
        ['mes' => '2025-05', 'total_gastos' => 71, 'total_monto' => 22100.00, 'promedio_monto' => 311.27],
        ['mes' => '2025-04', 'total_gastos' => 55, 'total_monto' => 15200.00, 'promedio_monto' => 276.36]
    ];
    
    $comparativas = [
        ['categoria' => 'Alimentación', 'total_gastos' => 89, 'total_monto' => 25600.00, 'promedio_monto' => 287.64, 'minimo_monto' => 15.50, 'maximo_monto' => 850.00],
        ['categoria' => 'Transporte', 'total_gastos' => 64, 'total_monto' => 18900.00, 'promedio_monto' => 295.31, 'minimo_monto' => 25.00, 'maximo_monto' => 1200.00],
        ['categoria' => 'Entretenimiento', 'total_gastos' => 42, 'total_monto' => 12800.00, 'promedio_monto' => 304.76, 'minimo_monto' => 35.00, 'maximo_monto' => 950.00],
        ['categoria' => 'Servicios', 'total_gastos' => 28, 'total_monto' => 8750.00, 'promedio_monto' => 312.50, 'minimo_monto' => 89.00, 'maximo_monto' => 750.00],
        ['categoria' => 'Salud', 'total_gastos' => 18, 'total_monto' => 6200.00, 'promedio_monto' => 344.44, 'minimo_monto' => 45.00, 'maximo_monto' => 1500.00]
    ];
}

// Calcular métricas generales
$total_meses = count($gastos_mensuales);
$promedio_mensual = $total_meses > 0 ? array_sum(array_column($gastos_mensuales, 'total_monto')) / $total_meses : 0;
$mes_mas_alto = !empty($gastos_mensuales) ? max($gastos_mensuales) : ['total_monto' => 0];
$mes_mas_bajo = !empty($gastos_mensuales) ? min($gastos_mensuales) : ['total_monto' => 0];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Avanzados - Sistema de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/charts-fix.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-chart-line text-blue-600"></i> Reportes Avanzados
            </h1>
            <p class="text-gray-600">Análisis detallado y comparativas de tus gastos</p>
            <?php if (!$database_connected): ?>
                <div class="mt-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                    <i class="fas fa-exclamation-triangle"></i> Mostrando datos de demostración. Configura la base de datos para ver datos reales.
                </div>
            <?php endif; ?>
        </div>

        <!-- Filtros de Reporte -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-filter text-blue-600"></i> Filtros de Reporte
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ultimo_mes">Último Mes</option>
                        <option value="ultimos_3_meses">Últimos 3 Meses</option>
                        <option value="ultimos_6_meses">Últimos 6 Meses</option>
                        <option value="ultimo_ano" selected>Último Año</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Reporte</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="completo" selected>Reporte Completo</option>
                        <option value="por_categoria">Por Categoría</option>
                        <option value="tendencias">Tendencias</option>
                        <option value="comparativo">Comparativo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Formato</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="web" selected>Ver en Web</option>
                        <option value="pdf">Exportar PDF</option>
                        <option value="excel">Exportar Excel</option>
                        <option value="csv">Exportar CSV</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-chart-bar"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>

        <!-- Métricas de Resumen -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Promedio Mensual</p>
                        <p class="text-3xl font-bold text-blue-600">$<?php echo number_format($promedio_mensual, 2); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Últimos <?php echo $total_meses; ?> meses</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Mes Más Alto</p>
                        <p class="text-3xl font-bold text-red-600">$<?php echo number_format($mes_mas_alto['total_monto'], 2); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo $mes_mas_alto['mes'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Mes Más Bajo</p>
                        <p class="text-3xl font-bold text-green-600">$<?php echo number_format($mes_mas_bajo['total_monto'], 2); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo $mes_mas_bajo['mes'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-arrow-down text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Variación</p>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php 
                            $variacion = $mes_mas_alto['total_monto'] - $mes_mas_bajo['total_monto'];
                            echo '$' . number_format($variacion, 2); 
                            ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Diferencia máx-mín</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-chart-area text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de Tendencias -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Tendencia Mensual -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-line-chart text-blue-600"></i> Tendencia Mensual
                </h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="tendenciaChart"></canvas>
                </div>
            </div>

            <!-- Distribución por Categorías -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-pie-chart text-green-600"></i> Top Categorías
                </h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="categoriasChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de Comparativas -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-table text-purple-600"></i> Análisis Comparativo por Categoría
                </h3>
                <div class="flex space-x-2">
                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Gastos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mínimo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Máximo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% del Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $total_general = array_sum(array_column($comparativas, 'total_monto'));
                        foreach ($comparativas as $categoria): 
                            $porcentaje = ($categoria['total_monto'] / $total_general) * 100;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($categoria['categoria']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $categoria['total_gastos']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">$<?php echo number_format($categoria['total_monto'], 2); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">$<?php echo number_format($categoria['promedio_monto'], 2); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-green-600">$<?php echo number_format($categoria['minimo_monto'], 2); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-red-600">$<?php echo number_format($categoria['maximo_monto'], 2); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm text-gray-900 mr-2"><?php echo number_format($porcentaje, 1); ?>%</div>
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
                <h4 class="text-lg font-semibold mb-2">
                    <i class="fas fa-file-pdf"></i> Reporte PDF
                </h4>
                <p class="text-blue-100 mb-4">Genera un reporte completo en formato PDF</p>
                <button class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-200">
                    Generar PDF
                </button>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
                <h4 class="text-lg font-semibold mb-2">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </h4>
                <p class="text-green-100 mb-4">Descarga todos los datos en formato Excel</p>
                <button class="bg-white text-green-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-200">
                    Descargar Excel
                </button>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                <h4 class="text-lg font-semibold mb-2">
                    <i class="fas fa-chart-pie"></i> Análisis Predictivo
                </h4>
                <p class="text-purple-100 mb-4">Proyecciones y tendencias futuras</p>
                <button class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-200">
                    Ver Análisis
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/charts-fix.js"></script>
    <script>
        // Datos para gráficos
        const meses = <?php echo json_encode(array_column($gastos_mensuales, 'mes')); ?>;
        const montosMensuales = <?php echo json_encode(array_column($gastos_mensuales, 'total_monto')); ?>;
        const categoriasNombres = <?php echo json_encode(array_column($comparativas, 'categoria')); ?>;
        const categoriasMontos = <?php echo json_encode(array_column($comparativas, 'total_monto')); ?>;

        // Colores
        const colores = [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
        ];

        // Gráfico de tendencia mensual
        const ctxTendencia = document.getElementById('tendenciaChart').getContext('2d');
        createSafeChart('tendenciaChart', {
            type: 'line',
            data: {
                labels: meses.map(mes => {
                    const [year, month] = mes.split('-');
                    return new Date(year, month - 1).toLocaleDateString('es-ES', { year: 'numeric', month: 'short' });
                }),
                datasets: [{
                    label: 'Gastos Mensuales',
                    data: montosMensuales,
                    borderColor: '#3B82F6',
                    backgroundColor: '#3B82F680',
                    fill: true,
                    tension: 0.4
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
                        beginAtZero: true,
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
        const ctxCategorias = document.getElementById('categoriasChart').getContext('2d');
        createSafeChart('categoriasChart', {
            type: 'doughnut',
            data: {
                labels: categoriasNombres.slice(0, 5), // Top 5
                datasets: [{
                    data: categoriasMontos.slice(0, 5),
                    backgroundColor: colores.slice(0, 5),
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
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
                                return context.label + ': $' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
