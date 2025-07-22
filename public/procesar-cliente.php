<?php
include '../config/database.php';
$conexion = conectarDB();

function limpiarEntrada($valor) {
    return !empty($valor) ? strtoupper(trim($valor)) : null;
}

$codigo = $_POST['codigo'];
$nombre = limpiarEntrada($_POST['nombre']);
$calle = limpiarEntrada($_POST['calle']);
$nro = limpiarEntrada($_POST['nro']);
$piso = !empty($_POST['piso']) ? limpiarEntrada($_POST['piso']) : null;
$dto = limpiarEntrada($_POST['dto']);
$ldad = limpiarEntrada($_POST['localidad']);
$cpa = limpiarEntrada($_POST['cpa']);
$tel1pre = limpiarEntrada($_POST['tel1pre']);
$tel1 = limpiarEntrada($_POST['tel1']);
$tel2pre = limpiarEntrada($_POST['tel2pre']);
$tel2 = limpiarEntrada($_POST['tel2']);
$tel3pre = limpiarEntrada($_POST['tel3pre']);
$tel3 = limpiarEntrada($_POST['tel3']);
$cuit = limpiarEntrada($_POST['cuit']);
$iva = limpiarEntrada($_POST['iva']);
$obs = limpiarEntrada($_POST['obs']);
$alta = date("Y-m-d");

// Insertar en la base de datos
$query = "INSERT INTO clientes (CODIGO, NOMBRE, CALLE, NRO, PISO, DTO, LDAD, CPA, TEL1PRE, TEL1, TEL2PRE, TEL2, TEL3PRE, TEL3, CUIT, IVA, OBS, ALTA)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "isssisssssssssssss", $codigo, $nombre, $calle, $nro, $piso, $dto, $ldad, $cpa, 
                                                $tel1pre, $tel1, $tel2pre, $tel2, $tel3pre, $tel3, 
                                                $cuit, $iva, $obs, $alta);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: index.php?msg=Cliente agregado con éxito");
} else {
    echo "Error al guardar el cliente: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
