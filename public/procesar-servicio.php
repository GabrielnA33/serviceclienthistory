<?php
include '../config/database.php';
$conexion = conectarDB();

$cliente = $_POST['cliente'];
$fecha = $_POST['fecha'];
$tecnico = $_POST['tecnico'];
$observacion = $_POST['obs'];
$importe = $_POST['importe'];

$query = "INSERT INTO servicios (FECHA, CLIENTE, TECNOM, OBSCLI, IMPORTE) VALUES (?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "sssss", $fecha, $cliente, $tecnico, $observacion, $importe);

if (mysqli_stmt_execute($stmt)) {
    header("Location: cliente.php?codigo=$cliente&msg=Servicio agregado con éxito");
} else {
    echo "Error al agregar servicio: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
