<?php
include 'config.php';
$pagos = $conexion->query("SELECT * FROM Pagos ORDER BY fecha DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Abonos | Gestión de Gastos</title>
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
        <a href="pagos.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition">Abonos</a>
        <a href="resumen.php" class="text-white hover:underline">Resumen</a>
    </div>
</nav>

<div class="bg-indigo-500 pt-8 pb-10 px-8 rounded-b-3xl shadow-xl -mt-1 relative z-0">
    <h2 class="text-white text-3xl font-bold mb-3">Abonos/Pagos</h2>
</div>

<main class="-mt-0 px-8">
    <div class="bg-white rounded-xl shadow-lg p-20 min-h-[350px] mb-10">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold text-indigo-700">Lista de Abonos</h3>
            <a href="addPago.php" class="bg-indigo-700 px-5 py-2 rounded text-white hover:bg-indigo-800 transition font-semibold">Agregar Abono</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border rounded-lg">
                <thead class="bg-indigo-50 text-indigo-800">
                    <tr>
                        <th class="px-3 py-2">Descripción</th>
                        <th class="px-3 py-2">Monto</th>
                        <th class="px-3 py-2">Método de pago</th>
                        <th class="px-3 py-2">Fecha</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                <?php if (count($pagos) > 0): foreach ($pagos as $pago): ?>
                    <tr class="border-b last:border-0">
                        <td class="px-3 py-2"><?= htmlspecialchars($pago['descripcion']) ?></td>
                        <td class="px-3 py-2">$<?= number_format($pago['monto'],2) ?></td>
                        <td class="px-3 py-2"><?= htmlspecialchars($pago['Metodo']) ?></td>
                        <td class="px-3 py-2"><?= htmlspecialchars($pago['fecha']) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-gray-400 py-3">No hay abonos registrados.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<footer class="mt-12 text-center text-gray-400 text-sm">
    &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
</footer>
</body>
</html>