<?php
include '../config/database.php';
$conexion = conectarDB();

$accion = $_POST['accion'];
$id = $_POST['id'];

if ($accion === "editar") {
    $fecha = $_POST['fecha'];
    $tecnico = $_POST['tecnico'];
    $observacion = $_POST['obs'];
    $importe = $_POST['importe'];

    $query = "UPDATE servicios SET FECHA = ?, TECNOM = ?, OBSCLI = ?, IMPORTE = ? WHERE Id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "ssssi", $fecha, $tecnico, $observacion, $importe, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: cliente.php?msg=Servicio actualizado con éxito");
    } else {
        echo "Error al actualizar servicio: " . mysqli_error($conexion);
    }
} elseif ($accion === "eliminar") {
    $query = "DELETE FROM servicios WHERE Id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: cliente.php?msg=Servicio eliminado con éxito");
    } else {
        echo "Error al eliminar servicio: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);
?>
