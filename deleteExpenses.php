<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM Gastos WHERE ID = $id";

    if ($conexion->query($sql) === TRUE) {
        header('Location: index.php');
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>
