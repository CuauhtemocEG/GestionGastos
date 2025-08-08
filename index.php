<?php
include 'config.php';

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Manejo de edición de gastos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_gasto_id'])) {
    $id = intval($_POST['edit_gasto_id']);
    $descripcion = $_POST['edit_gasto_descripcion'];
    $monto = floatval($_POST['edit_gasto_monto']);
    $fecha = $_POST['edit_gasto_fecha'];
    $metodo = $_POST['edit_gasto_metodo'];
    $tipo = $_POST['edit_gasto_tipo'];
    $stmt = $conexion->prepare("UPDATE Gastos SET Descripcion=?, Monto=?, Fecha=?, Metodo=?, Tipo=? WHERE ID=?");
    $stmt->bind_param('sdsssi', $descripcion, $monto, $fecha, $metodo, $tipo, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: index.php?fecha_inicio=' . urlencode($fechaInicio) . '&fecha_fin=' . urlencode($fechaFin));
    exit;
}

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
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Tipo='Mercado'";
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine.js para collapse -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<style>
  /* Tablas responsivas */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
</style>

<body class="bg-gray-100 font-sans">
    <nav class="bg-indigo-700 rounded-b-2xl px-4 sm:px-8 py-4 shadow-lg relative z-10">
        <div class="flex items-center justify-between">
            <span class="text-2xl font-bold text-white flex items-center gap-2">
                <svg class="w-8 h-8 inline-block text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle>
                </svg>
                GastosApp
            </span>
            <button id="nav-toggle" class="sm:hidden text-white focus:outline-none" aria-label="Abrir menú">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <div id="nav-menu" class="flex-col sm:flex-row sm:flex items-center gap-6 sm:gap-8 mt-4 sm:mt-0 hidden sm:flex">
            <a href="index.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition block sm:inline-block">Inicio</a>
            <a href="addExpenses.php" class="text-white hover:underline block py-2 sm:py-0">Agregar Gasto</a>
            <a href="pagos.php" class="text-white hover:underline block py-2 sm:py-0">Abonos</a>
            <a href="resumen.php" class="text-white hover:underline block py-2 sm:py-0">Resumen</a>
        </div>
    </nav>
    <script>
        // Navbar hamburguesa
        const navToggle = document.getElementById('nav-toggle');
        const navMenu = document.getElementById('nav-menu');
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('hidden');
        });
    </script>
    <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-xl p-8 mt-8 mb-8">
        <header class="mb-10">
            <h1 class="text-4xl font-bold text-center text-blue-700 flex items-center justify-center gap-3">
                <svg class="w-8 h-8 inline-block text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle>
                </svg>
                Gestión de Gastos
            </h1>
            <p class="text-center text-gray-500 mt-2">Visualiza y controla tus gastos de manera ordenada y profesional.</p>
        </header>

        <!-- Filtro -->
        <form action="index.php" method="GET" class="mb-8">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="fecha_inicio" class="block font-medium mb-1 text-blue-800">Fecha de inicio</label>
                    <input type="date" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-400 shadow-sm" id="fecha_inicio" name="fecha_inicio" value="<?= $fechaInicio ?>">
                </div>
                <div class="flex-1">
                    <label for="fecha_fin" class="block font-medium mb-1 text-blue-800">Fecha de fin</label>
                    <input type="date" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-400 shadow-sm" id="fecha_fin" name="fecha_fin" value="<?= $fechaFin ?>">
                </div>
                <div>
                    <button type="submit" class="px-5 py-2 rounded bg-blue-700 text-white font-semibold hover:bg-blue-800 transition">Buscar</button>
                </div>
            </div>
        </form>

        <!-- Graficas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-6 shadow-sm">
                <div class="text-lg font-semibold text-blue-700 mb-2 flex items-center gap-2">Gastos por Método de Pago</div>
                <canvas id="graficoMetodo"></canvas>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-6 shadow-sm">
                <div class="text-lg font-semibold text-blue-700 mb-2 flex items-center gap-2">Gastos por Categoría</div>
                <canvas id="graficoTipos"></canvas>
            </div>
        </div>

        <!-- Collapse Categorías -->
        <div x-data="{ openCollapse: null }">
            <div class="flex flex-wrap gap-2 mb-6">
                <button @click="openCollapse = (openCollapse === 'fijo' ? null : 'fijo')"
                    :class="openCollapse === 'fijo' ? 'bg-blue-700 text-white' : 'border border-blue-700 text-blue-700'"
                    class="px-4 py-2 rounded transition font-semibold">Gasto Fijo</button>
                <button @click="openCollapse = (openCollapse === 'central' ? null : 'central')"
                    :class="openCollapse === 'central' ? 'bg-blue-700 text-white' : 'border border-blue-700 text-blue-700'"
                    class="px-4 py-2 rounded transition font-semibold">Central de Abasto</button>
                <button @click="openCollapse = (openCollapse === 'sitio' ? null : 'sitio')"
                    :class="openCollapse === 'sitio' ? 'bg-blue-700 text-white' : 'border border-blue-700 text-blue-700'"
                    class="px-4 py-2 rounded transition font-semibold">Mercado</button>
                <button @click="openCollapse = (openCollapse === 'mantenimiento' ? null : 'mantenimiento')"
                    :class="openCollapse === 'mantenimiento' ? 'bg-blue-700 text-white' : 'border border-blue-700 text-blue-700'"
                    class="px-4 py-2 rounded transition font-semibold">Mantenimiento</button>
                <button @click="openCollapse = (openCollapse === 'inversiones' ? null : 'inversiones')"
                    :class="openCollapse === 'inversiones' ? 'bg-blue-700 text-white' : 'border border-blue-700 text-blue-700'"
                    class="px-4 py-2 rounded transition font-semibold">Inversiones</button>
            </div>

            <!-- Collapse Gastos Fijos -->
            <div x-show="openCollapse === 'fijo'" x-transition class="mb-4 bg-white border border-blue-100 rounded-lg p-4">
                <?php
                $lista = $gastosTotalesFijos;
                $total = $totalGastosFijos;
                ?>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-semibold text-blue-700">Gastos Fijos</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($total, 2) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border rounded-lg">
                        <thead class="bg-blue-50 text-blue-800">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Método</th>
                                <th class="px-3 py-2">Monto</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                        <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                        <td class="px-3 py-2 flex gap-1">
                                            <button class="inline-block px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition text-xs edit-gasto-btn"
                                                data-id="<?= $gasto['ID'] ?>"
                                                data-descripcion="<?= htmlspecialchars($gasto['Descripcion'], ENT_QUOTES) ?>"
                                                data-monto="<?= $gasto['Monto'] ?>"
                                                data-fecha="<?= $gasto['Fecha'] ?>"
                                                data-metodo="<?= htmlspecialchars($gasto['Metodo'], ENT_QUOTES) ?>"
                                                data-tipo="<?= htmlspecialchars($gasto['Tipo'], ENT_QUOTES) ?>"
                                            >Editar</button>
                                            <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Collapse Central de Abasto -->
            <div x-show="openCollapse === 'central'" x-transition class="mb-4 bg-white border border-blue-100 rounded-lg p-4">
                <?php
                $lista = $gastosTotalesCentral;
                $total = $totalGastosCentral;
                ?>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-semibold text-blue-700">Central de Abasto</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($total, 2) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border rounded-lg">
                        <thead class="bg-blue-50 text-blue-800">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Método</th>
                                <th class="px-3 py-2">Monto</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                        <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                        <td class="px-3 py-2 flex gap-1">
                                            <button class="inline-block px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition text-xs edit-gasto-btn"
                                                data-id="<?= $gasto['ID'] ?>"
                                                data-descripcion="<?= htmlspecialchars($gasto['Descripcion'], ENT_QUOTES) ?>"
                                                data-monto="<?= $gasto['Monto'] ?>"
                                                data-fecha="<?= $gasto['Fecha'] ?>"
                                                data-metodo="<?= htmlspecialchars($gasto['Metodo'], ENT_QUOTES) ?>"
                                                data-tipo="<?= htmlspecialchars($gasto['Tipo'], ENT_QUOTES) ?>"
                                            >Editar</button>
                                            <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Collapse Mercado -->
            <div x-show="openCollapse === 'sitio'" x-transition class="mb-4 bg-white border border-blue-100 rounded-lg p-4">
                <?php
                $lista = $gastosTotalesSitio;
                $total = $totalGastosSitio;
                ?>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-semibold text-blue-700">Mercado</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($total, 2) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border rounded-lg">
                        <thead class="bg-blue-50 text-blue-800">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Método</th>
                                <th class="px-3 py-2">Monto</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                        <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                        <td class="px-3 py-2 flex gap-1">
                                            <button class="inline-block px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition text-xs edit-gasto-btn"
                                                data-id="<?= $gasto['ID'] ?>"
                                                data-descripcion="<?= htmlspecialchars($gasto['Descripcion'], ENT_QUOTES) ?>"
                                                data-monto="<?= $gasto['Monto'] ?>"
                                                data-fecha="<?= $gasto['Fecha'] ?>"
                                                data-metodo="<?= htmlspecialchars($gasto['Metodo'], ENT_QUOTES) ?>"
                                                data-tipo="<?= htmlspecialchars($gasto['Tipo'], ENT_QUOTES) ?>"
                                            >Editar</button>
                                            <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Collapse Mantenimiento -->
            <div x-show="openCollapse === 'mantenimiento'" x-transition class="mb-4 bg-white border border-blue-100 rounded-lg p-4">
                <?php
                $lista = $gastosTotalesMantenimiento;
                $total = $totalGastosMantenimiento;
                ?>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-semibold text-blue-700">Mantenimiento</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($total, 2) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border rounded-lg">
                        <thead class="bg-blue-50 text-blue-800">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Método</th>
                                <th class="px-3 py-2">Monto</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                        <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                        <td class="px-3 py-2 flex gap-1">
                                            <button class="inline-block px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition text-xs edit-gasto-btn"
                                                data-id="<?= $gasto['ID'] ?>"
                                                data-descripcion="<?= htmlspecialchars($gasto['Descripcion'], ENT_QUOTES) ?>"
                                                data-monto="<?= $gasto['Monto'] ?>"
                                                data-fecha="<?= $gasto['Fecha'] ?>"
                                                data-metodo="<?= htmlspecialchars($gasto['Metodo'], ENT_QUOTES) ?>"
                                                data-tipo="<?= htmlspecialchars($gasto['Tipo'], ENT_QUOTES) ?>"
                                            >Editar</button>
                                            <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Collapse Inversiones -->
            <div x-show="openCollapse === 'inversiones'" x-transition class="mb-4 bg-white border border-blue-100 rounded-lg p-4">
                <?php
                $lista = $gastosTotalesInversiones;
                $total = $totalGastosInversiones;
                ?>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-lg font-semibold text-blue-700">Inversiones</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($total, 2) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border rounded-lg">
                        <thead class="bg-blue-50 text-blue-800">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Método</th>
                                <th class="px-3 py-2">Monto</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (count($lista) > 0): foreach ($lista as $gasto): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                        <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                        <td class="px-3 py-2 flex gap-1">
                                            <button class="inline-block px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition text-xs edit-gasto-btn"
                                                data-id="<?= $gasto['ID'] ?>"
                                                data-descripcion="<?= htmlspecialchars($gasto['Descripcion'], ENT_QUOTES) ?>"
                                                data-monto="<?= $gasto['Monto'] ?>"
                                                data-fecha="<?= $gasto['Fecha'] ?>"
                                                data-metodo="<?= htmlspecialchars($gasto['Metodo'], ENT_QUOTES) ?>"
                                                data-tipo="<?= htmlspecialchars($gasto['Tipo'], ENT_QUOTES) ?>"
                                            >Editar</button>
                                            <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="text-xl font-bold text-blue-800 mb-3 flex items-center gap-2">Resumen General</div>
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-blue-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-blue-700">Gastos con Tarjeta</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($totalGastos, 2) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border rounded-lg">
                        <thead class="bg-blue-50 text-blue-800">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Método</th>
                                <th class="px-3 py-2">Monto</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (count($gastos) > 0): foreach ($gastos as $gasto): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                        <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                        <td class="px-3 py-2">
                                            <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white border border-blue-100 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-blue-700">Gastos en Efectivo</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($totalGastosEfectivo, 2) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border rounded-lg">
                        <thead class="bg-blue-50 text-blue-800">
                            <tr>
                                <th class="px-3 py-2">Descripción</th>
                                <th class="px-3 py-2">Método</th>
                                <th class="px-3 py-2">Monto</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php if (count($gastosEfectivo) > 0): foreach ($gastosEfectivo as $gasto): ?>
                                    <tr class="border-b last:border-0">
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                        <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                        <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                        <td class="px-3 py-2">
                                            <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="bg-white border border-blue-100 rounded-lg p-4 mb-8">
            <div class="flex items-center justify-between mb-3">
                <span class="font-semibold text-blue-700">Gastos Totales</span>
                <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold text-base">Total: $<?= number_format($totalGastosAll, 2) ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border rounded-lg">
                    <thead class="bg-blue-50 text-blue-800">
                        <tr>
                            <th class="px-3 py-2">Descripción</th>
                            <th class="px-3 py-2">Método</th>
                            <th class="px-3 py-2">Monto</th>
                            <th class="px-3 py-2">Fecha</th>
                            <th class="px-3 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php if (count($gastosTotales) > 0): foreach ($gastosTotales as $gasto): ?>
                                <tr class="border-b last:border-0">
                                    <td class="px-3 py-2"><?= htmlspecialchars($gasto['Descripcion']) ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($gasto['Metodo']) ?></td>
                                    <td class="px-3 py-2 text-right">$<?= number_format($gasto['Monto'], 2) ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($gasto['Fecha']) ?></td>
                                    <td class="px-3 py-2 flex gap-1">
                                        <button class="inline-block px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition text-xs edit-gasto-btn"
                                            data-id="<?= $gasto['ID'] ?>"
                                            data-descripcion="<?= htmlspecialchars($gasto['Descripcion'], ENT_QUOTES) ?>"
                                            data-monto="<?= $gasto['Monto'] ?>"
                                            data-fecha="<?= $gasto['Fecha'] ?>"
                                            data-metodo="<?= htmlspecialchars($gasto['Metodo'], ENT_QUOTES) ?>"
                                            data-tipo="<?= htmlspecialchars($gasto['Tipo'], ENT_QUOTES) ?>"
                                        >Editar</button>
                                        <a href="deleteExpenses.php?id=<?= $gasto['ID'] ?>" class="inline-block px-2 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition text-xs">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-gray-400 py-3">No hay gastos registrados en este rango de fechas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex justify-end">
            <a href="addExpenses.php" class="inline-block px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition font-semibold">Agregar Nuevo Gasto</a>
        </div>
        <footer class="mt-10 text-center text-gray-400 text-sm">
            &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
        </footer>

        <!-- Modal Editar Gasto -->
        <div id="modalEditGasto" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden px-2 py-4 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md sm:max-w-lg md:max-w-xl p-4 sm:p-6 md:p-8 relative mx-auto overflow-y-auto" style="max-height:95vh;">
                <h3 class="text-xl font-bold mb-4 text-indigo-700">Editar Gasto</h3>
                <form method="POST" id="editGastoForm">
                    <input type="hidden" name="edit_gasto_id" id="edit_gasto_id">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1">Descripción</label>
                        <input type="text" name="edit_gasto_descripcion" id="edit_gasto_descripcion" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1">Monto</label>
                        <input type="number" step="0.01" name="edit_gasto_monto" id="edit_gasto_monto" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1">Fecha</label>
                        <input type="date" name="edit_gasto_fecha" id="edit_gasto_fecha" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1">Método de Pago</label>
                        <select name="edit_gasto_metodo" id="edit_gasto_metodo" class="w-full border rounded px-3 py-2" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1">Tipo de Gasto</label>
                        <select name="edit_gasto_tipo" id="edit_gasto_tipo" class="w-full border rounded px-3 py-2" required>
                            <option value="General">General</option>
                            <option value="Fijo">Fijo</option>
                            <option value="Central">Central</option>
                            <option value="Mercado">Mercado</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Inversiones">Inversiones</option>
                        </select>
                    </div>
                    <div class="flex flex-col sm:flex-row justify-end gap-2">
                        <button type="button" id="closeEditGastoModal" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 w-full sm:w-auto">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded bg-indigo-700 text-white hover:bg-indigo-800 w-full sm:w-auto">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        // Abrir modal y cargar datos
        document.querySelectorAll('.edit-gasto-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_gasto_id').value = this.dataset.id;
                document.getElementById('edit_gasto_descripcion').value = this.dataset.descripcion;
                document.getElementById('edit_gasto_monto').value = this.dataset.monto;
                document.getElementById('edit_gasto_fecha').value = this.dataset.fecha;
                document.getElementById('edit_gasto_metodo').value = this.dataset.metodo;
                document.getElementById('edit_gasto_tipo').value = this.dataset.tipo;
                document.getElementById('modalEditGasto').classList.remove('hidden');
            });
        });
        document.getElementById('closeEditGastoModal').onclick = function() {
            document.getElementById('modalEditGasto').classList.add('hidden');
        };
        </script>
    </div>
    <script>
        const ctxMetodo = document.getElementById('graficoMetodo').getContext('2d');
        new Chart(ctxMetodo, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labelsMetodo) ?>,
                datasets: [{
                    data: <?= json_encode($dataMetodo) ?>,
                    backgroundColor: ["#2563eb", "#059669"],
                    borderColor: "#fff",
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
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
                        "#2563eb", "#059669", "#ea580c", "#facc15", "#7c3aed"
                    ],
                    borderRadius: 7,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>