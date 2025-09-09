<?php
// Script de inicialización del sistema
// Desactivar verificación de autenticación para este script
$_SESSION['skip_auth'] = true;

include 'config.php';

echo "<h2>🚀 Inicializando Sistema de Gastos Profesional</h2>\n";

// 1. Verificar conexión a la base de datos
if ($conexion->connect_error) {
    die("❌ Error de conexión a la base de datos: " . $conexion->connect_error);
}
echo "✅ Conexión a la base de datos exitosa<br>";

// 2. Crear usuario demo si no existe
$email = 'admin@gastosapp.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$nombre = 'Administrador Demo';

$sql = "SELECT id FROM usuarios WHERE email = ?";
$stmt = $conexion->prepare($sql);
if ($stmt) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $sql = "INSERT INTO usuarios (email, password, nombre_completo, rol, activo, fecha_creacion) 
                VALUES (?, ?, ?, 'admin', 1, NOW())";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('sss', $email, $password, $nombre);
        
        if ($stmt->execute()) {
            echo "✅ Usuario administrador creado exitosamente<br>";
            echo "📧 Email: admin@gastosapp.com<br>";
            echo "🔑 Contraseña: admin123<br><br>";
        } else {
            echo "❌ Error creando usuario: " . $conexion->error . "<br><br>";
        }
    } else {
        echo "✅ Usuario administrador ya existe<br><br>";
    }
} else {
    echo "❌ Error preparando consulta de usuario<br><br>";
}

// 2. Verificar tablas necesarias
$tablas_requeridas = [
    'usuarios' => "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nombre_completo VARCHAR(255) NOT NULL,
            rol ENUM('admin', 'usuario') DEFAULT 'usuario',
            activo TINYINT(1) DEFAULT 1,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'sesiones' => "
        CREATE TABLE IF NOT EXISTS sesiones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_fin TIMESTAMP NULL,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'recuperacion_password' => "
        CREATE TABLE IF NOT EXISTS recuperacion_password (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            token VARCHAR(64) UNIQUE NOT NULL,
            expira TIMESTAMP NOT NULL,
            usado TINYINT(1) DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    'configuraciones' => "
        CREATE TABLE IF NOT EXISTS configuraciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(100) UNIQUE NOT NULL,
            valor TEXT,
            descripcion TEXT,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tablas_requeridas as $tabla => $sql) {
    if ($conexion->query($sql)) {
        echo "✅ Tabla '$tabla' verificada/creada<br>";
    } else {
        echo "❌ Error con tabla '$tabla': " . $conexion->error . "<br>";
    }
}

// 3. Insertar configuraciones por defecto
$configuraciones_default = [
    ['limite_gastos_diario', '5000.00', 'Límite diario de gastos'],
    ['limite_gastos_mensual', '150000.00', 'Límite mensual de gastos'],
    ['moneda_default', 'MXN', 'Moneda por defecto del sistema'],
    ['timezone', 'America/Mexico_City', 'Zona horaria del sistema'],
    ['empresa_nombre', 'Mi Empresa', 'Nombre de la empresa'],
    ['empresa_rfc', 'XAXX010101000', 'RFC de la empresa'],
    ['notificaciones_email', '1', 'Activar notificaciones por email'],
    ['backup_automatico', '1', 'Activar backup automático']
];

foreach ($configuraciones_default as $config) {
    $sql = "INSERT IGNORE INTO configuraciones (clave, valor, descripcion) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('sss', $config[0], $config[1], $config[2]);
    $stmt->execute();
}

echo "✅ Configuraciones por defecto instaladas<br><br>";

// 4. Verificar datos de prueba
$sql = "SELECT COUNT(*) as total FROM Gastos";
$result = $conexion->query($sql);
$gastos_count = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM Pagos";
$result = $conexion->query($sql);
$pagos_count = $result->fetch_assoc()['total'];

echo "<h3>📊 Estado actual de datos:</h3>";
echo "💳 Gastos registrados: $gastos_count<br>";
echo "💰 Pagos registrados: $pagos_count<br><br>";

// 5. Estado final
echo "<h3>🎉 Sistema inicializado correctamente!</h3>";
echo "<div style='background: #f0f9ff; padding: 15px; border-left: 4px solid #0ea5e9; margin: 10px 0;'>";
echo "<strong>Próximos pasos:</strong><br>";
echo "1. Accede a <a href='auth/login.php' style='color: #0ea5e9;'>auth/login.php</a><br>";
echo "2. Usa las credenciales: admin@gastosapp.com / admin123<br>";
echo "3. Explora el nuevo dashboard con analytics<br>";
echo "4. Configura filtros avanzados por fechas<br>";
echo "5. ¡Disfruta de tu sistema profesional!<br>";
echo "</div>";

echo "<br><a href='auth/login.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Ir al Login</a>";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: #f8fafc;
    color: #334155;
}
h2, h3 {
    color: #1e40af;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 8px;
}
</style>
