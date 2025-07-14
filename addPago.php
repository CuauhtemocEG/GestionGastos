<?php
include 'config.php';

// Procesar el formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'] ?? '';
    $metodo = $_POST['metodo'] ?? 'Efectivo';
    $monto = $_POST['monto'] ?? 0;
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    $stmt = $conexion->prepare("INSERT INTO Pagos (descripcion, monto, Metodo, fecha) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sds", $descripcion, $monto, $metodo, $fecha);
    $stmt->execute();

    header("Location: pagos.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Abono | Gestión de Gastos</title>
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
    <h2 class="text-white text-3xl font-bold mb-3">Agregar Abono</h2>
</div>

<main class="-mt-0 px-8">
    <div class="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-8 mb-10">
        <form method="POST" class="space-y-6">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Descripción</label>
                <input type="text" name="descripcion" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Monto</label>
                <input type="number" step="0.01" name="monto" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Método de Pago</label>
                <select name="metodo" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Fecha</label>
                <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="flex justify-between">
                <a href="pagos.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-800 font-semibold">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-indigo-700 text-white rounded hover:bg-indigo-800 font-semibold">Agregar Abono</button>
            </div>
        </form>
    </div>
</main>
<footer class="mt-12 text-center text-gray-400 text-sm">
    &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
</footer>
</body>
</html>