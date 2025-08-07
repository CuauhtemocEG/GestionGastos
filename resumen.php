<?php
include 'config.php';
$totalGastos = $conexion->query("SELECT SUM(Monto) as total FROM Gastos")->fetch_assoc()['total'] ?? 0;
$totalPagos  = $conexion->query("SELECT SUM(monto) as total FROM Pagos")->fetch_assoc()['total'] ?? 0;
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
        <button id="nav-toggle" class="sm:hidden text-white focus:outline-none" aria-label="Abrir menú">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    <div id="nav-menu" class="flex-col sm:flex-row sm:flex items-center gap-6 sm:gap-8 mt-4 sm:mt-0 hidden sm:flex">
        <a href="index.php" class="text-white hover:underline block py-2 sm:py-0">Inicio</a>
        <a href="addExpenses.php" class="text-white hover:underline block py-2 sm:py-0">Agregar Gasto</a>
        <a href="pagos.php" class="text-white hover:underline block py-2 sm:py-0">Abonos</a>
        <a href="resumen.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition block sm:inline-block">Resumen</a>
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
</style>
  /* Tablas responsivas */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
</style>

<div class="bg-indigo-500 pt-8 pb-10 px-8 rounded-b-3xl shadow-xl -mt-1 relative z-0">
    <h2 class="text-white text-3xl font-bold mb-3">Resumen</h2>
</div>

<main class="-mt-0 px-2 sm:px-4 md:px-8">
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 min-h-[350px] mb-10 w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
            <div class="bg-indigo-50 p-4 sm:p-6 rounded-lg shadow w-full">
                <h3 class="text-xl font-bold text-indigo-800 mb-3">Total de Gastos</h3>
                <p class="text-2xl font-bold text-red-600">$<?= number_format($totalGastos,2) ?></p>
            </div>
            <div class="bg-green-50 p-4 sm:p-6 rounded-lg shadow w-full">
                <h3 class="text-xl font-bold text-green-800 mb-3">Total de Abonos</h3>
                <p class="text-2xl font-bold text-green-600">$<?= number_format($totalPagos,2) ?></p>
            </div>
            <div class="bg-blue-50 p-4 sm:p-6 rounded-lg shadow w-full">
                <h3 class="text-xl font-bold text-blue-800 mb-3">Saldo</h3>
                <p class="text-2xl font-bold <?= $saldo >= 0 ? 'text-green-700' : 'text-red-700' ?>">
                    $<?= number_format($saldo,2) ?>
                </p>
            </div>
        </div>
    </div>
</main>
<footer class="mt-12 text-center text-gray-400 text-sm">
    &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
</footer>
</body>
</html>