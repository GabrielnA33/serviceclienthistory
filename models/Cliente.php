<?php
require_once '../config/database.php';
$conn = conectarDB();

// Obtener el código del cliente desde la URL
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : null;

if (!$codigo) {
    die("<p style='color:red; text-align:center;'>⚠️ ERROR: No se recibió el código de cliente.</p>");
}

// CONSULTA SQL PARA OBTENER CLIENTE
$sqlCliente = "SELECT * FROM clientes WHERE CODIGO = ?";
$stmtCliente = $conn->prepare($sqlCliente);
$stmtCliente->bind_param("i", $codigo);
$stmtCliente->execute();
$resultCliente = $stmtCliente->get_result();

if ($resultCliente->num_rows === 0) {
    die("<p style='color:red; text-align:center;'>⚠️ ERROR: Cliente no encontrado.</p>");
}

$cliente = $resultCliente->fetch_assoc();

// CONSULTA SQL PARA OBTENER SERVICIOS DEL CLIENTE
$sqlServicios = "SELECT * FROM servicios WHERE CLIENTE = ?";
$stmtServicios = $conn->prepare($sqlServicios);
$stmtServicios->bind_param("i", $codigo);
$stmtServicios->execute();
$resultServicios = $stmtServicios->get_result();

$servicios = [];
if ($resultServicios->num_rows > 0) {
    $servicios = $resultServicios->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Cliente - Rapigas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <h2 class="text-light">Detalles del Cliente</h2>
        <div class="card bg-dark text-white">
            <div class="card-body">
                <h4 class="card-title"><?= htmlspecialchars($cliente['NOMBRE']) ?></h4>
                <p class="card-text"><strong>Dirección:</strong> <?= htmlspecialchars($cliente['CALLE']) ?> <?= htmlspecialchars($cliente['NRO']) ?>, <?= htmlspecialchars($cliente['LDAD']) ?></p>
                <p class="card-text"><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['TEL1']) ?></p>
                <p class="card-text"><strong>CUIT:</strong> <?= htmlspecialchars($cliente['CUIT']) ?></p>
            </div>
        </div>

        <h3 class="mt-4 text-light">Servicios Realizados</h3>
        <?php if (count($servicios) > 0): ?>
            <div class="row">
                <?php foreach ($servicios as $servicio) : ?>
                    <div class="col-md-6">
                        <div class="card mt-3 bg-secondary text-white">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-calendar"></i> Fecha: <?= htmlspecialchars($servicio['FECHA']) ?></h5>
                                <p class="card-text"><strong><i class="bi bi-person"></i> Técnico:</strong> <?= htmlspecialchars($servicio['TECNOM']) ?></p>
                                <p class="card-text"><strong><i class="bi bi-tools"></i> Descripción:</strong> <?= htmlspecialchars($servicio['OBSCLI']) ?></p>
                                <p class="card-text"><strong><i class="bi bi-cash"></i> Monto:</strong> $<?= number_format($servicio['IMPORTE'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-danger mt-3">⚠️ No hay servicios registrados para este cliente.</p>
        <?php endif; ?>

        <a href="index.php" class="btn btn-primary mt-4"><i class="bi bi-arrow-left"></i> Volver al listado</a>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
