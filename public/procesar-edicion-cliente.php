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
$loc = limpiarEntrada($_POST['localidad']);
$cpa = limpiarEntrada($_POST['cpa']);
$piso = !empty($_POST['piso']) ? limpiarEntrada($_POST['piso']) : null;
$dto = limpiarEntrada($_POST['dto']);
$tel1pre = limpiarEntrada($_POST['tel1pre']);
$tel1 = limpiarEntrada($_POST['tel1']);
$tel2pre = limpiarEntrada($_POST['tel2pre']);
$tel2 = limpiarEntrada($_POST['tel2']);
$tel3pre = limpiarEntrada($_POST['tel3pre']);
$tel3 = limpiarEntrada($_POST['tel3']);
$cuit = limpiarEntrada($_POST['cuit']);
$iva = limpiarEntrada($_POST['iva']);
$obs = limpiarEntrada($_POST['obs']);

// Validar si el cliente existe antes de actualizarlo
$verificarQuery = "SELECT * FROM clientes WHERE CODIGO = ?";
$stmtVerificar = mysqli_prepare($conexion, $verificarQuery);
mysqli_stmt_bind_param($stmtVerificar, "i", $codigo);
mysqli_stmt_execute($stmtVerificar);
$resultadoVerificar = mysqli_stmt_get_result($stmtVerificar);

if (mysqli_num_rows($resultadoVerificar) === 0) {
    die("❌ Error: El cliente no existe.");
}

// Actualizar en la base de datos
$query = "UPDATE clientes SET 
          NOMBRE = ?, CALLE = ?, NRO = ?, PISO = ?, DTO = ?, LDAD = ?, CPA = ?, 
          TEL1PRE = ?, TEL1 = ?, TEL2PRE = ?, TEL2 = ?, TEL3PRE = ?, TEL3 = ?, 
          CUIT = ?, IVA = ?, OBS = ?
          WHERE CODIGO = ?";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "ssssssssssssssssi", 
                        $nombre, $calle, $nro, $piso, $dto, $ldad, $cpa, 
                        $tel1pre, $tel1, $tel2pre, $tel2, $tel3pre, $tel3, 
                        $cuit, $iva, $obs, $codigo);

if (mysqli_stmt_execute($stmt)) {
    header("Location: cliente.php?codigo=$codigo&msg=Cliente actualizado con éxito");
    exit();
} else {
    echo "❌ Error al actualizar el cliente: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
