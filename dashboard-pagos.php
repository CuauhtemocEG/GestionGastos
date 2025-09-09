<?php
// Dashboard moderno para pagos/ingresos
global $conexion;

// Verificar conexión y obtener datos
$pagos_data = [];
$estadisticas = [
    'total_registros' => 0,
    'total_monto' => 0,
    'promedio' => 0,
    'mayor_pago' => 0
];

try {
    if (isset($conexion) && $conexion->ping()) {
        // Obtener filtros
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $metodo_filtro = $_GET['metodo'] ?? 'todos';
        
        // Construir consulta
        $where = "WHERE fecha BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        $types = "ss";
        
        if ($metodo_filtro !== 'todos') {
            $where .= " AND Metodo = ?";
            $params[] = $metodo_filtro;
            $types .= "s";
        }
        
        // Obtener pagos
        $sql = "SELECT * FROM Pagos $where ORDER BY fecha DESC, id DESC LIMIT 100";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $pagos_data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Estadísticas
        $sql_stats = "SELECT 
            COUNT(*) as total_registros,
            COALESCE(SUM(monto), 0) as total_monto,
            COALESCE(AVG(monto), 0) as promedio,
            COALESCE(MAX(monto), 0) as mayor_pago
        FROM Pagos $where";
        $stmt_stats = $conexion->prepare($sql_stats);
        $stmt_stats->bind_param($types, ...$params);
        $stmt_stats->execute();
        $estadisticas = $stmt_stats->get_result()->fetch_assoc();
        
        $conexion_ok = true;
    } else {
        throw new Exception("Sin conexión a base de datos");
    }
} catch (Exception $e) {
    $conexion_ok = false;
    // Datos de demo si no hay conexión
    $pagos_data = [
        ['id' => 1, 'fecha' => '2025-09-01', 'descripcion' => 'Salario mensual', 'monto' => 25000.00, 'Metodo' => 'Transferencia'],
        ['id' => 2, 'fecha' => '2025-09-03', 'descripcion' => 'Freelance proyecto', 'monto' => 8500.00, 'Metodo' => 'Transferencia'],
        ['id' => 3, 'fecha' => '2025-09-05', 'descripcion' => 'Venta producto', 'monto' => 12000.00, 'Metodo' => 'Efectivo'],
        ['id' => 4, 'fecha' => '2025-09-02', 'descripcion' => 'Bonificación', 'monto' => 3000.00, 'Metodo' => 'Transferencia'],
        ['id' => 5, 'fecha' => '2025-09-07', 'descripcion' => 'Consultoría', 'monto' => 2500.00, 'Metodo' => 'Transferencia']
    ];
    $estadisticas = [
        'total_registros' => 5,
        'total_monto' => 51000.00,
        'promedio' => 10200.00,
        'mayor_pago' => 25000.00
    ];
}

// Análisis por método de pago
$pagos_por_metodo = [];
foreach ($pagos_data as $pago) {
    $metodo = $pago['Metodo'];
    if (!isset($pagos_por_metodo[$metodo])) {
        $pagos_por_metodo[$metodo] = ['total' => 0, 'cantidad' => 0];
    }
    $pagos_por_metodo[$metodo]['total'] += $pago['monto'];
    $pagos_por_metodo[$metodo]['cantidad']++;
}
arsort($pagos_por_metodo);

// Análisis temporal (últimos 7 días)
$pagos_temporales = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $total_dia = 0;
    foreach ($pagos_data as $pago) {
        if ($pago['fecha'] === $fecha) {
            $total_dia += $pago['monto'];
        }
    }
    $pagos_temporales[] = [
        'fecha' => $fecha,
        'total' => $total_dia
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Ingresos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <i class="fas fa-money-bill-wave text-2xl text-green-600 mr-3"></i>
                        <h1 class="text-2xl font-bold text-gray-900">Dashboard de Ingresos</h1>
                    </div>
                    
                    <?php if (!$conexion_ok): ?>
                    <div class="bg-yellow-100 text-yellow-800 px-3 py-2 rounded-lg text-sm">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Modo Demo - Sin conexión DB
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>
                    Filtros y Búsqueda
                </h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <input type="hidden" name="page" value="pagos">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?= $_GET['fecha_inicio'] ?? date('Y-m-01') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="<?= $_GET['fecha_fin'] ?? date('Y-m-d') ?>" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                        <select name="metodo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="todos">Todos los métodos</option>
                            <option value="Efectivo" <?= ($_GET['metodo'] ?? '') === 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                            <option value="Tarjeta" <?= ($_GET['metodo'] ?? '') === 'Tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                            <option value="Transferencia" <?= ($_GET['metodo'] ?? '') === 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Estadísticas Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Total Ingresos</p>
                            <p class="text-3xl font-bold"><?= $estadisticas['total_registros'] ?></p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                            <i class="fas fa-receipt text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium">Monto Total</p>
                            <p class="text-2xl font-bold">$<?= number_format($estadisticas['total_monto'], 2) ?></p>
                        </div>
                        <div class="bg-emerald-400 bg-opacity-30 rounded-full p-3">
                            <i class="fas fa-dollar-sign text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-teal-100 text-sm font-medium">Promedio</p>
                            <p class="text-2xl font-bold">$<?= number_format($estadisticas['promedio'], 2) ?></p>
                        </div>
                        <div class="bg-teal-400 bg-opacity-30 rounded-full p-3">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-cyan-100 text-sm font-medium">Mayor Ingreso</p>
                            <p class="text-2xl font-bold">$<?= number_format($estadisticas['mayor_pago'], 2) ?></p>
                        </div>
                        <div class="bg-cyan-400 bg-opacity-30 rounded-full p-3">
                            <i class="fas fa-arrow-up text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Ingresos por Método -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                        Ingresos por Método
                    </h3>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="metodosChart"></canvas>
                    </div>
                </div>

                <!-- Tendencia Temporal -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                        Ingresos Últimos 7 Días
                    </h3>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="tendenciaChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Ingresos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-trophy mr-2 text-yellow-600"></i>
                        Top 5 Mayores Ingresos
                    </h3>
                    <div class="space-y-3">
                        <?php 
                        $top_pagos = $pagos_data;
                        usort($top_pagos, function($a, $b) { return $b['monto'] <=> $a['monto']; });
                        $top_pagos = array_slice($top_pagos, 0, 5);
                        foreach ($top_pagos as $index => $pago): 
                        ?>
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="w-8 h-8 bg-green-500 text-white rounded-full text-sm font-bold flex items-center justify-center mr-3">
                                    <?= $index + 1 ?>
                                </span>
                                <div>
                                    <p class="font-medium text-gray-900"><?= htmlspecialchars($pago['descripcion']) ?></p>
                                    <p class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($pago['fecha'])) ?></p>
                                </div>
                            </div>
                            <span class="font-bold text-green-600">$<?= number_format($pago['monto'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resumen por Método -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-credit-card mr-2 text-purple-600"></i>
                        Resumen por Método
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($pagos_por_metodo as $metodo => $data): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($metodo) ?></p>
                                <p class="text-sm text-gray-500"><?= $data['cantidad'] ?> transacciones</p>
                            </div>
                            <span class="font-bold text-green-600">$<?= number_format($data['total'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tabla de Ingresos -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2 text-gray-600"></i>
                        Últimos Ingresos
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach (array_slice($pagos_data, 0, 20) as $pago): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($pago['descripcion']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <?= htmlspecialchars($pago['Metodo']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                    $<?= number_format($pago['monto'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex space-x-2">
                                        <button onclick="editarPago(<?= htmlspecialchars(json_encode($pago)) ?>)" 
                                                class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </button>
                                        <button onclick="confirmarEliminarPago(<?= $pago['id'] ?>, '<?= htmlspecialchars($pago['descripcion']) ?>')" 
                                                class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200">
                                            <i class="fas fa-trash mr-1"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Configuración de Chart.js
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';

    // Gráfico de métodos
    const metodosData = <?= json_encode($pagos_por_metodo) ?>;
    const metodosLabels = Object.keys(metodosData);
    const metodosValues = Object.values(metodosData).map(item => item.total);

    new Chart(document.getElementById('metodosChart'), {
        type: 'bar',
        data: {
            labels: metodosLabels,
            datasets: [{
                data: metodosValues,
                backgroundColor: ['#22c55e', '#3b82f6', '#eab308', '#ef4444', '#8b5cf6'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '$' + value.toLocaleString()
                    }
                }
            }
        }
    });

    // Gráfico de tendencia
    const tendenciaData = <?= json_encode($pagos_temporales) ?>;
    const tendenciaLabels = tendenciaData.map(item => {
        const fecha = new Date(item.fecha);
        return fecha.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
    });
    const tendenciaValues = tendenciaData.map(item => item.total);

    new Chart(document.getElementById('tendenciaChart'), {
        type: 'line',
        data: {
            labels: tendenciaLabels,
            datasets: [{
                label: 'Ingresos',
                data: tendenciaValues,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => '$' + value.toLocaleString()
                    }
                }
            }
        }
    });
    </script>

    <!-- Modal para Editar Pago -->
    <div id="modalEditarPago" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Editar Pago</h3>
                    <button onclick="cerrarModalEditarPago()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="formEditarPago" class="space-y-4">
                    <input type="hidden" id="editPagoId" name="id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                            <input type="date" id="editPagoFecha" name="fecha" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                            <input type="number" step="0.01" id="editPagoMonto" name="monto" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <input type="text" id="editPagoDescripcion" name="descripcion" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                               placeholder="Descripción del pago recibido">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                        <select id="editPagoMetodo" name="metodo" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" 
                                class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition duration-200">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                        <button type="button" onclick="cerrarModalEditarPago()"
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition duration-200">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmar Eliminación de Pago -->
    <div id="modalEliminarPago" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/3 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Eliminación</h3>
                <p class="text-sm text-gray-500 mb-4">
                    ¿Estás seguro de que deseas eliminar este pago?
                </p>
                <div class="bg-gray-50 p-3 rounded-md mb-4">
                    <p id="pagoAEliminar" class="text-sm font-medium text-gray-900"></p>
                </div>
                <p class="text-xs text-red-600 mb-6">
                    Esta acción no se puede deshacer.
                </p>
                <div class="flex space-x-3">
                    <button onclick="eliminarPago()" 
                            class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-200">
                        <i class="fas fa-trash mr-2"></i> Sí, Eliminar
                    </button>
                    <button onclick="cerrarModalEliminarPago()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition duration-200">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let pagoAEditarId = null;
        let pagoAEliminarId = null;

        // Función para abrir modal de edición de pago
        function editarPago(pago) {
            pagoAEditarId = pago.id;
            
            document.getElementById('editPagoId').value = pago.id;
            document.getElementById('editPagoFecha').value = pago.fecha;
            document.getElementById('editPagoMonto').value = pago.monto;
            document.getElementById('editPagoDescripcion').value = pago.descripcion;
            document.getElementById('editPagoMetodo').value = pago.Metodo;
            
            document.getElementById('modalEditarPago').classList.remove('hidden');
        }

        // Función para cerrar modal de edición de pago
        function cerrarModalEditarPago() {
            document.getElementById('modalEditarPago').classList.add('hidden');
            pagoAEditarId = null;
        }

        // Función para confirmar eliminación de pago
        function confirmarEliminarPago(id, descripcion) {
            pagoAEliminarId = id;
            document.getElementById('pagoAEliminar').textContent = descripcion;
            document.getElementById('modalEliminarPago').classList.remove('hidden');
        }

        // Función para cerrar modal de eliminación de pago
        function cerrarModalEliminarPago() {
            document.getElementById('modalEliminarPago').classList.add('hidden');
            pagoAEliminarId = null;
        }

        // Función para procesar edición de pago
        document.getElementById('formEditarPago').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'edit');
            
            fetch('procesar_pagos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pago actualizado exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });

        // Función para eliminar pago
        function eliminarPago() {
            if (!pagoAEliminarId) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', pagoAEliminarId);
            
            fetch('procesar_pagos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pago eliminado exitosamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }

        // Cerrar modales con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalEditarPago();
                cerrarModalEliminarPago();
            }
        });
    </script>
</body>
</html>
