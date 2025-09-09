<?php
/**
 * Componente de filtros reutilizable para todas las páginas
 * Uso: include 'includes/filtros-component.php';
 */

// Configuración por defecto de filtros
$filtros_config = $filtros_config ?? [
    'mostrar_fecha' => true,
    'mostrar_tipo' => false,
    'mostrar_metodo' => true,
    'mostrar_monto' => false,
    'mostrar_descripcion' => false,
    'mostrar_categoria' => false,
    'mostrar_exportar' => true,
    'accion_form' => '',
    'titulo' => 'Filtros'
];

// Valores actuales de filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipo_filtro = $_GET['tipo'] ?? 'todos';
$metodo_filtro = $_GET['metodo'] ?? 'todos';
$categoria_filtro = $_GET['categoria'] ?? 'todos';
$monto_min = $_GET['monto_min'] ?? '';
$monto_max = $_GET['monto_max'] ?? '';
$descripcion_filtro = $_GET['descripcion'] ?? '';

// Obtener tipos únicos de gastos
$tipos_gastos = [];
if ($filtros_config['mostrar_tipo']) {
    $sql_tipos = "SELECT DISTINCT Tipo FROM Gastos WHERE Tipo IS NOT NULL AND Tipo != '' ORDER BY Tipo";
    $result_tipos = $conexion->query($sql_tipos);
    while ($row = $result_tipos->fetch_assoc()) {
        $tipos_gastos[] = $row['Tipo'];
    }
}

// Obtener categorías únicas
$categorias = [];
if ($filtros_config['mostrar_categoria']) {
    $sql_categorias = "SELECT DISTINCT categoria FROM Gastos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria";
    $result_categorias = $conexion->query($sql_categorias);
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row['categoria'];
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-filter mr-2 text-blue-600"></i>
            <?php echo $filtros_config['titulo']; ?>
        </h3>
        <button type="button" id="toggle-filtros" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-chevron-down mr-1"></i>
            <span>Mostrar/Ocultar</span>
        </button>
    </div>

    <form method="GET" id="filtros-form" class="filtros-content">
        <?php if (isset($_GET['page'])): ?>
            <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-4">
            
            <?php if ($filtros_config['mostrar_fecha']): ?>
            <!-- Filtros de Fecha -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <?php endif; ?>

            <?php if ($filtros_config['mostrar_tipo']): ?>
            <!-- Filtro por Tipo -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Tipo de Gasto</label>
                <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="todos" <?php echo $tipo_filtro === 'todos' ? 'selected' : ''; ?>>Todos los tipos</option>
                    <?php foreach ($tipos_gastos as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo); ?>" 
                                <?php echo $tipo_filtro === $tipo ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($filtros_config['mostrar_categoria']): ?>
            <!-- Filtro por Categoría -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Categoría</label>
                <select name="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="todos" <?php echo $categoria_filtro === 'todos' ? 'selected' : ''; ?>>Todas las categorías</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo htmlspecialchars($categoria); ?>" 
                                <?php echo $categoria_filtro === $categoria ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($filtros_config['mostrar_metodo']): ?>
            <!-- Filtro por Método -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                <select name="metodo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="todos" <?php echo $metodo_filtro === 'todos' ? 'selected' : ''; ?>>Todos los métodos</option>
                    <option value="Efectivo" <?php echo $metodo_filtro === 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                    <option value="Tarjeta" <?php echo $metodo_filtro === 'Tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                    <option value="Transferencia" <?php echo $metodo_filtro === 'Transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($filtros_config['mostrar_monto']): ?>
            <!-- Filtros de Monto -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Monto Mínimo</label>
                <input type="number" name="monto_min" value="<?php echo $monto_min; ?>" 
                       step="0.01" placeholder="0.00"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Monto Máximo</label>
                <input type="number" name="monto_max" value="<?php echo $monto_max; ?>" 
                       step="0.01" placeholder="Sin límite"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <?php endif; ?>

            <?php if ($filtros_config['mostrar_descripcion']): ?>
            <!-- Filtro por Descripción -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Buscar en Descripción</label>
                <input type="text" name="descripcion" value="<?php echo htmlspecialchars($descripcion_filtro); ?>" 
                       placeholder="Buscar texto..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <?php endif; ?>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-wrap gap-3 items-center justify-between pt-4 border-t border-gray-200">
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-1"></i>
                    Aplicar Filtros
                </button>
                
                <a href="?page=<?php echo $_GET['page'] ?? 'home'; ?>" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-1"></i>
                    Limpiar
                </a>
            </div>

            <?php if ($filtros_config['mostrar_exportar']): ?>
            <div class="flex gap-2">
                <button type="button" onclick="exportarDatos('excel')" 
                        class="bg-green-600 text-white px-3 py-2 rounded-md hover:bg-green-700 transition-colors text-sm">
                    <i class="fas fa-file-excel mr-1"></i>
                    Excel
                </button>
                <button type="button" onclick="exportarDatos('pdf')" 
                        class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 transition-colors text-sm">
                    <i class="fas fa-file-pdf mr-1"></i>
                    PDF
                </button>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Resumen de Filtros Activos -->
<?php
$filtros_activos = [];
if ($fecha_inicio !== date('Y-m-01')) $filtros_activos[] = "Desde: $fecha_inicio";
if ($fecha_fin !== date('Y-m-d')) $filtros_activos[] = "Hasta: $fecha_fin";
if ($tipo_filtro !== 'todos') $filtros_activos[] = "Tipo: $tipo_filtro";
if ($metodo_filtro !== 'todos') $filtros_activos[] = "Método: $metodo_filtro";
if ($categoria_filtro !== 'todos') $filtros_activos[] = "Categoría: $categoria_filtro";
if ($monto_min !== '') $filtros_activos[] = "Min: $$monto_min";
if ($monto_max !== '') $filtros_activos[] = "Max: $$monto_max";
if ($descripcion_filtro !== '') $filtros_activos[] = "Busca: '$descripcion_filtro'";

if (!empty($filtros_activos)):
?>
<div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
    <div class="flex items-center">
        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
        <span class="text-sm text-blue-800">
            <strong>Filtros activos:</strong> <?php echo implode(' | ', $filtros_activos); ?>
        </span>
    </div>
</div>
<?php endif; ?>

<script>
// Toggle para mostrar/ocultar filtros
document.getElementById('toggle-filtros').addEventListener('click', function() {
    const content = document.querySelector('.filtros-content');
    const icon = this.querySelector('i');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-down mr-1';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-right mr-1';
    }
});

// Funciones de exportación
function exportarDatos(formato) {
    const formData = new FormData(document.getElementById('filtros-form'));
    formData.append('exportar', formato);
    formData.append('page', '<?php echo $_GET['page'] ?? ''; ?>');
    
    const params = new URLSearchParams(formData);
    window.open('includes/exportar.php?' + params.toString(), '_blank');
}

// Auto-submit cuando cambian los filtros principales
document.querySelectorAll('select[name="tipo"], select[name="metodo"], select[name="categoria"]').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filtros-form').submit();
    });
});
</script>
