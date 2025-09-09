<?php
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion']);
    $monto = floatval($_POST['monto']);
    $fecha = $_POST['fecha'];
    $metodo = $_POST['metodo'];
    
    if (!empty($descripcion) && $monto > 0 && !empty($fecha) && !empty($metodo)) {
        $stmt = $conexion->prepare("INSERT INTO Pagos (descripcion, monto, fecha, Metodo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sdss', $descripcion, $monto, $fecha, $metodo);
        
        if ($stmt->execute()) {
            $mensaje = 'Pago agregado exitosamente';
            $tipo_mensaje = 'success';
            // Limpiar formulario
            $descripcion = $monto = $fecha = $metodo = '';
        } else {
            $mensaje = 'Error al agregar el pago: ' . $conexion->error;
            $tipo_mensaje = 'error';
        }
        $stmt->close();
    } else {
        $mensaje = 'Por favor, complete todos los campos correctamente';
        $tipo_mensaje = 'error';
    }
}
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-plus-circle mr-2 text-green-600"></i>
            Agregar Nuevo Pago
        </h1>
        <p class="text-gray-600 mt-1">Complete el formulario para registrar un nuevo pago/ingreso</p>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo $mensaje; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" class="space-y-6">
            <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción del Pago <span class="text-red-500">*</span>
                </label>
                <input type="text" id="descripcion" name="descripcion" 
                       value="<?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?>"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Ej: Pago DORADA, Pago finanzas, Venta de productos, etc."
                       required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="monto" class="block text-sm font-medium text-gray-700 mb-2">
                        Monto <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" id="monto" name="monto" step="0.01" min="0"
                               value="<?php echo isset($monto) ? $monto : ''; ?>"
                               class="w-full border border-gray-300 rounded-md pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0.00"
                               required>
                    </div>
                </div>

                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="fecha" name="fecha"
                           value="<?php echo isset($fecha) ? $fecha : date('Y-m-d'); ?>"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>
            </div>

            <div>
                <label for="metodo" class="block text-sm font-medium text-gray-700 mb-2">
                    Método de Pago <span class="text-red-500">*</span>
                </label>
                <select id="metodo" name="metodo" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                    <option value="">Seleccione un método...</option>
                    <option value="Efectivo" <?php echo (isset($metodo) && $metodo === 'Efectivo') ? 'selected' : ''; ?>>
                        Efectivo
                    </option>
                    <option value="Tarjeta" <?php echo (isset($metodo) && $metodo === 'Tarjeta') ? 'selected' : ''; ?>>
                        Tarjeta
                    </option>
                    <option value="Transferencia" <?php echo (isset($metodo) && $metodo === 'Transferencia') ? 'selected' : ''; ?>>
                        Transferencia
                    </option>
                </select>
            </div>

            <div class="flex items-center space-x-4 pt-4">
                <button type="submit" 
                        class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Pago
                </button>
                <a href="<?php echo $router->getUrl('pagos'); ?>" 
                   class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver a Pagos
                </a>
            </div>
        </form>
    </div>

    <!-- Información adicional -->
    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-800 mb-2">
            <i class="fas fa-info-circle mr-1"></i>
            Información sobre los pagos:
        </h3>
        <ul class="text-sm text-green-700 space-y-1">
            <li><strong>Pagos DORADA:</strong> Ingresos de la sucursal Dorada</li>
            <li><strong>Pagos Finanzas:</strong> Ingresos de la sucursal Finanzas</li>
            <li><strong>Ventas:</strong> Ventas directas de productos</li>
            <li><strong>Otros ingresos:</strong> Cualquier otro tipo de ingreso</li>
        </ul>
    </div>
</div>

<script>
// Auto-focus en el primer campo
document.getElementById('descripcion').focus();

// Validación del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const monto = document.getElementById('monto').value;
    if (parseFloat(monto) <= 0) {
        e.preventDefault();
        alert('El monto debe ser mayor a 0');
        return false;
    }
});
</script>
