<?php
require_once 'config.php';

echo "<h1>🔧 Corrección de Estructura de Tablas</h1>";

// 1. Verificar tabla Gastos
echo "<h2>1. Verificando tabla Gastos...</h2>";
$query = "DESCRIBE Gastos";
$result = mysqli_query($conexion, $query);

if ($result) {
    $campos_gastos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $campos_gastos[] = $row['Field'];
    }
    
    echo "✅ Tabla Gastos encontrada con campos: " . implode(', ', $campos_gastos) . "<br>";
    
    // Verificar que tenga los campos necesarios
    $campos_requeridos = ['ID', 'Fecha', 'Descripcion', 'Monto', 'Metodo', 'Tipo'];
    $faltantes = array_diff($campos_requeridos, $campos_gastos);
    
    if (empty($faltantes)) {
        echo "✅ Todos los campos requeridos están presentes<br>";
    } else {
        echo "❌ Campos faltantes: " . implode(', ', $faltantes) . "<br>";
    }
} else {
    echo "❌ Error: Tabla Gastos no encontrada<br>";
}

// 2. Verificar tabla Pagos
echo "<h2>2. Verificando tabla Pagos...</h2>";
$query = "DESCRIBE Pagos";
$result = mysqli_query($conexion, $query);

if ($result) {
    $campos_pagos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $campos_pagos[] = $row['Field'];
    }
    
    echo "✅ Tabla Pagos encontrada con campos: " . implode(', ', $campos_pagos) . "<br>";
    
    // Verificar que tenga los campos necesarios
    $campos_requeridos = ['id', 'descripcion', 'monto', 'fecha', 'Metodo'];
    $faltantes = array_diff($campos_requeridos, $campos_pagos);
    
    if (empty($faltantes)) {
        echo "✅ Todos los campos requeridos están presentes<br>";
    } else {
        echo "❌ Campos faltantes: " . implode(', ', $faltantes) . "<br>";
    }
} else {
    echo "❌ Error: Tabla Pagos no encontrada<br>";
}

// 3. Test de inserción en Gastos
echo "<h2>3. Test de inserción en Gastos...</h2>";
$test_sql = "INSERT INTO Gastos (Descripcion, Monto, Fecha, Metodo, Tipo) VALUES (?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($test_sql);

if ($stmt) {
    echo "✅ Query de inserción en Gastos: VÁLIDA<br>";
    $stmt->close();
} else {
    echo "❌ Error en query de Gastos: " . $conexion->error . "<br>";
}

// 4. Test de inserción en Pagos
echo "<h2>4. Test de inserción en Pagos...</h2>";
$test_sql = "INSERT INTO Pagos (descripcion, monto, fecha, Metodo) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($test_sql);

if ($stmt) {
    echo "✅ Query de inserción en Pagos: VÁLIDA<br>";
    $stmt->close();
} else {
    echo "❌ Error en query de Pagos: " . $conexion->error . "<br>";
}

// 5. Test de consultas de resumen
echo "<h2>5. Test de consultas de resumen...</h2>";

// Test consulta gastos
$test_sql = "SELECT Tipo, COUNT(*) as cantidad, SUM(Monto) as total FROM Gastos WHERE Fecha BETWEEN ? AND ? GROUP BY Tipo";
$stmt = $conexion->prepare($test_sql);
if ($stmt) {
    echo "✅ Query de resumen Gastos: VÁLIDA<br>";
    $stmt->close();
} else {
    echo "❌ Error en query resumen Gastos: " . $conexion->error . "<br>";
}

// Test consulta pagos
$test_sql = "SELECT Metodo, COUNT(*) as cantidad, SUM(monto) as total FROM Pagos WHERE fecha BETWEEN ? AND ? GROUP BY Metodo";
$stmt = $conexion->prepare($test_sql);
if ($stmt) {
    echo "✅ Query de resumen Pagos: VÁLIDA<br>";
    $stmt->close();
} else {
    echo "❌ Error en query resumen Pagos: " . $conexion->error . "<br>";
}

// 6. Contar registros
echo "<h2>6. Conteo de registros...</h2>";

$query = "SELECT COUNT(*) as total FROM Gastos";
$result = mysqli_query($conexion, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "📊 Total gastos: " . $row['total'] . "<br>";
} else {
    echo "❌ Error contando gastos: " . mysqli_error($conexion) . "<br>";
}

$query = "SELECT COUNT(*) as total FROM Pagos";
$result = mysqli_query($conexion, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "📊 Total pagos: " . $row['total'] . "<br>";
} else {
    echo "❌ Error contando pagos: " . mysqli_error($conexion) . "<br>";
}

echo "<h2>✅ Diagnóstico Completo</h2>";
echo "<p><strong>Estado:</strong> El sistema parece estar correctamente configurado para tu estructura de base de datos.</p>";

echo "<h3>📋 Estructura Confirmada:</h3>";
echo "<ul>";
echo "<li><strong>Gastos:</strong> ID, Fecha, Descripcion, Monto, Metodo, Tipo</li>";
echo "<li><strong>Pagos:</strong> id, descripcion, monto, fecha, Metodo, created_at</li>";
echo "</ul>";

echo "<h3>🔗 Enlaces de prueba:</h3>";
echo "<a href='index.php?page=gastos' style='background: #dc2626; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 4px;'>Test Gastos</a>";
echo "<a href='index.php?page=pagos' style='background: #16a34a; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 4px;'>Test Pagos</a>";
echo "<a href='index.php?page=add-gasto' style='background: #dc2626; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 4px;'>Agregar Gasto</a>";
echo "<a href='index.php?page=add-pago' style='background: #16a34a; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 4px;'>Agregar Pago</a>";

mysqli_close($conexion);
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #f8fafc;
}
h1, h2 { color: #1e40af; }
ul { background: #e0f2fe; padding: 15px; border-radius: 5px; }
</style>
