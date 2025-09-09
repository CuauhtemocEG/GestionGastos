<?php
// Usar el GastosManager global
global $manager;

// Obtener filtros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Obtener resumen de gastos por tipo
$sql_gastos = "SELECT 
    Tipo,
    COUNT(*) as cantidad,
    SUM(Monto) as total,
    AVG(Monto) as promedio
FROM Gastos 
WHERE Fecha BETWEEN ? AND ? 
GROUP BY Tipo 
ORDER BY total DESC";

$stmt = $conexion->prepare($sql_gastos);
$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$gastos_por_tipo = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener resumen de gastos por método
$sql_gastos_metodo = "SELECT 
    Metodo,
    COUNT(*) as cantidad,
    SUM(Monto) as total
FROM Gastos 
WHERE Fecha BETWEEN ? AND ? 
GROUP BY Metodo";

$stmt = $conexion->prepare($sql_gastos_metodo);
$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$gastos_por_metodo = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener resumen de pagos
$sql_pagos = "SELECT 
    Metodo,
    COUNT(*) as cantidad,
    SUM(monto) as total,
    AVG(monto) as promedio
FROM Pagos 
WHERE fecha BETWEEN ? AND ? 
GROUP BY Metodo";

$stmt = $conexion->prepare($sql_pagos);
$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$pagos_por_metodo = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Totales generales
$sql_total_gastos = "SELECT SUM(Monto) as total FROM Gastos WHERE Fecha BETWEEN ? AND ?";
$stmt = $conexion->prepare($sql_total_gastos);
$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$total_gastos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$sql_total_pagos = "SELECT SUM(monto) as total FROM Pagos WHERE fecha BETWEEN ? AND ?";
$stmt = $conexion->prepare($sql_total_pagos);
$stmt->bind_param('ss', $fechaInicio, $fechaFin);
$stmt->execute();
$total_pagos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$balance = $total_pagos - $total_gastos;
?>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
            Análisis Financiero
        </h2>
        <p class="text-sm text-gray-600">Filtra y visualiza tus gastos e ingresos</p>
    </div>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="hidden" name="page" value="resumen">
        
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
        
        <div class="flex items-end">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                <i class="fas fa-filter mr-1"></i> Actualizar Resumen
            </button>
        </div>
    </form>
</div>

<!-- Resumen General -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-credit-card text-red-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Gastos</h3>
                <p class="text-2xl font-semibold text-red-600">
                    $<?php echo number_format($total_gastos, 2); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Ingresos</h3>
                <p class="text-2xl font-semibold text-green-600">
                    $<?php echo number_format($total_pagos, 2); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 <?php echo $balance >= 0 ? 'border-blue-500' : 'border-orange-500'; ?>">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 <?php echo $balance >= 0 ? 'bg-blue-100' : 'bg-orange-100'; ?> rounded-lg flex items-center justify-center">
                    <i class="fas fa-balance-scale <?php echo $balance >= 0 ? 'text-blue-600' : 'text-orange-600'; ?> text-xl"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Balance</h3>
                <p class="text-2xl font-semibold <?php echo $balance >= 0 ? 'text-blue-600' : 'text-orange-600'; ?>">
                    $<?php echo number_format($balance, 2); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Gastos por Tipo -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-chart-pie mr-2 text-red-600"></i>
                Gastos por Tipo
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($gastos_por_tipo)): ?>
                <p class="text-gray-500 text-center py-4">No hay gastos en el período seleccionado</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($gastos_por_tipo as $gasto): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
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
                                <span class="ml-2 text-sm text-gray-600">
                                    (<?php echo $gasto['cantidad']; ?> gastos)
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">$<?php echo number_format($gasto['total'], 2); ?></p>
                                <p class="text-xs text-gray-500">Prom: $<?php echo number_format($gasto['promedio'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gastos por Método -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-credit-card mr-2 text-blue-600"></i>
                Gastos por Método de Pago
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($gastos_por_metodo)): ?>
                <p class="text-gray-500 text-center py-4">No hay gastos en el período seleccionado</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($gastos_por_metodo as $gasto): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <?php 
                                $metodo_clases = [
                                    'Tarjeta' => 'bg-indigo-100 text-indigo-800',
                                    'Efectivo' => 'bg-green-100 text-green-800',
                                    'Transferencia' => 'bg-blue-100 text-blue-800'
                                ];
                                $metodo_iconos = [
                                    'Tarjeta' => 'fa-credit-card',
                                    'Efectivo' => 'fa-money-bill',
                                    'Transferencia' => 'fa-exchange-alt'
                                ];
                                $clase = $metodo_clases[$gasto['Metodo']] ?? 'bg-gray-100 text-gray-800';
                                $icono = $metodo_iconos[$gasto['Metodo']] ?? 'fa-question';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $clase; ?>">
                                    <i class="fas <?php echo $icono; ?> mr-1"></i>
                                    <?php echo $gasto['Metodo']; ?>
                                </span>
                                <span class="ml-2 text-sm text-gray-600">
                                    (<?php echo $gasto['cantidad']; ?> gastos)
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">$<?php echo number_format($gasto['total'], 2); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo number_format(($gasto['total'] / $total_gastos) * 100, 1); ?>% del total
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ingresos por Método -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-money-bill mr-2 text-green-600"></i>
                Ingresos por Método de Pago
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($pagos_por_metodo)): ?>
                <p class="text-gray-500 text-center py-4">No hay pagos en el período seleccionado</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pagos_por_metodo as $pago): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <?php 
                                $metodo_clases = [
                                    'Tarjeta' => 'bg-indigo-100 text-indigo-800',
                                    'Efectivo' => 'bg-green-100 text-green-800',
                                    'Transferencia' => 'bg-blue-100 text-blue-800'
                                ];
                                $metodo_iconos = [
                                    'Tarjeta' => 'fa-credit-card',
                                    'Efectivo' => 'fa-money-bill',
                                    'Transferencia' => 'fa-exchange-alt'
                                ];
                                $clase = $metodo_clases[$pago['Metodo']] ?? 'bg-gray-100 text-gray-800';
                                $icono = $metodo_iconos[$pago['Metodo']] ?? 'fa-question';
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $clase; ?>">
                                    <i class="fas <?php echo $icono; ?> mr-1"></i>
                                    <?php echo $pago['Metodo']; ?>
                                </span>
                                <span class="ml-2 text-sm text-gray-600">
                                    (<?php echo $pago['cantidad']; ?> pagos)
                                </span>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">$<?php echo number_format($pago['total'], 2); ?></p>
                                <p class="text-xs text-gray-500">Prom: $<?php echo number_format($pago['promedio'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Período Seleccionado -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-calendar mr-2 text-purple-600"></i>
                Información del Período
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Fecha de inicio:</span>
                    <span class="font-medium"><?php echo date('d/m/Y', strtotime($fechaInicio)); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Fecha de fin:</span>
                    <span class="font-medium"><?php echo date('d/m/Y', strtotime($fechaFin)); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Días analizados:</span>
                    <span class="font-medium">
                        <?php 
                        $inicio = new DateTime($fechaInicio);
                        $fin = new DateTime($fechaFin);
                        $diferencia = $inicio->diff($fin);
                        echo $diferencia->days + 1; 
                        ?> días
                    </span>
                </div>
                <hr class="my-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Promedio diario gastos:</span>
                    <span class="font-medium text-red-600">
                        $<?php echo number_format($total_gastos / ($diferencia->days + 1), 2); ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Promedio diario ingresos:</span>
                    <span class="font-medium text-green-600">
                        $<?php echo number_format($total_pagos / ($diferencia->days + 1), 2); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
