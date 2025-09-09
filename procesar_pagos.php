<?php
// Archivo para procesar acciones de pagos (editar, eliminar)
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
            $metodo = $_POST['metodo'];
            
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
            
            // Actualizar en base de datos
            $sql = "UPDATE Pagos SET 
                    fecha = ?, 
                    monto = ?, 
                    descripcion = ?, 
                    Metodo = ? 
                    WHERE id = ?";
            
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error en la preparación de la consulta: ' . $conexion->error);
            }
            
            $stmt->bind_param('sdssi', $fecha, $monto, $descripcion, $metodo, $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Pago actualizado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró el pago o no hubo cambios']);
                }
            } else {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }
            
            $stmt->close();
            break;
            
        case 'delete':
            $id = intval($_POST['id']);
            
            if ($id <= 0) {
                throw new Exception('ID de pago inválido');
            }
            
            // Verificar que el pago existe
            $sql_check = "SELECT id FROM Pagos WHERE id = ?";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->bind_param('i', $id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('El pago no existe');
            }
            $stmt_check->close();
            
            // Eliminar pago
            $sql = "DELETE FROM Pagos WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error en la preparación de la consulta: ' . $conexion->error);
            }
            
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Pago eliminado exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el pago']);
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
