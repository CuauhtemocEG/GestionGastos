<?php
require_once '../config.php';
require_once '../GastosManager.php';

// Verificar autenticación
session_start();
if (!isset($_SESSION['user_id'])) {
    die('No autorizado');
}

// Usar el GastosManager global si existe, sino crear uno
if (isset($GLOBALS['manager']) && $GLOBALS['manager'] instanceof GastosManager) {
    $manager = $GLOBALS['manager'];
} else {
    $manager = new GastosManager($conexion);
}

// Obtener filtros de la URL
$filtros = [
    'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
    'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
    'tipo' => $_GET['tipo'] ?? 'todos',
    'metodo' => $_GET['metodo'] ?? 'todos',
    'categoria' => $_GET['categoria'] ?? 'todos',
    'monto_min' => $_GET['monto_min'] ?? '',
    'monto_max' => $_GET['monto_max'] ?? '',
    'descripcion' => $_GET['descripcion'] ?? '',
    'limite' => 1000 // Sin paginación para exportación
];

$formato = $_GET['exportar'] ?? 'excel';
$pagina = $_GET['page'] ?? 'gastos';

// Obtener datos según la página
if ($pagina === 'gastos') {
    $datos = $manager->obtenerGastosFiltrados($filtros);
    $titulo = 'Gastos';
    $columnas = ['ID', 'Fecha', 'Descripcion', 'Tipo', 'Monto', 'Metodo'];
} elseif ($pagina === 'pagos') {
    $datos = $manager->obtenerPagosFiltrados($filtros);
    $titulo = 'Pagos';
    $columnas = ['id', 'fecha', 'descripcion', 'monto', 'Metodo'];
} else {
    // Para resumen, obtener ambos
    $gastos = $manager->obtenerGastosFiltrados($filtros);
    $pagos = $manager->obtenerPagosFiltrados($filtros);
    
    $datos = [];
    foreach ($gastos as $gasto) {
        $datos[] = [
            'Tipo' => 'Gasto',
            'Fecha' => $gasto['Fecha'],
            'Descripcion' => $gasto['Descripcion'],
            'Monto' => -$gasto['Monto'], // Negativo para gastos
            'Metodo' => $gasto['Metodo'],
            'Categoria' => $gasto['Tipo']
        ];
    }
    foreach ($pagos as $pago) {
        $datos[] = [
            'Tipo' => 'Pago',
            'Fecha' => $pago['fecha'],
            'Descripcion' => $pago['descripcion'],
            'Monto' => $pago['monto'], // Positivo para pagos
            'Metodo' => $pago['Metodo'],
            'Categoria' => ''
        ];
    }
    
    // Ordenar por fecha
    usort($datos, function($a, $b) {
        return strcmp($b['Fecha'], $a['Fecha']);
    });
    
    $titulo = 'Resumen_Financiero';
    $columnas = ['Tipo', 'Fecha', 'Descripcion', 'Monto', 'Metodo', 'Categoria'];
}

if ($formato === 'excel') {
    exportarExcel($datos, $titulo, $columnas, $filtros);
} elseif ($formato === 'pdf') {
    exportarPDF($datos, $titulo, $columnas, $filtros);
}

function exportarExcel($datos, $titulo, $columnas, $filtros) {
    $filename = $titulo . '_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Información de filtros
    fputcsv($output, ['REPORTE: ' . strtoupper($titulo)]);
    fputcsv($output, ['Generado el: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, ['Período: ' . $filtros['fecha_inicio'] . ' a ' . $filtros['fecha_fin']]);
    fputcsv($output, []);
    
    // Encabezados
    fputcsv($output, $columnas);
    
    // Datos
    foreach ($datos as $fila) {
        $fila_exportar = [];
        foreach ($columnas as $columna) {
            $valor = $fila[$columna] ?? $fila[strtolower($columna)] ?? '';
            if ($columna === 'Monto' || $columna === 'monto') {
                $valor = number_format($valor, 2, '.', '');
            }
            $fila_exportar[] = $valor;
        }
        fputcsv($output, $fila_exportar);
    }
    
    // Totales
    $total = array_sum(array_column($datos, 'Monto')) ?: array_sum(array_column($datos, 'monto'));
    fputcsv($output, []);
    fputcsv($output, ['TOTAL:', '', '', number_format($total, 2, '.', '')]);
    
    fclose($output);
}

function exportarPDF($datos, $titulo, $columnas, $filtros) {
    // Para PDF usaremos HTML y CSS básico
    $filename = $titulo . '_' . date('Y-m-d_H-i-s') . '.html';
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte ' . $titulo . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .filtros { background: #f5f5f5; padding: 10px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .total { font-weight: bold; background-color: #e8f4f8; }
            .gasto { color: #dc3545; }
            .pago { color: #28a745; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>REPORTE: ' . strtoupper($titulo) . '</h1>
            <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
        </div>
        
        <div class="filtros">
            <strong>Filtros aplicados:</strong><br>
            Período: ' . $filtros['fecha_inicio'] . ' a ' . $filtros['fecha_fin'] . '<br>';
    
    if ($filtros['tipo'] !== 'todos') echo 'Tipo: ' . $filtros['tipo'] . '<br>';
    if ($filtros['metodo'] !== 'todos') echo 'Método: ' . $filtros['metodo'] . '<br>';
    if ($filtros['categoria'] !== 'todos') echo 'Categoría: ' . $filtros['categoria'] . '<br>';
    
    echo '</div>
        
        <table>
            <thead>
                <tr>';
    
    foreach ($columnas as $columna) {
        echo '<th>' . ucfirst($columna) . '</th>';
    }
    
    echo '</tr>
            </thead>
            <tbody>';
    
    $total = 0;
    foreach ($datos as $fila) {
        echo '<tr>';
        foreach ($columnas as $columna) {
            $valor = $fila[$columna] ?? $fila[strtolower($columna)] ?? '';
            if ($columna === 'Monto' || $columna === 'monto') {
                $total += $valor;
                $valor = '$' . number_format($valor, 2);
                $clase = $valor < 0 ? 'gasto' : 'pago';
                echo '<td class="' . $clase . '">' . $valor . '</td>';
            } else {
                echo '<td>' . htmlspecialchars($valor) . '</td>';
            }
        }
        echo '</tr>';
    }
    
    echo '</tbody>
            <tfoot>
                <tr class="total">
                    <td colspan="' . (count($columnas) - 1) . '"><strong>TOTAL:</strong></td>
                    <td><strong>$' . number_format($total, 2) . '</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>';
}
?>
