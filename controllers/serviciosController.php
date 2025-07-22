<?php
header("Content-Type: application/json");
include '../config/database.php';

$conexion = conectarDB();
mysqli_set_charset($conexion, "utf8mb4");

// ========================
// FUNCIÓN PARA OBTENER SERVICIOS DE UN CLIENTE
// ========================
if (isset($_GET['codigo'])) {
    $codigo = mysqli_real_escape_string($conexion, trim($_GET['codigo']));

    $query = "SELECT Id AS id, FECHA AS fecha, CLIENTE AS cliente, CLINOM AS clinom, CALLE AS calle, NRO AS nro, LDAD AS ldad,
                     TECNOM AS tecnico, OBSCLI AS obscli, OBSTEC AS obstec, IMPORTE AS importe
              FROM servicios 
              WHERE CLIENTE = ? 
              ORDER BY FECHA DESC";

    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "s", $codigo);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $servicios = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $servicios[] = $fila;
    }

    echo json_encode($servicios ?: ["mensaje" => "No se encontraron servicios para este cliente."]);
    mysqli_close($conexion);
    exit;
}

// ========================
// FUNCIÓN PARA OBTENER UN SERVICIO ESPECÍFICO
// ========================
if (isset($_GET['id']) && !isset($_GET['codigo'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);

    $query = "SELECT Id AS id, FECHA AS fecha, CLIENTE AS cliente, CLINOM AS clinom, CALLE AS calle, NRO AS nro, LDAD AS ldad,
                     TECNOM AS tecnico, OBSCLI AS obscli, OBSTEC AS obstec, IMPORTE AS importe
              FROM servicios 
              WHERE Id = ? 
              LIMIT 1";

    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $servicio = mysqli_fetch_assoc($resultado);

    echo json_encode($servicio ?: ["error" => "Servicio no encontrado."]);
    mysqli_close($conexion);
    exit;
}

// ========================
// FUNCIÓN PARA AGREGAR, EDITAR O ELIMINAR SERVICIO
// ========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $cliente = mysqli_real_escape_string($conexion, $_POST['cliente']);
    $fecha = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $tecnico = mysqli_real_escape_string($conexion, $_POST['tecnico']);
    $obscli = mysqli_real_escape_string($conexion, $_POST['obscli']) ?: '';
    $obstec = mysqli_real_escape_string($conexion, $_POST['obstec']) ?: '';
    $importe = mysqli_real_escape_string($conexion, $_POST['importe']) ?: null;

    if ($accion === "agregar") {
        // Obtener datos del cliente desde la tabla clientes, incluyendo LDAD
        $queryCliente = "SELECT NOMBRE AS clinom, CALLE AS calle, NRO AS nro, COALESCE(LDAD, LOC) AS ldad FROM clientes WHERE CODIGO = ?";
        $stmtCliente = mysqli_prepare($conexion, $queryCliente);
        mysqli_stmt_bind_param($stmtCliente, "s", $cliente);
        mysqli_stmt_execute($stmtCliente);
        $resultadoCliente = mysqli_stmt_get_result($stmtCliente);
        $datosCliente = mysqli_fetch_assoc($resultadoCliente);

        if (!$datosCliente) {
            echo json_encode(["error" => "Cliente no encontrado."]);
            mysqli_close($conexion);
            exit;
        }

        $clinom = $datosCliente['clinom'];
        $calle = $datosCliente['calle'];
        $nro = $datosCliente['nro'];
        $ldad = $datosCliente['ldad'];

        // Insertar el servicio con datos adicionales del cliente, incluyendo LDAD
        $query = "INSERT INTO servicios (FECHA, CLIENTE, CLINOM, CALLE, NRO, LDAD, TECNOM, OBSCLI, OBSTEC, IMPORTE) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $query);
        if (!$stmt) {
            echo json_encode(["error" => "Error preparando la consulta: " . mysqli_error($conexion)]);
            mysqli_close($conexion);
            exit;
        }
        mysqli_stmt_bind_param($stmt, "ssssssssss", $fecha, $cliente, $clinom, $calle, $nro, $ldad, $tecnico, $obscli, $obstec, $importe);

        if (mysqli_stmt_execute($stmt)) {
            $nuevoId = mysqli_insert_id($conexion);
            echo json_encode([
                "success" => true,
                "message" => "Servicio agregado correctamente",
                "id" => $nuevoId,
                "fecha" => $fecha,
                "cliente" => $cliente,
                "clinom" => $clinom,
                "calle" => $calle,
                "nro" => $nro,
                "ldad" => $ldad,
                "tecnico" => $tecnico,
                "obscli" => $obscli,
                "obstec" => $obstec,
                "importe" => $importe
            ]);
        } else {
            echo json_encode(["error" => "Error al agregar servicio: " . mysqli_error($conexion)]);
        }
    } elseif ($accion === "editar") {
        $id = mysqli_real_escape_string($conexion, $_POST['id']);
        $queryCliente = "SELECT NOMBRE AS clinom, CALLE AS calle, NRO AS nro, COALESCE(LDAD, LOC) AS ldad FROM clientes WHERE CODIGO = ?";
        $stmtCliente = mysqli_prepare($conexion, $queryCliente);
        mysqli_stmt_bind_param($stmtCliente, "s", $cliente);
        mysqli_stmt_execute($stmtCliente);
        $resultadoCliente = mysqli_stmt_get_result($stmtCliente);
        $datosCliente = mysqli_fetch_assoc($resultadoCliente);

        if (!$datosCliente) {
            echo json_encode(["error" => "Cliente no encontrado."]);
            mysqli_close($conexion);
            exit;
        }

        $clinom = $datosCliente['clinom'];
        $calle = $datosCliente['calle'];
        $nro = $datosCliente['nro'];
        $ldad = $datosCliente['ldad'];

        $query = "UPDATE servicios SET FECHA = ?, CLIENTE = ?, CLINOM = ?, CALLE = ?, NRO = ?, LDAD = ?, TECNOM = ?, OBSCLI = ?, OBSTEC = ?, IMPORTE = ? WHERE Id = ?";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssssi", $fecha, $cliente, $clinom, $calle, $nro, $ldad, $tecnico, $obscli, $obstec, $importe, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Servicio editado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al editar servicio: " . mysqli_error($conexion)]);
        }
    } elseif ($accion === "eliminar") {
        $id = mysqli_real_escape_string($conexion, $_POST['id']);
        $query = "DELETE FROM servicios WHERE Id = ?";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Servicio eliminado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al eliminar servicio: " . mysqli_error($conexion)]);
        }
    }

    mysqli_close($conexion);
    exit;
}

// Compatibilidad con eliminación vía GET
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $query = "DELETE FROM servicios WHERE Id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Servicio eliminado correctamente"]);
    } else {
        echo json_encode(["error" => "Error al eliminar servicio: " . mysqli_error($conexion)]);
    }

    mysqli_close($conexion);
    exit;
}

echo json_encode(["error" => "Acción no válida"]);
mysqli_close($conexion);
?>