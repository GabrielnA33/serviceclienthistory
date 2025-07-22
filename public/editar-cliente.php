<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../config/database.php';

$conexion = conectarDB();
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : null;

if (!$codigo) {
    die("<p class='text-center'>Cliente no encontrado.</p>");
}

// Obtener los datos del cliente
$query = "SELECT * FROM clientes WHERE CODIGO = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $codigo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($resultado);

if (!$cliente) {
    die("<p class='text-center'>Cliente no encontrado.</p>");
}

mysqli_close($conexion);
?>

<div class="container">
    <h2>Editar Cliente</h2>
    <form action="procesar-edicion-cliente.php" method="POST">
        <input type="hidden" name="codigo" value="<?= $cliente['CODIGO'] ?>">

        <label for="nombre">Nombre del Cliente:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['NOMBRE']) ?>" required>

        <label for="calle">Calle y Número:</label>
        <div class="input-group">
            <input type="text" name="calle" value="<?= htmlspecialchars($cliente['CALLE']) ?>" placeholder="Calle">
            <input type="text" name="nro" value="<?= htmlspecialchars($cliente['NRO']) ?>" placeholder="N°">
        </div>

        <label for="localidad">Localidad:</label>
        <input type="text" name="localidad" value="<?= htmlspecialchars($cliente['LDAD']) ?>">

        <label for="cpa">Código Postal:</label>
        <input type="text" name="cpa" value="<?= htmlspecialchars($cliente['CPA']) ?>">

        <!-- Switch para Piso y Departamento -->
        <div class="switch-container">
            <label for="switch-piso">¿Tiene Piso y Departamento?</label>
            <input type="checkbox" id="switch-piso" <?= !empty($cliente['PISO']) || !empty($cliente['DTO']) ? 'checked' : '' ?>>
        </div>
        <div id="piso-depto" class="<?= empty($cliente['PISO']) && empty($cliente['DTO']) ? 'hidden' : '' ?>">
            <label for="piso">Piso:</label>
            <input type="text" name="piso" value="<?= htmlspecialchars($cliente['PISO']) ?>">

            <label for="dto">Departamento:</label>
            <input type="text" name="dto" value="<?= htmlspecialchars($cliente['DTO']) ?>">
        </div>

        <!-- Switch para Teléfonos -->
        <div class="switch-container">
            <label for="switch-tel">¿Editar teléfonos?</label>
            <input type="checkbox" id="switch-tel" checked>
        </div>
        <div id="telefonos">
            <label for="tel1pre">Prefijo Teléfono 1:</label>
            <input type="text" name="tel1pre" value="<?= htmlspecialchars($cliente['TEL1PRE']) ?>" placeholder="Ej: 011">
            <label for="tel1">Teléfono 1:</label>
            <input type="text" name="tel1" value="<?= htmlspecialchars($cliente['TEL1']) ?>">

            <div class="switch-container">
                <label for="switch-tel2">¿Agregar segundo teléfono?</label>
                <input type="checkbox" id="switch-tel2" <?= !empty($cliente['TEL2']) ? 'checked' : '' ?>>
            </div>
            <div id="tel2-container" class="<?= empty($cliente['TEL2']) ? 'hidden' : '' ?>">
                <label for="tel2pre">Prefijo Teléfono 2:</label>
                <input type="text" name="tel2pre" value="<?= htmlspecialchars($cliente['TEL2PRE']) ?>" placeholder="Ej: 011">
                <label for="tel2">Teléfono 2:</label>
                <input type="text" name="tel2" value="<?= htmlspecialchars($cliente['TEL2']) ?>">

                <div class="switch-container">
                    <label for="switch-tel3">¿Agregar tercer teléfono?</label>
                    <input type="checkbox" id="switch-tel3" <?= !empty($cliente['TEL3']) ? 'checked' : '' ?>>
                </div>
                <div id="tel3-container" class="<?= empty($cliente['TEL3']) ? 'hidden' : '' ?>">
                    <label for="tel3pre">Prefijo Teléfono 3:</label>
                    <input type="text" name="tel3pre" value="<?= htmlspecialchars($cliente['TEL3PRE']) ?>" placeholder="Ej: 011">
                    <label for="tel3">Teléfono 3:</label>
                    <input type="text" name="tel3" value="<?= htmlspecialchars($cliente['TEL3']) ?>">
                </div>
            </div>
        </div>

        <!-- Switch para CUIT e IVA -->
        <div class="switch-container">
            <label for="switch-cuit">¿Editar CUIT?</label>
            <input type="checkbox" id="switch-cuit" <?= !empty($cliente['CUIT']) ? 'checked' : '' ?>>
        </div>
        <div id="cuit-container" class="<?= empty($cliente['CUIT']) ? 'hidden' : '' ?>">
            <label for="cuit">CUIT:</label>
            <input type="text" name="cuit" value="<?= htmlspecialchars($cliente['CUIT']) ?>">

            <label for="iva">Condición de IVA:</label>
            <select name="iva">
                <option value="">Seleccione</option>
                <option value="RI" <?= $cliente['IVA'] == 'RI' ? 'selected' : '' ?>>Responsable Inscripto</option>
                <option value="MT" <?= $cliente['IVA'] == 'MT' ? 'selected' : '' ?>>Monotributista</option>
                <option value="EX" <?= $cliente['IVA'] == 'EX' ? 'selected' : '' ?>>Exento</option>
                <option value="CF" <?= $cliente['IVA'] == 'CF' ? 'selected' : '' ?>>Consumidor Final</option>
            </select>
        </div>

        <button type="submit">Guardar Cambios</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function toggleVisibility(switchId, targetId) {
        document.getElementById(switchId).addEventListener("change", function() {
            document.getElementById(targetId).classList.toggle("hidden", !this.checked);
        });
    }

    toggleVisibility("switch-piso", "piso-depto");
    toggleVisibility("switch-tel2", "tel2-container");
    toggleVisibility("switch-tel3", "tel3-container");
    toggleVisibility("switch-cuit", "cuit-container");
});
</script>

<?php include '../includes/footer.php'; ?>
