<?php
// Sistema de enrutamiento simple
class Router {
    private $routes = [];
    private $basePath;
    
    public function __construct($basePath = '') {
        $this->basePath = $basePath;
    }
    
    public function addRoute($path, $file, $title = '') {
        $this->routes[$path] = [
            'file' => $file,
            'title' => $title
        ];
    }
    
    public function getRoutes() {
        return $this->routes;
    }
    
    public function getCurrentPath() {
        $path = $_GET['page'] ?? 'home';
        return $path;
    }
    
    public function getCurrentTitle() {
        $path = $this->getCurrentPath();
        return $this->routes[$path]['title'] ?? 'Sistema de Gastos';
    }
    
    public function renderPage() {
        global $conexion; // Hacer la conexión disponible
        $path = $this->getCurrentPath();
        
        if (isset($this->routes[$path])) {
            $file = $this->routes[$path]['file'];
            if (file_exists($file)) {
                include $file;
            } else {
                $this->render404();
            }
        } else {
            $this->render404();
        }
    }
    
    public function render404() {
        http_response_code(404);
        echo "<h1>Página no encontrada</h1>";
        echo "<p>La página solicitada no existe.</p>";
        echo "<a href='?page=home'>Ir al inicio</a>";
    }
    
    public function getUrl($page) {
        return "?page=" . $page;
    }
}

// Configurar rutas
$router = new Router();
$router->addRoute('home', 'pages/home.php', 'Inicio - Gastos');
$router->addRoute('gastos', 'pages/gastos.php', 'Gestión de Gastos');
$router->addRoute('pagos', 'pages/pagos.php', 'Gestión de Pagos');
$router->addRoute('resumen', 'pages/resumen.php', 'Resumen Financiero');
$router->addRoute('add-gasto', 'pages/add-gasto.php', 'Agregar Gasto');
$router->addRoute('add-pago', 'pages/add-pago.php', 'Agregar Pago');
$router->addRoute('dashboard', 'pages/dashboard.php', 'Dashboard Avanzado');
$router->addRoute('configuracion', 'pages/configuracion.php', 'Configuración');
?>
