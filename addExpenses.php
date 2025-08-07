<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];
    $method = $_POST['typeExpense'];
    $tipoGasto = $_POST['tipoGasto'];

    $sql = "INSERT INTO Gastos (Descripcion, Monto, Fecha, Metodo, Tipo) VALUES ('$descripcion', '$monto', '$fecha', '$method', '$tipoGasto')";

    if ($conexion->query($sql) === TRUE) {
        header('Location: index.php');
    } else {
        echo "Error: " . $conexion->error;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Gasto | Gestión de Gastos</title>
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
        <a href="addExpenses.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition block sm:inline-block">Agregar Gasto</a>
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

<div class="bg-indigo-500 pt-8 pb-10 px-8 rounded-b-3xl shadow-xl -mt-1 relative z-0">
    <h2 class="text-white text-3xl font-bold mb-3">Agregar Gasto</h2>
</div>

<main class="-mt-0 px-2 sm:px-8">
</style>
  /* Tablas responsivas */
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
</style>
    <div class="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 mb-10 w-full">
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
                <label class="block font-medium text-gray-700 mb-1">Fecha</label>
                <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Método de Pago</label>
                <select name="typeExpense" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Tipo de Gasto</label>
                <select name="tipoGasto" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
                    <option value="General">General</option>
                    <option value="Fijo">Fijo</option>
                    <option value="Central">Central</option>
                    <option value="Mercado">Mercado</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Inversiones">Inversiones</option>
                </select>
            </div>
            <div class="flex flex-col sm:flex-row justify-between gap-2">
                <a href="index.php" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-800 font-semibold w-full sm:w-auto text-center">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-indigo-700 text-white rounded hover:bg-indigo-800 font-semibold w-full sm:w-auto">Agregar Gasto</button>
            </div>
        </form>
    </div>
</main>
<footer class="mt-12 text-center text-gray-400 text-sm">
    &copy; <?= date('Y') ?> Gestión de Gastos · Desarrollado por CuauhtemocEG
</footer>
</body>
</html>