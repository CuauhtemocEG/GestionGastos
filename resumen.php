<?php
include 'config.php';

// Obtener filtros
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Usar prepared statements para seguridad
$stmtGastos = $conexion->prepare("SELECT SUM(Monto) as total FROM Gastos WHERE Fecha BETWEEN ? AND ?");
$stmtGastos->bind_param('ss', $fechaInicio, $fechaFin);
$stmtGastos->execute();
$totalGastos = $stmtGastos->get_result()->fetch_assoc()['total'] ?? 0;

$stmtPagos = $conexion->prepare("SELECT SUM(monto) as total FROM Pagos WHERE fecha BETWEEN ? AND ?");
$stmtPagos->bind_param('ss', $fechaInicio, $fechaFin);
$stmtPagos->execute();
$totalPagos = $stmtPagos->get_result()->fetch_assoc()['total'] ?? 0;

$saldo = $totalPagos - $totalGastos;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resumen | Gestión de Gastos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    /* Tablas responsivas */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>

<body class="bg-gray-100 min-h-screen">


    <nav class="bg-indigo-700 rounded-b-2xl px-4 sm:px-8 py-4 shadow-lg relative z-10">
        <div class="flex items-center justify-between">
            <span class="text-2xl font-bold text-white flex items-center gap-2">
                <svg class="w-8 h-8 inline-block text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle>
                </svg>
                GastosApp
            </span>
            <div class="flex items-center gap-4">
                <span class="text-white hidden sm:block">Hola, <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
                <a href="?logout=1" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition text-sm">
                    Cerrar Sesión
                </a>
                <button id="nav-toggle" class="sm:hidden text-white focus:outline-none" aria-label="Abrir menú">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        <div id="nav-menu" class="flex-col sm:flex-row sm:flex items-center gap-6 sm:gap-8 mt-4 sm:mt-0 hidden sm:flex">
            <a href="index.php" class="text-white hover:underline block py-2 sm:py-0">Inicio</a>
            <a href="dashboard.php" class="text-white hover:underline block py-2 sm:py-0">Dashboard Avanzado</a>
            <a href="addExpenses.php" class="text-white hover:underline block py-2 sm:py-0">Agregar Gasto</a>
            <a href="pagos.php" class="text-white hover:underline block py-2 sm:py-0">Abonos</a>
            <a href="resumen.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition block sm:inline-block">Resumen Simple</a>
            <a href="resumen-mejorado.php" class="text-white hover:underline block py-2 sm:py-0">Resumen Avanzado</a>
        </div>
    </nav>
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
    <div class="bg-indigo-500 pt-8 pb-10 px-8 rounded-b-3xl shadow-xl -mt-1 relative z-0">
        <h2 class="text-white text-3xl font-bold mb-3">Resumen</h2>
    </div>

    <main class="-mt-0 px-2 sm:px-4 md:px-8">
        <!-- Filtros de Fecha -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-6">
            <h3 class="text-xl font-semibold text-indigo-700 mb-4">Filtrar por Período</h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="fecha_inicio" class="block font-medium mb-1 text-indigo-800">Fecha de inicio</label>
                    <input type="date" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-400 shadow-sm" 
                           id="fecha_inicio" name="fecha_inicio" value="<?= $fechaInicio ?>">
                </div>
                <div>
                    <label for="fecha_fin" class="block font-medium mb-1 text-indigo-800">Fecha de fin</label>
                    <input type="date" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-400 shadow-sm" 
                           id="fecha_fin" name="fecha_fin" value="<?= $fechaFin ?>">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2 rounded bg-indigo-700 text-white font-semibold hover:bg-indigo-800 transition">
                        Actualizar
                    </button>
                    <a href="resumen.php" class="px-5 py-2 rounded bg-gray-500 text-white font-semibold hover:bg-gray-600 transition">
                        Limpiar
                    </a>
                </div>
            </form>
            <p class="text-sm text-gray-600 mt-2">
                Período actual: <?= date('d/m/Y', strtotime($fechaInicio)) ?> al <?= date('d/m/Y', strtotime($fechaFin)) ?>
            </p>
        </div>
        
        <!-- Resumen Principal -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 min-h-[350px] mb-10 w-full">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
                <div class="bg-indigo-50 p-4 sm:p-6 rounded-lg shadow w-full">
                    <h3 class="text-xl font-bold text-indigo-800 mb-3">Total de Gastos</h3>
                    <p class="text-2xl font-bold text-red-600">$<?= number_format($totalGastos, 2) ?></p>
                </div>
                <div class="bg-green-50 p-4 sm:p-6 rounded-lg shadow w-full">
                    <h3 class="text-xl font-bold text-green-800 mb-3">Total de Abonos</h3>
                    <p class="text-2xl font-bold text-green-600">$<?= number_format($totalPagos, 2) ?></p>
                </div>
                <div class="bg-blue-50 p-4 sm:p-6 rounded-lg shadow w-full">
                    <h3 class="text-xl font-bold text-blue-800 mb-3">Saldo</h3>
                    <p class="text-2xl font-bold <?= $saldo >= 0 ? 'text-green-700' : 'text-red-700' ?>">
                        $<?= number_format($saldo, 2) ?>
                    </p>
                </div>
            </div>
            
            <!-- Botón para Resumen Avanzado -->
            <div class="mt-8 text-center">
                <a href="resumen-mejorado.php?fecha_inicio=<?= urlencode($fechaInicio) ?>&fecha_fin=<?= urlencode($fechaFin) ?>" 
                   class="inline-block px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow hover:from-indigo-700 hover:to-purple-700 transition">
                    Ver Resumen Avanzado con Gráficos
                </a>
            </div>
        </div>
    </main>
    <footer class="mt-12 text-center text-gray-400 text-sm">
        &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
    </footer>
</body>

</html>