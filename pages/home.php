<?php
// Obtener estadísticas rápidas
$sql = "SELECT 
    COUNT(*) as total_gastos,
    SUM(Monto) as total_monto_gastos
FROM Gastos 
WHERE MONTH(Fecha) = MONTH(CURRENT_DATE()) 
AND YEAR(Fecha) = YEAR(CURRENT_DATE())";

$result = $conexion->query($sql);
$stats_gastos = $result->fetch_assoc();

$sql = "SELECT 
    COUNT(*) as total_pagos,
    SUM(monto) as total_monto_pagos
FROM Pagos 
WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
AND YEAR(fecha) = YEAR(CURRENT_DATE())";

$result = $conexion->query($sql);
$stats_pagos = $result->fetch_assoc();

// Obtener gastos recientes
$sql = "SELECT * FROM Gastos ORDER BY Fecha DESC LIMIT 5";
$gastos_recientes = $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);

// Obtener pagos recientes
$sql = "SELECT * FROM Pagos ORDER BY fecha DESC LIMIT 5";
$pagos_recientes = $conexion->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

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
