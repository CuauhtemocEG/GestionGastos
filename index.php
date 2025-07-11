<?php
include 'config.php';

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

function obtenerGastos($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Metodo='Tarjeta'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosEfectivo($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Metodo='Efectivo'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosTotales($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function calcularTotal($gastos) {
    $total = 0;
    foreach ($gastos as $gasto) $total += $gasto['Monto'];
    return $total;
}
function obtenerGastosFijos($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Fijo'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosCentral($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Central'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosSitio($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Mercado'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosMantenimiento($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Mantenimiento'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosInversiones($conexion, $fechaInicio, $fechaFin) {
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Inversiones'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
$gastos = obtenerGastos($conexion, $fechaInicio, $fechaFin);
$gastosEfectivo = obtenerGastosEfectivo($conexion, $fechaInicio, $fechaFin);
$gastosTotales = obtenerGastosTotales($conexion, $fechaInicio, $fechaFin);
$gastosTotalesFijos = obtenerGastosFijos($conexion, $fechaInicio, $fechaFin);
$gastosTotalesCentral = obtenerGastosCentral($conexion, $fechaInicio, $fechaFin);
$gastosTotalesSitio = obtenerGastosSitio($conexion, $fechaInicio, $fechaFin);
$gastosTotalesMantenimiento = obtenerGastosMantenimiento($conexion, $fechaInicio, $fechaFin);
$gastosTotalesInversiones = obtenerGastosInversiones($conexion, $fechaInicio, $fechaFin);

$totalGastos = calcularTotal($gastos);
$totalGastosEfectivo = calcularTotal($gastosEfectivo);
$totalGastosAll = calcularTotal($gastosTotales);
$totalGastosFijos = calcularTotal($gastosTotalesFijos);
$totalGastosCentral = calcularTotal($gastosTotalesCentral);
$totalGastosSitio = calcularTotal($gastosTotalesSitio);
$totalGastosMantenimiento = calcularTotal($gastosTotalesMantenimiento);
$totalGastosInversiones = calcularTotal($gastosTotalesInversiones);

$labelsTipos = ["Fijo", "Central", "Mercado", "Mantenimiento", "Inversiones"];
$dataTipos = [
    $totalGastosFijos,
    $totalGastosCentral,
    $totalGastosSitio,
    $totalGastosMantenimiento,
    $totalGastosInversiones
];

$labelsMetodo = ["Tarjeta", "Efectivo"];
$dataMetodo = [
    $totalGastos,
    $totalGastosEfectivo
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', Arial, sans-serif;}
        .main-container { background: #fff; border-radius: 10px; box-shadow: 0 2px 16px rgba(0,0,0,0.09); padding: 2.5rem 2.5rem; margin: 2rem auto; max-width: 90%;}
        .section-title { margin-top: 2rem; margin-bottom: 1rem; font-size: 1.3rem; color: #2365bc; border-bottom: 2px solid #e9ecef; padding-bottom: 0.4rem; font-weight: 600;}
        footer { color: #888; font-size: 0.96rem; margin-top: 2rem; text-align: center; }
        .badge-total { font-size: 1.08em; background:#0d6efd; }
        .btn-collapse { margin-right: 0.5em; }
        .table thead { background: #f6fafd; }
        .card { margin-bottom: 1.2em; border-radius: 0.75em; }
        h1, h3, h5 { letter-spacing: 0.5px;}
        .table-sm td, .table-sm th { padding: 0.45rem;}
        .icon-section { font-size: 1.5em; color: #0d6efd; margin-right: 0.5em;}
        .chart-card { background: #f9fbff; border: 1px solid #e0e8f3; padding: 1em 1.5em; border-radius: 12px; }
        .chart-title { font-size: 1.08em; color: #295099; font-weight: 600;}
        .btn-success { font-size: 1.1em;}
        .table td i.bi { font-size: 1.15em; vertical-align: middle;}
    </style>
</head>
<body>
    <div class="main-container">
        <header class="mb-4">
            <h1 class="text-center mb-2"><i class="bi bi-cash-coin icon-section"></i>Gestión de Gastos</h1>
            <p class="text-center text-muted">Visualiza y controla tus gastos de manera ordenada y profesional.</p>
        </header>

        <form action="index.php" method="GET" class="mb-4">
            <div class="row align-items-end g-2">
                <div class="col-sm-4">
                    <label for="fecha_inicio" class="form-label"><i class="bi bi-calendar-date"></i> Fecha de inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $fechaInicio ?>">
                </div>
                <div class="col-sm-4">
                    <label for="fecha_fin" class="form-label"><i class="bi bi-calendar-date-fill"></i> Fecha de fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $fechaFin ?>">
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-card mb-3">
                    <div class="chart-title mb-1"><i class="bi bi-pie-chart-fill"></i> Gastos por Método de Pago</div>
                    <canvas id="graficoMetodo"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card mb-3">
                    <div class="chart-title mb-1"><i class="bi bi-bar-chart-fill"></i> Gastos por Categoría</div>
                    <canvas id="graficoTipos"></canvas>
                </div>
            </div>
        </div>

        <div class="section-title"><i class="bi bi-folder2-open"></i> Categorías de Gastos</div>
        <div class="mb-3 d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseFijo" role="button" aria-expanded="false"><i class="bi bi-house-gear"></i> Gasto Fijo</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseCentral" role="button" aria-expanded="false"><i class="bi bi-basket2"></i> Central de Abasto</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseSitio" role="button" aria-expanded="false"><i class="bi bi-shop"></i> Mercado</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseMantenimiento" role="button" aria-expanded="false"><i class="bi bi-tools"></i> Gasto de Mantenimiento</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseInversiones" role="button" aria-expanded="false"><i class="bi bi-bar-chart-line"></i> Inversiones</a>
        </div>

        <?php
        $categorias = [
            ['collapseFijo',         'Gastos Fijos',         $gastosTotalesFijos,         $totalGastosFijos,         'bi bi-house-gear'],
            ['collapseCentral',      'Central de Abasto',    $gastosTotalesCentral,       $totalGastosCentral,       'bi bi-basket2'],
            ['collapseSitio',        'Mercado',       $gastosTotalesSitio,         $totalGastosSitio,         'bi bi-shop'],
            ['collapseMantenimiento','Gasto de Mantenimiento', $gastosTotalesMantenimiento, $totalGastosMantenimiento, 'bi bi-tools'],
            ['collapseInversiones',  'Inversiones',          $gastosTotalesInversiones,   $totalGastosInversiones,   'bi bi-bar-chart-line'],
        ];
        foreach ($categorias as [$id, $nombre, $lista, $total, $icon]) { ?>
            <div class="collapse" id="<?= $id ?>">
                <div class="card card-body">
                    <h5 class="mb-3"><i class="<?= $icon ?> me-2"></i><?= $nombre ?>
                        <span class="badge badge-total text-bg-primary">Total: $<?= number_format($total,2) ?></span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-card-text"></i> Descripción</th>
                                    <th><i class="bi bi-credit-card-2-front"></i> Método</th>
                                    <th><i class="bi bi-currency-dollar"></i> Monto</th>
                                    <th><i class="bi bi-calendar-event"></i> Fecha</th>
                                    <th><i class="bi bi-tools"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                    <td>$<?= number_format($gasto['Monto'],2) ?></td>
                                    <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                    <td>
                                        <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="section-title"><i class="bi bi-table"></i> Resumen General</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card card-body">
                    <h5 class="mb-3"><i class="bi bi-credit-card-2-front"></i> Gastos con Tarjeta
                        <span class="badge badge-total text-bg-primary">Total: $<?= number_format($totalGastos,2) ?></span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-card-text"></i> Descripción</th>
                                    <th><i class="bi bi-credit-card-2-front"></i> Método</th>
                                    <th><i class="bi bi-currency-dollar"></i> Monto</th>
                                    <th><i class="bi bi-calendar-event"></i> Fecha</th>
                                    <th><i class="bi bi-tools"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($gastos) > 0): foreach ($gastos as $gasto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                    <td>$<?= number_format($gasto['Monto'],2) ?></td>
                                    <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                    <td>
                                        <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-body">
                    <h5 class="mb-3"><i class="bi bi-cash"></i> Gastos en Efectivo
                        <span class="badge badge-total text-bg-primary">Total: $<?= number_format($totalGastosEfectivo,2) ?></span>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-card-text"></i> Descripción</th>
                                    <th><i class="bi bi-credit-card-2-front"></i> Método</th>
                                    <th><i class="bi bi-currency-dollar"></i> Monto</th>
                                    <th><i class="bi bi-calendar-event"></i> Fecha</th>
                                    <th><i class="bi bi-tools"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($gastosEfectivo) > 0): foreach ($gastosEfectivo as $gasto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                    <td>$<?= number_format($gasto['Monto'],2) ?></td>
                                    <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                    <td>
                                        <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-body mb-3">
            <h5><i class="bi bi-collection"></i> Gastos Totales
                <span class="badge badge-total text-bg-primary">Total: $<?= number_format($totalGastosAll,2) ?></span>
            </h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th><i class="bi bi-card-text"></i> Descripción</th>
                            <th><i class="bi bi-credit-card-2-front"></i> Método</th>
                            <th><i class="bi bi-currency-dollar"></i> Monto</th>
                            <th><i class="bi bi-calendar-event"></i> Fecha</th>
                            <th><i class="bi bi-tools"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($gastosTotales) > 0): foreach ($gastosTotales as $gasto): ?>
                        <tr>
                            <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                            <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                            <td>$<?= number_format($gasto['Monto'],2) ?></td>
                            <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                            <td>
                                <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay gastos registrados en este rango de fechas.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-end">
            <a href="addExpenses.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar Nuevo Gasto</a>
        </div>
        <footer class="mt-4">
            &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctxMetodo = document.getElementById('graficoMetodo').getContext('2d');
        new Chart(ctxMetodo, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labelsMetodo) ?>,
                datasets: [{
                    data: <?= json_encode($dataMetodo) ?>,
                    backgroundColor: ["#0d6efd", "#20c997"],
                    borderColor: "#fff",
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        const ctxTipos = document.getElementById('graficoTipos').getContext('2d');
        new Chart(ctxTipos, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelsTipos) ?>,
                datasets: [{
                    data: <?= json_encode($dataTipos) ?>,
                    label: "Total por categoría",
                    backgroundColor: [
                        "#2e86de", "#38ada9", "#e17055", "#fdcb6e", "#6c5ce7"
                    ],
                    borderRadius: 7,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>