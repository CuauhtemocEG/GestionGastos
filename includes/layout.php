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
    <title><?= $router->getCurrentTitle() ?> - Sistema de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .chart-container { position: relative; height: 300px; }
        .animate-fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo y nombre -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <span class="ml-3 text-xl font-bold text-gray-900">GastosApp</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="?page=home" class="<?= $router->getCurrentPath() === 'home' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="?page=gastos" class="<?= $router->getCurrentPath() === 'gastos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                        Gastos
                    </a>
                    <a href="?page=pagos" class="<?= $router->getCurrentPath() === 'pagos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        Pagos
                    </a>
                    <a href="?page=resumen" class="<?= $router->getCurrentPath() === 'resumen' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Resumen
                    </a>
                </div>

                <!-- User menu -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center" x-data="{ open: false }">
                    <div class="ml-3 relative">
                        <div>
                            <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="user-menu" aria-haspopup="true">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium"><?= strtoupper(substr($_SESSION['nombre_completo'] ?? 'U', 0, 1)) ?></span>
                                </div>
                                <span class="ml-2 text-gray-700 font-medium"><?= htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario') ?></span>
                                <svg class="ml-1 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="user-menu">
                                <div class="px-4 py-2 text-xs text-gray-500 border-b">
                                    <?= htmlspecialchars($_SESSION['email'] ?? '') ?>
                                </div>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Mi Perfil</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Configuración</a>
                                <a href="?logout=1" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">Cerrar Sesión</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="sm:hidden flex items-center">
                    <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition" aria-label="Abrir menú">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <?php $router->renderPage(); ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="text-gray-500 text-sm">
                    &copy; <?= date('Y') ?> GastosApp. Desarrollado por CuauhtemocEG
                </div>
                <div class="flex space-x-4 text-sm text-gray-500">
                    <span>v2.0.0</span>
                    <span>•</span>
                    <span>Última actualización: <?= date('d/m/Y H:i') ?></span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
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
