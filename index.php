<?php
include 'config.php';

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Funciones de obtención y sumatoria de gastos (igual que el original)
function obtenerGastos($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Metodo='Tarjeta'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosEfectivo($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Metodo='Efectivo'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosTotales($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function calcularTotal($gastos)
{
    $total = 0;
    foreach ($gastos as $gasto) $total += $gasto['Monto'];
    return $total;
}
function obtenerGastosFijos($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Fijo'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosCentral($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Central'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosSitio($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='En Sitio'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosMantenimiento($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Mantenimiento'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
function obtenerGastosInversiones($conexion, $fechaInicio, $fechaFin)
{
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
        .card {
            border-radius: 12px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .section-title {
            font-size: 1.5rem;
            color: #1d3557;
            margin-bottom: 1rem;
        }

        .btn-sm {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <header class="mb-5 text-center">
            <h1 class="mb-1">📊 Gestión de Gastos</h1>
            <p class="text-muted">Controla y analiza tus gastos de forma eficiente y clara.</p>
        </header>

        <!-- Filtro de fechas -->
        <form action="index.php" method="GET" class="mb-4">
            <div class="row g-3 align-items-end justify-content-center">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                    <input type="date" class="form-control" name="fecha_inicio" value="<?= $fechaInicio ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha de fin</label>
                    <input type="date" class="form-control" name="fecha_fin" value="<?= $fechaFin ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </div>
        </form>

        <!-- Resumen superior -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary shadow">
                    <div class="card-body text-center">
                        <h5>Tarjeta 💳</h5>
                        <h4>$<?= number_format($totalGastos, 2) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success shadow">
                    <div class="card-body text-center">
                        <h5>Efectivo 💵</h5>
                        <h4>$<?= number_format($totalGastosEfectivo, 2) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-dark shadow">
                    <div class="card-body text-center">
                        <h5>Total General 📈</h5>
                        <h4>$<?= number_format($totalGastosAll, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de categorías -->
        <div class="section-title"><i class="bi bi-folder2-open"></i> Categorías de Gastos</div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($categorias as [$id, $nombre]) : ?>
                <a class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" href="#<?= $id ?>"><?= $nombre ?></a>
            <?php endforeach; ?>
        </div>

        <!-- Collapsibles dinámicos -->
        <?php foreach ($categorias as [$id, $nombre, $lista, $total]) : ?>
            <div class="collapse" id="<?= $id ?>">
                <div class="card card-body mb-3 border-start border-3 border-primary">
                    <h5><?= $nombre ?> <span class="badge bg-primary ms-2">Total: $<?= number_format($total, 2) ?></span></h5>
                    <div class="table-responsive mt-2">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción</th>
                                    <th>Método</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                            <td><?= $gasto['Metodo'] ?></td>
                                            <td>$<?= number_format($gasto['Monto'], 2) ?></td>
                                            <td><?= $gasto['Fecha'] ?></td>
                                            <td>
                                                <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="btn btn-outline-danger btn-sm">🗑️</a>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay datos.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Botón de agregar nuevo -->
        <div class="text-end mt-4">
            <a href="addExpenses.php" class="btn btn-success btn-lg">➕ Agregar Nuevo Gasto</a>
        </div>

        <footer class="mt-5 text-muted text-center">
            &copy; <?= date('Y') ?> · Sistema de Gestión de Gastos · Desarrollado por CuauhtemocEG
        </footer>
    </div>

    <!-- Bootstrap Icons (para íconos) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>

</html>