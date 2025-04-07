<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];
    $method = $_POST['typeExpense'];

    $sql = "INSERT INTO Gastos (Descripcion, Monto, Fecha) VALUES ('$descripcion', '$monto', '$fecha', '$method')";

    if ($conexion->query($sql) === TRUE) {
        header('Location: index.php');
    } else {
        echo "Error: " . $conexion->error;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Agregar Gasto</h1>

        <form action="addExpenses.php" method="POST">
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" class="form-control" id="descripcion" name="descripcion" required>
            </div>
            <div class="mb-3">
                <label for="monto" class="form-label">Monto</label>
                <input type="number" class="form-control" id="monto" name="monto" required>
            </div>
            <div class="mb-3">
                <label for="typeExpense" class="form-label">Medio de Pago</label>
                <select name="typeExpense">
                    <option>Pago con Tarjeta</option>
                    <option>Efectivo</option>
                    <?php
					$categorias = $conexion->query("SELECT * FROM Gastos");
					if ($categorias->fetch_row() > 0) {
						$categorias = $categorias->fetch_all();
						foreach ($categorias as $row) {
							echo '<option value="' . $row['ID'] . '" >' . $row['Metodo'] . '</option>';
						}
					}
					$categorias = null;
					?>
                </select>
            </div>
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Gasto</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
