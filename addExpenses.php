<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion']);
    $monto = floatval($_POST['monto']);
    $fecha = $_POST['fecha'];
    $method = $_POST['typeExpense'];
    $tipoGasto = $_POST['tipoGasto'];

    // Validaciones
    if (empty($descripcion) || $monto <= 0 || empty($fecha) || empty($method) || empty($tipoGasto)) {
        $error = "Todos los campos son obligatorios y el monto debe ser mayor a 0";
    } else {
        // Usar prepared statement para seguridad
        $stmt = $conexion->prepare("INSERT INTO Gastos (Descripcion, Monto, Fecha, Metodo, Tipo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sdsss', $descripcion, $monto, $fecha, $method, $tipoGasto);
        
        if ($stmt->execute()) {
            $success = "Gasto agregado exitosamente";
            // Limpiar formulario
            $descripcion = $monto = $fecha = $method = $tipoGasto = '';
        } else {
            $error = "Error al agregar el gasto: " . $conexion->error;
        }
        $stmt->close();
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
        <a href="addExpenses.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition block sm:inline-block">Agregar Gasto</a>
        <a href="pagos.php" class="text-white hover:underline block py-2 sm:py-0">Abonos</a>
        <a href="resumen-mejorado.php" class="text-white hover:underline block py-2 sm:py-0">Resumen</a>
    </div>
</nav>
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
    <div class="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-4 sm:p-6 md:p-8 mb-10 w-full">
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Descripción</label>
                <input type="text" name="descripcion" required 
                       value="<?= htmlspecialchars($descripcion ?? '') ?>"
                       class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400" 
                       placeholder="Ej: Gasolina, Comida, etc.">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Monto</label>
                <input type="number" step="0.01" min="0.01" name="monto" required 
                       value="<?= htmlspecialchars($monto ?? '') ?>"
                       class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400" 
                       placeholder="0.00">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Fecha</label>
                <input type="date" name="fecha" required 
                       value="<?= htmlspecialchars($fecha ?? date('Y-m-d')) ?>" 
                       class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Método de Pago</label>
                <select name="typeExpense" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
                    <option value="">Selecciona un método</option>
                    <option value="Efectivo" <?= ($method ?? '') === 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                    <option value="Tarjeta" <?= ($method ?? '') === 'Tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Tipo de Gasto</label>
                <select name="tipoGasto" required class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400">
                    <option value="">Selecciona un tipo</option>
                    <option value="Fijo" <?= ($tipoGasto ?? '') === 'Fijo' ? 'selected' : '' ?>>Fijo</option>
                    <option value="Central" <?= ($tipoGasto ?? '') === 'Central' ? 'selected' : '' ?>>Central</option>
                    <option value="Mercado" <?= ($tipoGasto ?? '') === 'Mercado' ? 'selected' : '' ?>>Mercado</option>
                    <option value="Mantenimiento" <?= ($tipoGasto ?? '') === 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                    <option value="Inversiones" <?= ($tipoGasto ?? '') === 'Inversiones' ? 'selected' : '' ?>>Inversiones</option>
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