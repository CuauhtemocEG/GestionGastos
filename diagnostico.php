<?php
// Archivo de diagnóstico para verificar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico del Sistema</h1>";

// Verificar conexión a la base de datos
echo "<h2>1. Verificando conexión a la base de datos</h2>";
try {
    $host = 'localhost:3306';
    $usuario = 'kallijag_stage';
    $clave = 'uNtiL.horSe@5';
    $baseDeDatos = 'kallijag_inventory_stage';
    
    $conexion = new mysqli($host, $usuario, $clave, $baseDeDatos);
    
    if ($conexion->connect_error) {
        echo "❌ Error de conexión: " . $conexion->connect_error . "<br>";
    } else {
        echo "✅ Conexión a la base de datos exitosa<br>";
        
        // Verificar tablas
        echo "<h3>Verificando tablas:</h3>";
        $tablas = ['Gastos', 'Pagos', 'Sucursales', 'usuarios'];
        foreach ($tablas as $tabla) {
            $result = $conexion->query("SHOW TABLES LIKE '$tabla'");
            if ($result->num_rows > 0) {
                echo "✅ Tabla '$tabla' existe<br>";
            } else {
                echo "❌ Tabla '$tabla' no existe<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error al conectar: " . $e->getMessage() . "<br>";
}

// Verificar archivos PHP
echo "<h2>2. Verificando archivos PHP</h2>";
$archivos = ['config.php', 'auth.php', 'GastosManager.php', 'index.php', 'pagos.php', 'resumen.php'];
foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ Archivo '$archivo' existe<br>";
    } else {
        echo "❌ Archivo '$archivo' no existe<br>";
    }
}

// Verificar permisos
echo "<h2>3. Verificando permisos</h2>";
echo "Directorio actual: " . getcwd() . "<br>";
echo "Permisos del directorio: " . substr(sprintf('%o', fileperms('.')), -4) . "<br>";

// Verificar versión de PHP
echo "<h2>4. Información de PHP</h2>";
echo "Versión de PHP: " . phpversion() . "<br>";
echo "Extensiones cargadas: " . implode(', ', get_loaded_extensions()) . "<br>";

// Verificar si mysqli está disponible
if (extension_loaded('mysqli')) {
    echo "✅ Extensión mysqli está disponible<br>";
} else {
    echo "❌ Extensión mysqli NO está disponible<br>";
}

echo "<h2>5. Variables de servidor</h2>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'No definido') . "<br>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'No definido') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No definido') . "<br>";

echo "<h2>Diagnóstico completado</h2>";
?>
