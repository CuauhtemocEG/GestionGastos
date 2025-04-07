<?php
include 'config.php';

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');  // Fecha de inicio por defecto el primer día del mes
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');          // Fecha de fin por defecto el día de hoy

// Función para obtener los gastos en Tarjeta dentro de un rango de fechas
function obtenerGastos($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Metodo='Tarjeta'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

// Obtener los gastos Tarjeta
$gastos = obtenerGastos($conexion, $fechaInicio, $fechaFin);

// Función para obtener los gastos en Efectivo dentro de un rango de fechas
function obtenerGastosEfectivo($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Metodo='Efectivo'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

// Obtener los gastos Efectivo
$gastosEfectivo = obtenerGastosEfectivo($conexion, $fechaInicio, $fechaFin);

// Función para obtener los gastos totales dentro de un rango de fechas
function obtenerGastosTotales($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

// Obtener los gastos Efectivo
$gastosTotales = obtenerGastosTotales($conexion, $fechaInicio, $fechaFin);


// Función para calcular el total de los gastos
function calcularTotal($gastos)
{
    $total = 0;
    foreach ($gastos as $gasto) {
        $total += $gasto['Monto'];
    }
    return $total;
}

// Función para calcular el total de los gastos
function calcularTotalEfectivo($gastosEfectivo)
{
    $total = 0;
    foreach ($gastosEfectivo as $gastoEfectivo) {
        $total += $gastoEfectivo['Monto'];
    }
    return $total;
}

// Función para calcular el total de los gastos
function calcularTotales($gastosTotales)
{
    $total = 0;
    foreach ($gastosTotales as $gastoEfectivo) {
        $total += $gastoEfectivo['Monto'];
    }
    return $total;
}

// FIJOS
function obtenerGastosFijos($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Fijo'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

$gastosTotalesFijos = obtenerGastosFijos($conexion, $fechaInicio, $fechaFin);

function calcularTotalesFijos($gastosTotalesFijos)
{
    $total = 0;
    foreach ($gastosTotalesFijos as $gastoFijo) {
        $total += $gastoFijo['Monto'];
    }
    return $total;
}

// CENTRAL DE ABASTOS
function obtenerGastosCentral($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Central'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

$gastosTotalesCentral = obtenerGastosCentral($conexion, $fechaInicio, $fechaFin);

function calcularTotalesCentral($gastosTotalesCentral)
{
    $total = 0;
    foreach ($gastosTotalesCentral as $gastoCentral) {
        $total += $gastoCentral['Monto'];
    }
    return $total;
}

$totalGastos = calcularTotal($gastos);
$totalGastosEfectivo = calcularTotalEfectivo($gastosEfectivo);
$totalGastosAll = calcularTotales($gastosTotales);
$totalGastosFijos = calcularTotalesFijos($gastosTotalesFijos);
$totalGastosCentral = calcularTotalesCentral($gastosTotalesCentral);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Gestión de Gastos</h1>

        <!-- Filtro de fechas -->
        <form action="index.php" method="GET">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fechaInicio; ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha de fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fechaFin; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="visibility: hidden;">Buscar</label>
                    <button type="submit" class="btn btn-primary form-control">Buscar</button>
                </div>
            </div>
        </form>
        <br>
        <!-- Collapse -->
        <p>
            <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseFijo" role="button" aria-expanded="false" aria-controls="collapseExample">
                Gasto Fijo
            </a>
            <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseCentral" role="button" aria-expanded="false" aria-controls="collapseExample">
                Central de Abasto
            </a>
            <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseSitio" role="button" aria-expanded="false" aria-controls="collapseExample">
                Gasto en Sitio
            </a>
            <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseMantenimiento" role="button" aria-expanded="false" aria-controls="collapseExample">
                Gasto de Mantenimiento
            </a>
            <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseInversiones" role="button" aria-expanded="false" aria-controls="collapseExample">
                Inversiones
            </a>
        </p>
        <div class="collapse" id="collapseFijo">
            <div class="card card-body">
                <!-- Resumen de gastos -->
                <h3>Resumen de Gastos Totales Fijos</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item">
                        <strong>Total de Gastos: </strong> $<?php echo number_format($totalGastosFijos, 2); ?>
                    </li>
                </ul>

                <!-- Mostrar los gastos Efectivo-->
                <h3>Lista de Gastos Totales Fijos</h3>
                <table class="table table-bordered">
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
                        <?php if (count($gastosTotalesFijos) > 0): ?>
                            <?php foreach ($gastosTotalesFijos as $gasto): ?>
                                <tr>
                                    <td><?php echo $gasto['Descripcion']; ?></td>
                                    <td><?php echo $gasto['Metodo']; ?></td>
                                    <td>$<?php echo number_format($gasto['Monto'], 2); ?></td>
                                    <td><?php echo $gasto['Fecha']; ?></td>
                                    <td>
                                        <a href="deleteExpenses.php?id=<?php echo $gasto['ID']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay gastos registrados en este rango de fechas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="collapse" id="collapseCentral">
            <div class="card card-body">
                <!-- Resumen de gastos -->
                <h3>Resumen de Gastos - Central de Abastos</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item">
                        <strong>Total de Gastos: </strong> $<?php echo number_format($totalGastosCentral, 2); ?>
                    </li>
                </ul>

                <!-- Mostrar los gastos Efectivo-->
                <h3>Lista de Gastos - Central de Abastos</h3>
                <table class="table table-bordered">
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
                        <?php if (count($gastosTotalesCentral) > 0): ?>
                            <?php foreach ($gastosTotalesCentral as $gasto): ?>
                                <tr>
                                    <td><?php echo $gasto['Descripcion']; ?></td>
                                    <td><?php echo $gasto['Metodo']; ?></td>
                                    <td>$<?php echo number_format($gasto['Monto'], 2); ?></td>
                                    <td><?php echo $gasto['Fecha']; ?></td>
                                    <td>
                                        <a href="deleteExpenses.php?id=<?php echo $gasto['ID']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay gastos registrados en este rango de fechas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="collapse" id="collapseSitio">
            <div class="card card-body">
                Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
            </div>
        </div>
        <div class="collapse" id="collapseMantenimiento">
            <div class="card card-body">
                Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
            </div>
        </div>
        <div class="collapse" id="collapseInversiones">
            <div class="card card-body">
                Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <!-- Resumen de gastos -->
                <h3>Resumen de Gastos con Tarjeta</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item">
                        <strong>Total de Gastos: </strong> $<?php echo number_format($totalGastos, 2); ?>
                    </li>
                </ul>

                <!-- Mostrar los gastos Tarjeta-->
                <h3>Lista de Gastos con Tarjeta</h3>
                <table class="table table-bordered">
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
                        <?php if (count($gastos) > 0): ?>
                            <?php foreach ($gastos as $gasto): ?>
                                <tr>
                                    <td><?php echo $gasto['Descripcion']; ?></td>
                                    <td><?php echo $gasto['Metodo']; ?></td>
                                    <td>$<?php echo number_format($gasto['Monto'], 2); ?></td>
                                    <td><?php echo $gasto['Fecha']; ?></td>
                                    <td>
                                        <a href="deleteExpenses.php?id=<?php echo $gasto['ID']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay gastos registrados en este rango de fechas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <!-- Resumen de gastos -->
                <h3>Resumen de Gastos en Efectivo</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item">
                        <strong>Total de Gastos: </strong> $<?php echo number_format($totalGastosEfectivo, 2); ?>
                    </li>
                </ul>

                <!-- Mostrar los gastos Efectivo-->
                <h3>Lista de Gastos con Efectivo</h3>
                <table class="table table-bordered">
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
                        <?php if (count($gastosEfectivo) > 0): ?>
                            <?php foreach ($gastosEfectivo as $gasto): ?>
                                <tr>
                                    <td><?php echo $gasto['Descripcion']; ?></td>
                                    <td><?php echo $gasto['Metodo']; ?></td>
                                    <td>$<?php echo number_format($gasto['Monto'], 2); ?></td>
                                    <td><?php echo $gasto['Fecha']; ?></td>
                                    <td>
                                        <a href="deleteExpenses.php?id=<?php echo $gasto['ID']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay gastos registrados en este rango de fechas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <hr>
        <!-- Resumen de gastos -->
        <h3>Resumen de Gastos Totales</h3>
        <ul class="list-group mb-4">
            <li class="list-group-item">
                <strong>Total de Gastos: </strong> $<?php echo number_format($totalGastosAll, 2); ?>
            </li>
        </ul>

        <!-- Mostrar los gastos Efectivo-->
        <h3>Lista de Gastos Totales</h3>
        <table class="table table-bordered">
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
                <?php if (count($gastosTotales) > 0): ?>
                    <?php foreach ($gastosTotales as $gasto): ?>
                        <tr>
                            <td><?php echo $gasto['Descripcion']; ?></td>
                            <td><?php echo $gasto['Metodo']; ?></td>
                            <td>$<?php echo number_format($gasto['Monto'], 2); ?></td>
                            <td><?php echo $gasto['Fecha']; ?></td>
                            <td>
                                <a href="deleteExpenses.php?id=<?php echo $gasto['ID']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay gastos registrados en este rango de fechas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Agregar gasto -->
        <a href="addExpenses.php" class="btn btn-success mt-3">Agregar Nuevo Gasto</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>