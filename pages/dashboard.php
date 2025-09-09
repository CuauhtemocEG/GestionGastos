<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">
        <i class="fas fa-tachometer-alt mr-2 text-blue-600"></i>
        Dashboard Avanzado
    </h1>
    <p class="text-gray-600 mt-1">Panel de control con métricas y análisis avanzado</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="text-center py-12">
        <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
            <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Dashboard Avanzado</h3>
        <p class="text-gray-600 mb-6">
            Esta sección contendrá gráficos avanzados, métricas detalladas y análisis predictivo.<br>
            Por ahora, utilice las otras secciones para gestionar sus gastos y pagos.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto">
            <a href="<?php echo $router->getUrl('home'); ?>" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                <i class="fas fa-home mr-1"></i> Ir al Inicio
            </a>
            <a href="<?php echo $router->getUrl('gastos'); ?>" 
               class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                <i class="fas fa-credit-card mr-1"></i> Ver Gastos
            </a>
            <a href="<?php echo $router->getUrl('pagos'); ?>" 
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
                <i class="fas fa-money-bill mr-1"></i> Ver Pagos
            </a>
        </div>
    </div>
</div>

<!-- Características futuras -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-pie text-purple-600"></i>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Gráficos Interactivos</h3>
        </div>
        <p class="text-gray-600 text-sm">Visualizaciones avanzadas con filtros dinámicos y análisis temporal.</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-robot text-indigo-600"></i>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Análisis Predictivo</h3>
        </div>
        <p class="text-gray-600 text-sm">Predicciones de gastos futuros basadas en patrones históricos.</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-bell text-yellow-600"></i>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Alertas Inteligentes</h3>
        </div>
        <p class="text-gray-600 text-sm">Notificaciones automáticas para gastos inusuales y límites de presupuesto.</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-export text-green-600"></i>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Exportación Avanzada</h3>
        </div>
        <p class="text-gray-600 text-sm">Reportes personalizados en múltiples formatos con análisis automático.</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-red-600"></i>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Auditoría Completa</h3>
        </div>
        <p class="text-gray-600 text-sm">Registro detallado de todas las transacciones y cambios del sistema.</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-mobile-alt text-blue-600"></i>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">App Móvil</h3>
        </div>
        <p class="text-gray-600 text-sm">Aplicación móvil para gestión de gastos en tiempo real desde cualquier lugar.</p>
    </div>
</div>
