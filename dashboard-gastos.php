<?php
// Dashboard moderno para gastos
global $conexion;

// Verificar conexión y obtener datos
$gastos_data = [];
$estadisticas = [
    'total_registros' => 0,
    'total_monto' => 0,
    'promedio' => 0,
    'mayor_gasto' => 0
];

try {
    if (isset($conexion) && $conexion->ping()) {
        // Obtener filtros
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $tipo_filtro = $_GET['tipo'] ?? 'todos';
        $metodo_filtro = $_GET['metodo'] ?? 'todos';

        // Construir consulta
        $where = "WHERE Fecha BETWEEN ? AND ?";
        $params = [$fecha_inicio, $fecha_fin];
        $types = "ss";

        if ($tipo_filtro !== 'todos') {
            $where .= " AND Tipo = ?";
            $params[] = $tipo_filtro;
            $types .= "s";
        }

        if ($metodo_filtro !== 'todos') {
            $where .= " AND Metodo = ?";
            $params[] = $metodo_filtro;
            $types .= "s";
        }

        // Obtener gastos
        $sql = "SELECT * FROM Gastos $where ORDER BY Fecha DESC, ID DESC LIMIT 100";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $gastos_data = $result->fetch_all(MYSQLI_ASSOC);

        // Estadísticas
        $sql_stats = "SELECT 
            COUNT(*) as total_registros,
            COALESCE(SUM(Monto), 0) as total_monto,
            COALESCE(AVG(Monto), 0) as promedio,
            COALESCE(MAX(Monto), 0) as mayor_gasto
        FROM Gastos $where";
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
    $gastos_data = [
        ['ID' => 1, 'Fecha' => '2025-09-08', 'Descripcion' => 'Compra supermercado', 'Monto' => 350.50, 'Tipo' => 'Mercado', 'Metodo' => 'Tarjeta'],
        ['ID' => 2, 'Fecha' => '2025-09-07', 'Descripcion' => 'Gasolina', 'Monto' => 850.00, 'Tipo' => 'Transporte', 'Metodo' => 'Efectivo'],
        ['ID' => 3, 'Fecha' => '2025-09-06', 'Descripcion' => 'Restaurante', 'Monto' => 280.75, 'Tipo' => 'Comida', 'Metodo' => 'Tarjeta'],
        ['ID' => 4, 'Fecha' => '2025-09-05', 'Descripcion' => 'Internet', 'Monto' => 599.00, 'Tipo' => 'Fijo', 'Metodo' => 'Transferencia'],
        ['ID' => 5, 'Fecha' => '2025-09-04', 'Descripcion' => 'Farmacia', 'Monto' => 145.00, 'Tipo' => 'Salud', 'Metodo' => 'Efectivo']
    ];
    $estadisticas = [
        'total_registros' => 5,
        'total_monto' => 2225.25,
        'promedio' => 445.05,
        'mayor_gasto' => 850.00
    ];
}

// Análisis por categorías
$gastos_por_categoria = [];
foreach ($gastos_data as $gasto) {
    $categoria = $gasto['Tipo'] ?: 'Sin categoría';
    if (!isset($gastos_por_categoria[$categoria])) {
        $gastos_por_categoria[$categoria] = ['total' => 0, 'cantidad' => 0];
    }
    $gastos_por_categoria[$categoria]['total'] += $gasto['Monto'];
    $gastos_por_categoria[$categoria]['cantidad']++;
}
arsort($gastos_por_categoria);

// Análisis por método
$gastos_por_metodo = [];
foreach ($gastos_data as $gasto) {
    $metodo = $gasto['Metodo'];
    $gastos_por_metodo[$metodo] = ($gastos_por_metodo[$metodo] ?? 0) + $gasto['Monto'];
}
arsort($gastos_por_metodo);
?>
<div class="min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <i class="fas fa-credit-card text-2xl text-red-600 mr-3"></i>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard de Gastos</h1>
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
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <input type="hidden" name="page" value="gastos">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="<?= $_GET['fecha_inicio'] ?? date('Y-m-01') ?>"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="<?= $_GET['fecha_fin'] ?? date('Y-m-d') ?>"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select name="tipo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                        <option value="todos">Todas las categorías</option>
                        <option value="Central" <?= ($_GET['tipo'] ?? '') === 'Central' ? 'selected' : '' ?>>Central</option>
                        <option value="Mercado" <?= ($_GET['tipo'] ?? '') === 'Mercado' ? 'selected' : '' ?>>Mercado</option>
                        <option value="Mantenimiento" <?= ($_GET['tipo'] ?? '') === 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                        <option value="Inversiones" <?= ($_GET['tipo'] ?? '') === 'Inversiones' ? 'selected' : '' ?>>Inversiones</option>
                        <option value="Fijo" <?= ($_GET['tipo'] ?? '') === 'Fijo' ? 'selected' : '' ?>>Fijo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                    <select name="metodo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                        <option value="todos">Todos los métodos</option>
                        <option value="Efectivo" <?= ($_GET['metodo'] ?? '') === 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                        <option value="Tarjeta" <?= ($_GET['metodo'] ?? '') === 'Tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                        <option value="Transferencia" <?= ($_GET['metodo'] ?? '') === 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-search mr-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Estadísticas Principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Total Gastos</p>
                        <p class="text-3xl font-bold"><?= $estadisticas['total_registros'] ?></p>
                    </div>
                    <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-receipt text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Monto Total</p>
                        <p class="text-2xl font-bold">$<?= number_format($estadisticas['total_monto'], 2) ?></p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-dollar-sign text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Promedio</p>
                        <p class="text-2xl font-bold">$<?= number_format($estadisticas['promedio'], 2) ?></p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Mayor Gasto</p>
                        <p class="text-2xl font-bold">$<?= number_format($estadisticas['mayor_gasto'], 2) ?></p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                        <i class="fas fa-arrow-up text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Gastos por Categoría -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-purple-600"></i>
                    Gastos por Categoría
                </h3>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="categoriasChart"></canvas>
                </div>
            </div>

            <!-- Gastos por Método -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-credit-card mr-2 text-green-600"></i>
                    Gastos por Método de Pago
                </h3>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="metodosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de Gastos -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-list mr-2 text-gray-600"></i>
                    Últimos Gastos
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach (array_slice($gastos_data, 0, 20) as $gasto): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('d/m/Y', strtotime($gasto['Fecha'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($gasto['Descripcion']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($gasto['Tipo']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <?= htmlspecialchars($gasto['Metodo']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">
                                    $<?= number_format($gasto['Monto'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex space-x-2">
                                        <button onclick="editarGasto(<?= htmlspecialchars(json_encode($gasto)) ?>)"
                                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </button>
                                        <button onclick="confirmarEliminarGasto(<?= $gasto['ID'] ?>, '<?= htmlspecialchars($gasto['Descripcion']) ?>')"
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>
    // Configuración de Chart.js
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';

    // Gráfico de categorías
    const categoriasData = <?= json_encode($gastos_por_categoria) ?>;
    const categoriasLabels = Object.keys(categoriasData);
    const categoriasValues = Object.values(categoriasData).map(item => item.total);

    new Chart(document.getElementById('categoriasChart'), {
        type: 'doughnut',
        data: {
            labels: categoriasLabels,
            datasets: [{
                data: categoriasValues,
                backgroundColor: [
                    '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6',
                    '#8b5cf6', '#ec4899', '#6b7280', '#14b8a6', '#f59e0b'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20
                    }
                }
            }
        }
    });

    // Gráfico de métodos
    const metodosData = <?= json_encode($gastos_por_metodo) ?>;
    const metodosLabels = Object.keys(metodosData);
    const metodosValues = Object.values(metodosData);

    new Chart(document.getElementById('metodosChart'), {
        type: 'bar',
        data: {
            labels: metodosLabels,
            datasets: [{
                data: metodosValues,
                backgroundColor: ['#22c55e', '#3b82f6', '#eab308', '#ef4444'],
                borderRadius: 8
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
                        callback: value => '$' + value.toLocaleString()
                    }
                }
            }
        }
    });
</script>

<!-- Modal para Editar Gasto -->
<div id="modalEditarGasto" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Editar Gasto</h3>
                <button onclick="cerrarModalEditar()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="formEditarGasto" class="space-y-4">
                <input type="hidden" id="editGastoId" name="id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                        <input type="date" id="editFecha" name="fecha" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                        <input type="number" step="0.01" id="editMonto" name="monto" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <input type="text" id="editDescripcion" name="descripcion" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Descripción del gasto">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <select id="editTipo" name="tipo" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Central">Central</option>
                            <option value="Mercado">Mercado</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Inversiones">Inversiones</option>
                            <option value="Fijo">Fijo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                        <select id="editMetodo" name="metodo" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar Cambios
                    </button>
                    <button type="button" onclick="cerrarModalEditar()"
                        class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition duration-200">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Confirmar Eliminación -->
<div id="modalEliminarGasto" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Eliminación</h3>
            <p class="text-sm text-gray-500 mb-4">
                ¿Estás seguro de que deseas eliminar este gasto?
            </p>
            <div class="bg-gray-50 p-3 rounded-md mb-4">
                <p id="gastoAEliminar" class="text-sm font-medium text-gray-900"></p>
            </div>
            <p class="text-xs text-red-600 mb-6">
                Esta acción no se puede deshacer.
            </p>
            <div class="flex space-x-3">
                <button onclick="eliminarGasto()"
                    class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-200">
                    <i class="fas fa-trash mr-2"></i> Sí, Eliminar
                </button>
                <button onclick="cerrarModalEliminar()"
                    class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition duration-200">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let gastoAEditarId = null;
    let gastoAEliminarId = null;

    // Función para abrir modal de edición
    function editarGasto(gasto) {
        console.log('Editando gasto:', gasto);
        
        // Validar que el gasto tiene un ID válido
        if (!gasto.ID || gasto.ID == '0' || isNaN(gasto.ID)) {
            alert('Error: No se puede editar un gasto sin ID válido');
            return;
        }

        gastoAEditarId = parseInt(gasto.ID);

        document.getElementById('editGastoId').value = gasto.ID;
        document.getElementById('editFecha').value = gasto.Fecha;
        document.getElementById('editMonto').value = parseFloat(gasto.Monto);
        document.getElementById('editDescripcion').value = gasto.Descripcion || '';
        document.getElementById('editTipo').value = gasto.Tipo || '';
        document.getElementById('editMetodo').value = gasto.Metodo || '';

        // Debug - verificar valores asignados
        console.log('Valores asignados al formulario:');
        console.log('ID:', document.getElementById('editGastoId').value);
        console.log('Fecha:', document.getElementById('editFecha').value);
        console.log('Monto:', document.getElementById('editMonto').value);
        console.log('Descripción:', document.getElementById('editDescripcion').value);
        console.log('Tipo:', document.getElementById('editTipo').value);
        console.log('Método:', document.getElementById('editMetodo').value);

        document.getElementById('modalEditarGasto').classList.remove('hidden');
    }

    // Función para cerrar modal de edición
    function cerrarModalEditar() {
        document.getElementById('modalEditarGasto').classList.add('hidden');
        gastoAEditarId = null;
    }

    // Función para confirmar eliminación
    function confirmarEliminarGasto(id, descripcion) {
        console.log('Confirmando eliminación del gasto ID:', id);
        
        // Validar ID
        if (!id || id == '0' || isNaN(id)) {
            alert('Error: ID de gasto inválido para eliminación');
            return;
        }

        gastoAEliminarId = parseInt(id);
        document.getElementById('gastoAEliminar').textContent = descripcion || 'Sin descripción';
        document.getElementById('modalEliminarGasto').classList.remove('hidden');
    }

    // Función para cerrar modal de eliminación
    function cerrarModalEliminar() {
        document.getElementById('modalEliminarGasto').classList.add('hidden');
        gastoAEliminarId = null;
    }

    // Variables para prevenir múltiples envíos
    let procesandoEdicion = false;
    let procesandoEliminacion = false;

    // Función para procesar edición
    document.getElementById('formEditarGasto').addEventListener('submit', function(e) {
        e.preventDefault();

        // Prevenir múltiples envíos
        if (procesandoEdicion) {
            console.log('Ya se está procesando una edición');
            return;
        }

        procesandoEdicion = true;
        
        // Validar que tenemos un ID válido
        const gastoId = document.getElementById('editGastoId').value;
        if (!gastoId || gastoId == '0' || isNaN(gastoId)) {
            alert('Error: ID de gasto inválido');
            procesandoEdicion = false;
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'edit');

        // Debug - verificar datos que se envían
        console.log('Enviando datos:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }

        fetch('procesar_gastos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta del servidor:', data);
                if (data.success) {
                    alert('Gasto actualizado exitosamente');
                    cerrarModalEditar();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud: ' + error.message);
            })
            .finally(() => {
                procesandoEdicion = false;
            });
    });

    // Función para eliminar gasto
    function eliminarGasto() {
        if (!gastoAEliminarId || procesandoEliminacion) return;

        procesandoEliminacion = true;

        // Validar ID
        if (gastoAEliminarId == '0' || isNaN(gastoAEliminarId)) {
            alert('Error: ID de gasto inválido');
            procesandoEliminacion = false;
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', gastoAEliminarId);

        console.log('Eliminando gasto con ID:', gastoAEliminarId);

        fetch('procesar_gastos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta del servidor:', data);
                if (data.success) {
                    alert('Gasto eliminado exitosamente');
                    cerrarModalEliminar();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud: ' + error.message);
            })
            .finally(() => {
                procesandoEliminacion = false;
            });
    }

    // Cerrar modales con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalEditar();
            cerrarModalEliminar();
        }
    });
</script>