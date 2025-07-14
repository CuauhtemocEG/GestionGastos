<?php
include 'config.php';
$totalGastos = $conexion->query("SELECT SUM(Monto) as total FROM Gastos")->fetch_assoc()['total'] ?? 0;
$totalPagos  = $conexion->query("SELECT SUM(monto) as total FROM pagos")->fetch_assoc()['total'] ?? 0;
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

<nav class="bg-indigo-700 rounded-b-2xl px-8 py-4 flex items-center justify-between shadow-lg relative z-10">
    <div class="flex items-center gap-8">
        <span class="text-2xl font-bold text-white flex items-center gap-2">
            <svg class="w-8 h-8 inline-block text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 8v4l3 3"></path>
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle>
            </svg>
            GastosApp
        </span>
        <a href="index.php" class="text-white hover:underline">Inicio</a>
        <a href="addExpenses.php" class="text-white hover:underline">Agregar Gasto</a>
        <a href="pagos.php" class="text-white hover:underline">Abonos</a>
        <a href="resumen.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition">Resumen</a>
    </div>
    <div class="flex items-center gap-4">
        <input type="text" placeholder="Buscar" class="rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400 w-56">
        <img src="https://ui-avatars.com/api/?name=Usuario&background=4f46e5&color=fff" class="rounded-full w-9 h-9 shadow border-2 border-white" alt="Avatar">
    </div>
</nav>

<div class="bg-indigo-700 pt-8 pb-24 px-8 rounded-b-3xl shadow-xl -mt-1 relative z-0">
    <h2 class="text-white text-3xl font-bold mb-3">Resumen</h2>
</div>

<main class="-mt-16 px-8">
    <div class="bg-white rounded-xl shadow-lg p-8 min-h-[350px] mb-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-indigo-50 p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold text-indigo-800 mb-3">Total de Gastos</h3>
                <p class="text-2xl font-bold text-red-600">$<?= number_format($totalGastos,2) ?></p>
            </div>
            <div class="bg-green-50 p-6 rounded-lg shadow">
                <h3 class="text-xl font-bold text-green-800 mb-3">Total de Abonos</h3>
                <p class="text-2xl font-bold text-green-600">$<?= number_format($totalPagos,2) ?></p>
            </div>
            <div class="bg-blue-50 p-6 rounded-lg shadow">
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