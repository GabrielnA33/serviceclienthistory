<?php
header("Content-Type: application/json");
include '../config/database.php';

$conexion = conectarDB();
mysqli_set_charset($conexion, "utf8mb4");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$limite = isset($_GET['limite']) ? max(1, (int)$_GET['limite']) : 1000;
$offset = ($pagina - 1) * $limite;

$query = "SELECT c.*, COALESCE(c.LDAD, c.LOC) AS LOCALIDAD FROM clientes c";
$params = [];
$types = "";

if (!empty($search)) {
    $searchTerms = explode(" ", $search);
    $searchConditions = [];

    foreach ($searchTerms as $term) {
        $searchConditions[] = "(LOWER(c.NOMBRE) LIKE LOWER(?) OR LOWER(c.LOC) LIKE LOWER(?) OR 
                               LOWER(c.LDAD) LIKE LOWER(?) OR LOWER(c.CALLE) LIKE LOWER(?) OR 
                               LOWER(c.NRO) LIKE LOWER(?) OR LOWER(c.CODIGO) LIKE LOWER(?) OR 
                               LOWER(c.TEL1) LIKE LOWER(?) OR LOWER(c.TEL2) LIKE LOWER(?) OR 
                               LOWER(c.TEL3) LIKE LOWER(?))";
        for ($i = 0; $i < 9; $i++) {
            $params[] = "%$term%";
            $types .= "s";
        }
    }

    $query .= " WHERE " . implode(" AND ", $searchConditions);
}

$query .= " ORDER BY c.CODIGO DESC LIMIT ? OFFSET ?";
$params[] = $limite;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$clientes = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $fila["LOCALIDAD"] = !empty($fila["LOCALIDAD"]) ? $fila["LOCALIDAD"] : "No disponible";

    $queryObstec = "SELECT OBSTEC FROM servicios WHERE CLIENTE = ? ORDER BY STR_TO_DATE(FECHA, '%d/%m/%Y') DESC LIMIT 1";
    $stmtObstec = mysqli_prepare($conexion, $queryObstec);
    mysqli_stmt_bind_param($stmtObstec, "s", $fila["CODIGO"]);
    mysqli_stmt_execute($stmtObstec);
    $resultadoObstec = mysqli_stmt_get_result($stmtObstec);

    if ($filaObstec = mysqli_fetch_assoc($resultadoObstec)) {
        $fila["OBSTEC"] = !empty($filaObstec["OBSTEC"]) ? $filaObstec["OBSTEC"] : "No disponible";
    } else {
        $fila["OBSTEC"] = "No disponible";
    }

    $clientes[] = $fila;
}

// ========================
// FUNCIÓN PARA ELIMINAR CLIENTE
// ========================
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['codigo'])) {
    $codigo = mysqli_real_escape_string($conexion, $_GET['codigo']);

    // Verificar si el cliente existe
    $verificarQuery = "SELECT * FROM clientes WHERE CODIGO = ?";
    $stmtVerificar = mysqli_prepare($conexion, $verificarQuery);
    mysqli_stmt_bind_param($stmtVerificar, "i", $codigo);
    mysqli_stmt_execute($stmtVerificar);
    $resultadoVerificar = mysqli_stmt_get_result($stmtVerificar);

    if (mysqli_num_rows($resultadoVerificar) === 0) {
        echo json_encode(["error" => "❌ El cliente no existe en la base de datos."]);
        exit;
    }

    // Eliminar los servicios relacionados antes de eliminar el cliente (si existen)
    $queryEliminarServicios = "DELETE FROM servicios WHERE CLIENTE = ?";
    $stmtEliminarServicios = mysqli_prepare($conexion, $queryEliminarServicios);
    mysqli_stmt_bind_param($stmtEliminarServicios, "i", $codigo);
    mysqli_stmt_execute($stmtEliminarServicios);

    // Eliminar el cliente
    $queryEliminar = "DELETE FROM clientes WHERE CODIGO = ?";
    $stmtEliminar = mysqli_prepare($conexion, $queryEliminar);
    mysqli_stmt_bind_param($stmtEliminar, "i", $codigo);

    if (mysqli_stmt_execute($stmtEliminar)) {
        echo json_encode(["success" => true, "message" => "✅ Cliente eliminado correctamente."]);
    } else {
        echo json_encode(["error" => "❌ Error al eliminar el cliente."]);
    }

    mysqli_close($conexion);
    exit;
}

echo json_encode($clientes);
mysqli_close($conexion);
?>
