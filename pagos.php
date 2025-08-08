<?php
include 'config.php';
$pagos = $conexion->query("SELECT * FROM Pagos ORDER BY fecha DESC")->fetch_all(MYSQLI_ASSOC);
// Manejo de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_id'])) {
        $id = intval($_POST['edit_id']);
        $descripcion = $_POST['edit_descripcion'];
        $monto = floatval($_POST['edit_monto']);
        $metodo = $_POST['edit_metodo'];
        $fecha = $_POST['edit_fecha'];
        $stmt = $conexion->prepare("UPDATE Pagos SET descripcion=?, monto=?, Metodo=?, fecha=? WHERE id= ?");
        $stmt->bind_param('sdssi', $descripcion, $monto, $metodo, $fecha, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: pagos.php');
        exit;
    }
    // Manejo de eliminación
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        $stmt = $conexion->prepare("DELETE FROM Pagos WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: pagos.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Abonos | Gestión de Gastos</title>
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
            <button id="nav-toggle" class="sm:hidden text-white focus:outline-none" aria-label="Abrir menú">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        <div id="nav-menu" class="flex-col sm:flex-row sm:flex items-center gap-6 sm:gap-8 mt-4 sm:mt-0 hidden sm:flex">
            <a href="index.php" class="text-white hover:underline block py-2 sm:py-0">Inicio</a>
            <a href="addExpenses.php" class="text-white hover:underline block py-2 sm:py-0">Agregar Gasto</a>
            <a href="pagos.php" class="px-6 py-2 rounded-lg bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-100 focus:bg-indigo-100 transition block sm:inline-block">Abonos</a>
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
                            <th class="px-3 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php if (count($pagos) > 0): foreach ($pagos as $pago): ?>
                                <tr class="border-b last:border-0">
                                    <td class="px-3 py-2"><?= htmlspecialchars($pago['descripcion']) ?></td>
                                    <td class="px-3 py-2">$<?= number_format($pago['monto'], 2) ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($pago['Metodo']) ?></td>
                                    <td class="px-3 py-2"><?= htmlspecialchars($pago['fecha']) ?></td>
                                    <td class="px-3 py-2 flex gap-2">
                                        <button class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded edit-btn"
                                            data-id="<?= $pago['id'] ?>"
                                            data-descripcion="<?= htmlspecialchars($pago['descripcion'], ENT_QUOTES) ?>"
                                            data-monto="<?= $pago['monto'] ?>"
                                            data-metodo="<?= htmlspecialchars($pago['Metodo'], ENT_QUOTES) ?>"
                                            data-fecha="<?= $pago['fecha'] ?>">
                                            Editar
                                        </button>
                                        <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded delete-btn"
                                            data-id="<?= $pago['id'] ?>">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-gray-400 py-3">No hay abonos registrados.</td>
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

<!-- Modal Editar -->
<div id="modalEdit" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden px-2 py-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md sm:max-w-lg md:max-w-xl p-4 sm:p-6 md:p-8 relative mx-auto overflow-y-auto" style="max-height:95vh;">
        <h3 class="text-xl font-bold mb-4 text-indigo-700">Editar Abono</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Descripción</label>
                <input type="text" name="edit_descripcion" id="edit_descripcion" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Monto</label>
                <input type="number" step="0.01" name="edit_monto" id="edit_monto" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Método de pago</label>
                <select name="edit_metodo" id="edit_metodo" class="w-full border rounded px-3 py-2" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Fecha</label>
                <input type="date" name="edit_fecha" id="edit_fecha" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" id="closeEditModal" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 w-full sm:w-auto">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded bg-indigo-700 text-white hover:bg-indigo-800 w-full sm:w-auto">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="modalDelete" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden px-2 py-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md sm:max-w-lg md:max-w-xl p-4 sm:p-6 md:p-8 relative mx-auto overflow-y-auto" style="max-height:95vh;">
        <h3 class="text-xl font-bold mb-4 text-red-600">¿Eliminar abono?</h3>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="delete_id" id="delete_id">
            <p class="mb-6">¿Estás seguro de que deseas eliminar este abono? Esta acción no se puede deshacer.</p>
            <div class="flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" id="closeDeleteModal" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 w-full sm:w-auto">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600 w-full sm:w-auto">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Editar
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_descripcion').value = this.dataset.descripcion;
            document.getElementById('edit_monto').value = this.dataset.monto;
            document.getElementById('edit_metodo').value = this.dataset.metodo;
            document.getElementById('edit_fecha').value = this.dataset.fecha;
            document.getElementById('modalEdit').classList.remove('hidden');
        });
    });
    document.getElementById('closeEditModal').onclick = function() {
        document.getElementById('modalEdit').classList.add('hidden');
    };

    // Editar
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_descripcion').value = this.dataset.descripcion;
            document.getElementById('edit_monto').value = this.dataset.monto;
            // Seleccionar la opción correcta en el select
            const metodoSelect = document.getElementById('edit_metodo');
            metodoSelect.value = this.dataset.metodo;
            document.getElementById('edit_fecha').value = this.dataset.fecha;
            document.getElementById('modalEdit').classList.remove('hidden');
        });
    });

    // Eliminar
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('modalDelete').classList.remove('hidden');
        });
    });
    document.getElementById('closeDeleteModal').onclick = function() {
        document.getElementById('modalDelete').classList.add('hidden');
    };
</script>
</body>

</html>