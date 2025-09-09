<?php
// Variables globales necesarias para compatibilidad
global $conexion;

// Definir variables de filtros por defecto si no existen
if (!isset($fechaInicio)) $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
if (!isset($fechaFin)) $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
if (!isset($tipoFiltro)) $tipoFiltro = $_GET['tipo'] ?? 'todos';
if (!isset($metodoFiltro)) $metodoFiltro = $_GET['metodo'] ?? 'todos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $router->getCurrentTitle(); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navegación -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold">
                        <i class="fas fa-calculator mr-2"></i>
                        Sistema de Gastos
                    </h1>
                </div>
                
                <div class="hidden md:flex space-x-4">
                    <a href="<?php echo $router->getUrl('home'); ?>" 
                       class="hover:bg-blue-700 px-3 py-2 rounded transition-colors
                              <?php echo $router->getCurrentPath() === 'home' ? 'bg-blue-800' : ''; ?>">
                        <i class="fas fa-home mr-1"></i> Inicio
                    </a>
                    <a href="<?php echo $router->getUrl('gastos'); ?>" 
                       class="hover:bg-blue-700 px-3 py-2 rounded transition-colors
                              <?php echo $router->getCurrentPath() === 'gastos' ? 'bg-blue-800' : ''; ?>">
                        <i class="fas fa-credit-card mr-1"></i> Gastos
                    </a>
                    <a href="<?php echo $router->getUrl('pagos'); ?>" 
                       class="hover:bg-blue-700 px-3 py-2 rounded transition-colors
                              <?php echo $router->getCurrentPath() === 'pagos' ? 'bg-blue-800' : ''; ?>">
                        <i class="fas fa-money-bill mr-1"></i> Pagos
                    </a>
                    <a href="<?php echo $router->getUrl('resumen'); ?>" 
                       class="hover:bg-blue-700 px-3 py-2 rounded transition-colors
                              <?php echo $router->getCurrentPath() === 'resumen' ? 'bg-blue-800' : ''; ?>">
                        <i class="fas fa-chart-bar mr-1"></i> Resumen
                    </a>
                    <a href="<?php echo $router->getUrl('dashboard'); ?>" 
                       class="hover:bg-blue-700 px-3 py-2 rounded transition-colors
                              <?php echo $router->getCurrentPath() === 'dashboard' ? 'bg-blue-800' : ''; ?>">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm">
                        <i class="fas fa-user mr-1"></i>
                        Sistema Activo
                    </span>
                </div>
            </div>
            
            <!-- Navegación móvil -->
            <div class="md:hidden border-t border-blue-500 pt-2 pb-2">
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo $router->getUrl('home'); ?>" class="bg-blue-700 px-2 py-1 rounded text-sm">Inicio</a>
                    <a href="<?php echo $router->getUrl('gastos'); ?>" class="bg-blue-700 px-2 py-1 rounded text-sm">Gastos</a>
                    <a href="<?php echo $router->getUrl('pagos'); ?>" class="bg-blue-700 px-2 py-1 rounded text-sm">Pagos</a>
                    <a href="<?php echo $router->getUrl('resumen'); ?>" class="bg-blue-700 px-2 py-1 rounded text-sm">Resumen</a>
                    <a href="<?php echo $router->getUrl('dashboard'); ?>" class="bg-blue-700 px-2 py-1 rounded text-sm">Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto py-6 px-4">
        <?php $router->renderPage(); ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; 2025 Sistema de Gestión de Gastos. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        // Función para mostrar notificaciones
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>
