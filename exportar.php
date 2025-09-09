<?php
include 'config.php';
include 'auth.php';
include 'GastosManager.php';

// Verificar autenticación
$auth->requireLogin();

// Obtener filtros de la URL
$filtros = [
    'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
    'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
    'tipo' => $_GET['tipo'] ?? 'todos',
    'metodo' => $_GET['metodo'] ?? 'todos',
    'monto_min' => $_GET['monto_min'] ?? '',
    'monto_max' => $_GET['monto_max'] ?? '',
    'descripcion' => $_GET['descripcion'] ?? '',
    'orden' => $_GET['orden'] ?? 'fecha_desc',
    'limite' => 99999, // Sin límite para exportación
    'pagina' => 1
];

// Obtener tipo de exportación
$tipo_exportacion = $_GET['tipo_export'] ?? 'gastos';

// Configurar headers para descarga de Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $tipo_exportacion . '_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Función para escapar contenido para Excel
function excelEscape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Iniciar output buffer
ob_start();

if ($tipo_exportacion === 'gastos') {
    $datos = $gastosManager->obtenerGastosFiltrados($filtros);
    $estadisticas = $gastosManager->obtenerEstadisticas($filtros);
    
    echo '<table border="1">';
    echo '<tr><td colspan="6"><strong>Reporte de Gastos - ' . date('d/m/Y H:i') . '</strong></td></tr>';
    echo '<tr><td colspan="6">Período: ' . date('d/m/Y', strtotime($filtros['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($filtros['fecha_fin'])) . '</td></tr>';
    echo '<tr><td colspan="6">Total: $' . number_format($estadisticas['total_gastos'], 2) . '</td></tr>';
    echo '<tr><td colspan="6"></td></tr>';
    
    // Cabeceras
    echo '<tr>';
    echo '<th>Fecha</th>';
    echo '<th>Descripción</th>';
    echo '<th>Monto</th>';
    echo '<th>Método</th>';
    echo '<th>Tipo</th>';
    echo '<th>ID</th>';
    echo '</tr>';
    
    // Datos
    foreach ($datos as $row) {
        echo '<tr>';
        echo '<td>' . date('d/m/Y', strtotime($row['Fecha'])) . '</td>';
        echo '<td>' . excelEscape($row['Descripcion']) . '</td>';
        echo '<td>' . number_format($row['Monto'], 2) . '</td>';
        echo '<td>' . excelEscape($row['Metodo']) . '</td>';
        echo '<td>' . excelEscape($row['Tipo']) . '</td>';
        echo '<td>' . $row['ID'] . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
} elseif ($tipo_exportacion === 'pagos') {
    $datos = $gastosManager->obtenerPagosFiltrados($filtros);
    
    // Calcular total
    $total = 0;
    foreach ($datos as $pago) {
        $total += $pago['monto'];
    }
    
    echo '<table border="1">';
    echo '<tr><td colspan="5"><strong>Reporte de Pagos - ' . date('d/m/Y H:i') . '</strong></td></tr>';
    echo '<tr><td colspan="5">Período: ' . date('d/m/Y', strtotime($filtros['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($filtros['fecha_fin'])) . '</td></tr>';
    echo '<tr><td colspan="5">Total: $' . number_format($total, 2) . '</td></tr>';
    echo '<tr><td colspan="5"></td></tr>';
    
    // Cabeceras
    echo '<tr>';
    echo '<th>Fecha</th>';
    echo '<th>Descripción</th>';
    echo '<th>Monto</th>';
    echo '<th>Método</th>';
    echo '<th>ID</th>';
    echo '</tr>';
    
    // Datos
    foreach ($datos as $row) {
        echo '<tr>';
        echo '<td>' . date('d/m/Y', strtotime($row['fecha'])) . '</td>';
        echo '<td>' . excelEscape($row['descripcion']) . '</td>';
        echo '<td>' . number_format($row['monto'], 2) . '</td>';
        echo '<td>' . excelEscape($row['Metodo']) . '</td>';
        echo '<td>' . $row['id'] . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
} elseif ($tipo_exportacion === 'resumen') {
    // Estadísticas de gastos
    $estadisticasGastos = $gastosManager->obtenerEstadisticas($filtros);
    
    // Estadísticas de pagos
    $filtrosPagos = $filtros;
    $pagosFiltrados = $gastosManager->obtenerPagosFiltrados($filtrosPagos);
    $totalPagos = 0;
    foreach ($pagosFiltrados as $pago) {
        $totalPagos += $pago['monto'];
    }
    
    $saldo = $totalPagos - $estadisticasGastos['total_gastos'];
    
    echo '<table border="1">';
    echo '<tr><td colspan="4"><strong>Resumen Financiero - ' . date('d/m/Y H:i') . '</strong></td></tr>';
    echo '<tr><td colspan="4">Período: ' . date('d/m/Y', strtotime($filtros['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($filtros['fecha_fin'])) . '</td></tr>';
    echo '<tr><td colspan="4"></td></tr>';
    
    echo '<tr>';
    echo '<th>Concepto</th>';
    echo '<th>Monto</th>';
    echo '<th>%</th>';
    echo '<th>Estado</th>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>Total Ingresos (Pagos)</td>';
    echo '<td>$' . number_format($totalPagos, 2) . '</td>';
    echo '<td>100%</td>';
    echo '<td>Ingreso</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>Total Gastos</td>';
    echo '<td>$' . number_format($estadisticasGastos['total_gastos'], 2) . '</td>';
    echo '<td>' . ($totalPagos > 0 ? number_format(($estadisticasGastos['total_gastos'] / $totalPagos) * 100, 2) : '0') . '%</td>';
    echo '<td>Gasto</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td><strong>Saldo Final</strong></td>';
    echo '<td><strong>$' . number_format($saldo, 2) . '</strong></td>';
    echo '<td></td>';
    echo '<td><strong>' . ($saldo >= 0 ? 'Positivo' : 'Negativo') . '</strong></td>';
    echo '</tr>';
    
    echo '<tr><td colspan="4"></td></tr>';
    echo '<tr><td colspan="4"><strong>Detalle por Tipo de Gasto</strong></td></tr>';
    
    foreach ($estadisticasGastos['estadisticas_detalladas'] as $detalle) {
        $porcentaje = $estadisticasGastos['total_gastos'] > 0 ? 
            ($detalle['total_monto'] / $estadisticasGastos['total_gastos']) * 100 : 0;
        
        echo '<tr>';
        echo '<td>' . excelEscape($detalle['Tipo']) . ' (' . excelEscape($detalle['Metodo']) . ')</td>';
        echo '<td>$' . number_format($detalle['total_monto'], 2) . '</td>';
        echo '<td>' . number_format($porcentaje, 2) . '%</td>';
        echo '<td>' . $detalle['total_transacciones'] . ' transacciones</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
} elseif ($tipo_exportacion === 'comparativo') {
    // Comparativo mensual
    $anioActual = date('Y');
    $meses = [];
    
    for ($mes = 1; $mes <= 12; $mes++) {
        $fechaInicio = "$anioActual-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));
        
        $filtrosMes = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'tipo' => 'todos',
            'metodo' => 'todos',
            'limite' => 99999,
            'pagina' => 1
        ];
        
        $gastosDelMes = $gastosManager->obtenerEstadisticas($filtrosMes);
        $pagosDelMes = $gastosManager->obtenerPagosFiltrados($filtrosMes);
        
        $totalPagosMes = 0;
        foreach ($pagosDelMes as $pago) {
            $totalPagosMes += $pago['monto'];
        }
        
        $meses[] = [
            'mes' => $mes,
            'nombre' => date('F', strtotime($fechaInicio)),
            'gastos' => $gastosDelMes['total_gastos'],
            'pagos' => $totalPagosMes,
            'saldo' => $totalPagosMes - $gastosDelMes['total_gastos']
        ];
    }
    
    echo '<table border="1">';
    echo '<tr><td colspan="5"><strong>Comparativo Mensual ' . $anioActual . ' - ' . date('d/m/Y H:i') . '</strong></td></tr>';
    echo '<tr><td colspan="5"></td></tr>';
    
    echo '<tr>';
    echo '<th>Mes</th>';
    echo '<th>Pagos</th>';
    echo '<th>Gastos</th>';
    echo '<th>Saldo</th>';
    echo '<th>Estado</th>';
    echo '</tr>';
    
    foreach ($meses as $mes) {
        echo '<tr>';
        echo '<td>' . ucfirst($mes['nombre']) . '</td>';
        echo '<td>$' . number_format($mes['pagos'], 2) . '</td>';
        echo '<td>$' . number_format($mes['gastos'], 2) . '</td>';
        echo '<td>$' . number_format($mes['saldo'], 2) . '</td>';
        echo '<td>' . ($mes['saldo'] >= 0 ? 'Positivo' : 'Negativo') . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
}

// Obtener el contenido y enviarlo
$output = ob_get_clean();
echo $output;
exit;
?>
