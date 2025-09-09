<?php
class GastosManager {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    public function obtenerGastosFiltrados($filtros = []) {
        $sql = "SELECT * FROM Gastos WHERE 1=1";
        $params = [];
        $types = "";
        
        // Filtro por fecha
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND Fecha >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND Fecha <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }
        
        // Filtro por tipo
        if (!empty($filtros['tipo']) && $filtros['tipo'] !== 'todos') {
            $sql .= " AND Tipo = ?";
            $params[] = $filtros['tipo'];
            $types .= "s";
        }
        
        // Filtro por método
        if (!empty($filtros['metodo']) && $filtros['metodo'] !== 'todos') {
            $sql .= " AND Metodo = ?";
            $params[] = $filtros['metodo'];
            $types .= "s";
        }
        
        // Filtro por rango de monto
        if (!empty($filtros['monto_min'])) {
            $sql .= " AND Monto >= ?";
            $params[] = $filtros['monto_min'];
            $types .= "d";
        }
        
        if (!empty($filtros['monto_max'])) {
            $sql .= " AND Monto <= ?";
            $params[] = $filtros['monto_max'];
            $types .= "d";
        }
        
        // Filtro por descripción
        if (!empty($filtros['descripcion'])) {
            $sql .= " AND Descripcion LIKE ?";
            $params[] = "%" . $filtros['descripcion'] . "%";
            $types .= "s";
        }
        
        // Ordenamiento
        $ordenamiento = $filtros['orden'] ?? 'fecha_desc';
        switch ($ordenamiento) {
            case 'fecha_asc':
                $sql .= " ORDER BY Fecha ASC";
                break;
            case 'fecha_desc':
                $sql .= " ORDER BY Fecha DESC";
                break;
            case 'monto_asc':
                $sql .= " ORDER BY Monto ASC";
                break;
            case 'monto_desc':
                $sql .= " ORDER BY Monto DESC";
                break;
            case 'descripcion':
                $sql .= " ORDER BY Descripcion ASC";
                break;
            default:
                $sql .= " ORDER BY Fecha DESC";
        }
        
        // Paginación
        $limite = $filtros['limite'] ?? 50;
        $pagina = $filtros['pagina'] ?? 1;
        $offset = ($pagina - 1) * $limite;
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conexion->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerPagosFiltrados($filtros = []) {
        $sql = "SELECT * FROM Pagos WHERE 1=1";
        $params = [];
        $types = "";
        
        // Filtro por fecha
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND fecha >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND fecha <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }
        
        // Filtro por método
        if (!empty($filtros['metodo']) && $filtros['metodo'] !== 'todos') {
            $sql .= " AND Metodo = ?";
            $params[] = $filtros['metodo'];
            $types .= "s";
        }
        
        // Filtro por rango de monto
        if (!empty($filtros['monto_min'])) {
            $sql .= " AND monto >= ?";
            $params[] = $filtros['monto_min'];
            $types .= "d";
        }
        
        if (!empty($filtros['monto_max'])) {
            $sql .= " AND monto <= ?";
            $params[] = $filtros['monto_max'];
            $types .= "d";
        }
        
        // Filtro por descripción
        if (!empty($filtros['descripcion'])) {
            $sql .= " AND descripcion LIKE ?";
            $params[] = "%" . $filtros['descripcion'] . "%";
            $types .= "s";
        }
        
        // Ordenamiento
        $ordenamiento = $filtros['orden'] ?? 'fecha_desc';
        switch ($ordenamiento) {
            case 'fecha_asc':
                $sql .= " ORDER BY fecha ASC";
                break;
            case 'fecha_desc':
                $sql .= " ORDER BY fecha DESC";
                break;
            case 'monto_asc':
                $sql .= " ORDER BY monto ASC";
                break;
            case 'monto_desc':
                $sql .= " ORDER BY monto DESC";
                break;
            case 'descripcion':
                $sql .= " ORDER BY descripcion ASC";
                break;
            default:
                $sql .= " ORDER BY fecha DESC";
        }
        
        // Paginación
        $limite = $filtros['limite'] ?? 50;
        $pagina = $filtros['pagina'] ?? 1;
        $offset = ($pagina - 1) * $limite;
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $this->conexion->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerEstadisticas($filtros = []) {
        $condiciones = "WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($filtros['fecha_inicio'])) {
            $condiciones .= " AND Fecha >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $condiciones .= " AND Fecha <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }
        
        // Estadísticas de gastos
        $sqlGastos = "
            SELECT 
                COUNT(*) as total_transacciones,
                SUM(Monto) as total_monto,
                AVG(Monto) as promedio_monto,
                MIN(Monto) as monto_minimo,
                MAX(Monto) as monto_maximo,
                Tipo,
                Metodo
            FROM Gastos $condiciones
            GROUP BY Tipo, Metodo
        ";
        
        $stmt = $this->conexion->prepare($sqlGastos);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $estadisticasGastos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Total general de gastos
        $sqlTotalGastos = "SELECT SUM(Monto) as total FROM Gastos $condiciones";
        $stmt = $this->conexion->prepare($sqlTotalGastos);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $totalGastos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        
        return [
            'estadisticas_detalladas' => $estadisticasGastos,
            'total_gastos' => $totalGastos
        ];
    }
    
    public function contarTotalRegistros($tabla, $filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM $tabla WHERE 1=1";
        $params = [];
        $types = "";
        
        $campoFecha = ($tabla === 'Gastos') ? 'Fecha' : 'fecha';
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND $campoFecha >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND $campoFecha <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }
        
        if ($tabla === 'Gastos' && !empty($filtros['tipo']) && $filtros['tipo'] !== 'todos') {
            $sql .= " AND Tipo = ?";
            $params[] = $filtros['tipo'];
            $types .= "s";
        }
        
        if (!empty($filtros['metodo']) && $filtros['metodo'] !== 'todos') {
            $sql .= " AND Metodo = ?";
            $params[] = $filtros['metodo'];
            $types .= "s";
        }
        
        $stmt = $this->conexion->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        
        return $resultado['total'];
    }
}

$gastosManager = new GastosManager($conexion);
?>
