<?php
// Archivo para procesar acciones de gastos (editar, eliminar)
header('Content-Type: application/json');

// Incluir configuración de base de datos
include 'config.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'edit':
            $id = intval($_POST['id']);
            $fecha = $_POST['fecha'];
            $monto = floatval($_POST['monto']);
            $descripcion = trim($_POST['descripcion']);
            $tipo = $_POST['tipo'];
            $metodo = $_POST['metodo'];
            
            // Debug: Log de los valores recibidos
            error_log("DEBUG - Valores recibidos: ID=$id, metodo='$metodo', descripcion='$descripcion'");
            
            // Validaciones
            if (empty($descripcion)) {
                throw new Exception('La descripción es requerida');
            }
            
            if ($monto <= 0) {
                throw new Exception('El monto debe ser mayor a 0');
            }
            
            if (empty($fecha)) {
                throw new Exception('La fecha es requerida');
            }
            
            // Validar que el método sea uno de los permitidos
            $metodos_validos = ['Efectivo', 'Tarjeta', 'Transferencia'];
            if (!in_array($metodo, $metodos_validos)) {
                throw new Exception("Método de pago inválido: '$metodo'. Métodos válidos: " . implode(', ', $metodos_validos));
            }
            
            // Actualizar en base de datos
            $sql = "UPDATE Gastos SET 
                    Fecha = ?, 
                    Monto = ?, 
                    Descripcion = ?, 
                    Tipo = ?, 
                    Metodo = ? 
                    WHERE ID = ?";
            
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error en la preparación de la consulta: ' . $conexion->error);
            }
            
            $stmt->bind_param('sdsssi', $fecha, $monto, $descripcion, $tipo, $metodo, $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Gasto actualizado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el gasto o no hubo cambios']);
                }
            } else {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }
            
            $stmt->close();
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            
            if ($id <= 0) {
                throw new Exception('ID de gasto inválido');
            }
            
            // Verificar que el gasto existe
            $sql_check = "SELECT ID FROM Gastos WHERE ID = ?";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->bind_param('i', $id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('El gasto no existe');
            }
            $stmt_check->close();
            
            // Eliminar gasto
            $sql = "DELETE FROM Gastos WHERE ID = ?";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error en la preparación de la consulta: ' . $conexion->error);
            }
            
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Gasto eliminado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el gasto']);
                }
            } else {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }
            
            $stmt->close();
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conexion)) {
        $conexion->close();
    }
}
?>
