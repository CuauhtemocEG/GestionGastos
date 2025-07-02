<?php
include 'config.php';

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Funciones de obtención y sumatoria de gastos (igual que el original)
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
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='En Sitio'";
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
// Totales
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', Arial, sans-serif;}
        .main-container { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 2rem 2.5rem; margin: 2rem auto; max-width: 1100px;}
        .section-title { margin-top: 2rem; margin-bottom: 1rem; font-size: 1.3rem; color: #295099; border-bottom: 2px solid #e9ecef; padding-bottom: 0.4rem; font-weight: 600;}
        footer { color: #888; font-size: 0.95rem; margin-top: 2rem; text-align: center; }
        .badge-total { font-size: 1.05em; background:#0071b3; }
        .btn-collapse { margin-right: 0.5em; }
        .table thead { background: #f2f2f2; }
        .card { margin-bottom: 1.2em; }
    </style>
</head>
<body>
    <div class="main-container">
        <header class="mb-4">
            <h1 class="text-center mb-1">Gestión de Gastos</h1>
        </header>
        <!-- Filtro de fechas -->
        <form action="index.php" method="GET" class="mb-4">
            <div class="row align-items-end g-2">
                <div class="col-sm-4">
                    <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $fechaInicio ?>">
                </div>
                <div class="col-sm-4">
                    <label for="fecha_fin" class="form-label">Fecha de fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $fechaFin ?>">
                </div>
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                </div>
            </div>
        </form>

        <!-- Sección de categorías con collapse -->
        <div class="section-title">Categorías de Gastos</div>
        <div class="mb-3 d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseFijo" role="button" aria-expanded="false">Gasto Fijo</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseCentral" role="button" aria-expanded="false">Central de Abasto</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseSitio" role="button" aria-expanded="false">Gasto en Sitio</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseMantenimiento" role="button" aria-expanded="false">Gasto de Mantenimiento</a>
            <a class="btn btn-outline-primary btn-collapse" data-bs-toggle="collapse" href="#collapseInversiones" role="button" aria-expanded="false">Inversiones</a>
        </div>
        <!-- Collapse de cada categoría -->
        <?php
        // Array para automatizar la generación de collapses
        $categorias = [
            ['collapseFijo',         'Gastos Fijos',         $gastosTotalesFijos,         $totalGastosFijos],
            ['collapseCentral',      'Central de Abasto',    $gastosTotalesCentral,       $totalGastosCentral],
            ['collapseSitio',        'Gasto en Sitio',       $gastosTotalesSitio,         $totalGastosSitio],
            ['collapseMantenimiento','Gasto de Mantenimiento', $gastosTotalesMantenimiento, $totalGastosMantenimiento],
            ['collapseInversiones',  'Inversiones',          $gastosTotalesInversiones,   $totalGastosInversiones],
        ];
        foreach ($categorias as [$id, $nombre, $lista, $total]) { ?>
            <div class="collapse" id="<?= $id ?>">
                <div class="card card-body">
                    <h5 class="mb-3"><?= $nombre ?> <span class="badge badge-total text-bg-primary">Total: $<?= number_format($total,2) ?></span></h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                    <td>$<?= number_format($gasto['Monto'],2) ?></td>
                                    <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                    <td><a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm">Eliminar</a></td>
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

        <div class="section-title">Resumen General</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card card-body">
                    <h5 class="mb-3">Gastos con Tarjeta <span class="badge badge-total text-bg-primary">Total: $<?= number_format($totalGastos,2) ?></span></h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($gastos) > 0): foreach ($gastos as $gasto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                    <td>$<?= number_format($gasto['Monto'],2) ?></td>
                                    <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                    <td><a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm">Eliminar</a></td>
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
                    <h5 class="mb-3">Gastos en Efectivo <span class="badge badge-total text-bg-primary">Total: $<?= number_format($totalGastosEfectivo,2) ?></span></h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($gastosEfectivo) > 0): foreach ($gastosEfectivo as $gasto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                    <td>$<?= number_format($gasto['Monto'],2) ?></td>
                                    <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                    <td><a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm">Eliminar</a></td>
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

        <!-- Gastos Totales -->
        <div class="card card-body mb-3">
            <h5>Gastos Totales <span class="badge badge-total text-bg-primary">Total: $<?= number_format($totalGastosAll,2) ?></span></h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Método de Pago</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($gastosTotales) > 0): foreach ($gastosTotales as $gasto): ?>
                        <tr>
                            <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                            <td><?= htmlspecialchars($gasto['Metodo']) ?></td>
                            <td>$<?= number_format($gasto['Monto'],2) ?></td>
                            <td><?= htmlspecialchars($gasto['Fecha']) ?></td>
                            <td><a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-danger btn-sm">Eliminar</a></td>
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
            <a href="addExpenses.php" class="btn btn-success">Agregar Nuevo Gasto</a>
        </div>
        <footer class="mt-4">
            &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>