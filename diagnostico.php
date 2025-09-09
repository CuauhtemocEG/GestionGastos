<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">🔧 Diagnóstico del Sistema</h1>
        
        <?php
        // Configuraciones posibles de MAMP
        $configuraciones = [
            ['host' => 'localhost', 'puerto' => 3306, 'usuario' => 'root', 'clave' => 'root'],
            ['host' => 'localhost', 'puerto' => 8889, 'usuario' => 'root', 'clave' => 'root'],
            ['host' => '127.0.0.1', 'puerto' => 3306, 'usuario' => 'root', 'clave' => 'root'],
            ['host' => '127.0.0.1', 'puerto' => 8889, 'usuario' => 'root', 'clave' => 'root'],
        ];
        
        echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
        
        foreach ($configuraciones as $i => $config) {
            echo '<div class="bg-white rounded-lg shadow-md p-6">';
            echo '<h3 class="text-lg font-semibold mb-4">Configuración ' . ($i + 1) . '</h3>';
            echo '<p class="text-sm text-gray-600 mb-2">Host: ' . $config['host'] . ':' . $config['puerto'] . '</p>';
            
            try {
                $conexion = new mysqli($config['host'], $config['usuario'], $config['clave'], '', $config['puerto']);
                
                if ($conexion->connect_error) {
                    echo '<div class="bg-red-100 text-red-700 p-3 rounded">';
                    echo '❌ Error: ' . $conexion->connect_error;
                    echo '</div>';
                } else {
                    echo '<div class="bg-green-100 text-green-700 p-3 rounded">';
                    echo '✅ Conexión exitosa!';
                    echo '</div>';
                    
                    // Verificar si existe la base de datos
                    $result = $conexion->query("SHOW DATABASES LIKE 'GastosApp'");
                    if ($result && $result->num_rows > 0) {
                        echo '<p class="mt-2 text-green-600">✅ Base de datos GastosApp existe</p>';
                        
                        // Usar esta configuración para crear config.php correcto
                        echo '<div class="mt-4 p-3 bg-blue-50 rounded">';
                        echo '<p class="font-semibold text-blue-800">Configuración recomendada para config.php:</p>';
                        echo '<pre class="text-xs mt-2 text-blue-700">';
                        echo "\$host = '{$config['host']}';\n";
                        echo "\$usuario = '{$config['usuario']}';\n";
                        echo "\$clave = '{$config['clave']}';\n";
                        echo "\$puerto = {$config['puerto']};\n";
                        echo "\$baseDeDatos = 'GastosApp';";
                        echo '</pre>';
                        echo '</div>';
                    } else {
                        echo '<p class="mt-2 text-yellow-600">⚠️ Base de datos GastosApp no existe</p>';
                        echo '<button onclick="crearBaseDatos(' . $i . ')" class="mt-2 bg-blue-600 text-white px-3 py-1 rounded text-sm">Crear Base de Datos</button>';
                    }
                }
            } catch (Exception $e) {
                echo '<div class="bg-red-100 text-red-700 p-3 rounded">';
                echo '❌ Error: ' . $e->getMessage();
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        ?>
        
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">📋 Instrucciones</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Asegúrate de que MAMP esté ejecutándose</li>
                <li>Encuentra la configuración que muestre "✅ Conexión exitosa"</li>
                <li>Si la base de datos no existe, haz clic en "Crear Base de Datos"</li>
                <li>Actualiza el archivo config.php con la configuración recomendada</li>
                <li>Prueba las páginas de nuevo</li>
            </ol>
        </div>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Volver al Sistema
            </a>
        </div>
    </div>
    
    <script>
    function crearBaseDatos(configIndex) {
        alert('Para crear la base de datos, ejecuta este comando en phpMyAdmin:\nCREATE DATABASE GastosApp;');
    }
    </script>
</body>
</html>
