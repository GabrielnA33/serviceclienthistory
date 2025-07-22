<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../config/database.php';

$conexion = conectarDB();
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    die("<p class='text-center'>Servicio no encontrado.</p>");
}

$query = "SELECT * FROM servicios WHERE Id = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$servicio = mysqli_fetch_assoc($resultado);

if (!$servicio) {
    die("<p class='text-center'>Servicio no encontrado.</p>");
}

$codigoCliente = $servicio['CLIENTE'];
mysqli_close($conexion);
?>

<div class="container">
    <h2>Editar Servicio</h2>
    <form id="form-editar-servicio">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id" value="<?= htmlspecialchars($servicio['Id']) ?>">
        <input type="hidden" name="cliente" value="<?= htmlspecialchars($servicio['CLIENTE']) ?>">

        <div class="form-group">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($servicio['FECHA']) ?>" required>
        </div>

        <div class="form-group">
            <label for="tecnico">Técnico:</label>
            <input type="text" id="tecnico" name="tecnico" value="<?= htmlspecialchars($servicio['TECNOM'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="obscli">Observaciones Cliente:</label>
            <textarea id="obscli" name="obscli" rows="3"><?= htmlspecialchars($servicio['OBSCLI'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="obstec">Código Técnico:</label>
            <textarea id="obstec" name="obstec" rows="3"><?= htmlspecialchars($servicio['OBSTEC'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="importe">Importe:</label>
            <input type="number" id="importe" name="importe" step="0.01" value="<?= htmlspecialchars($servicio['IMPORTE'] ?? '') ?>">
        </div>

        <div class="modal-buttons">
            <button type="submit" class="button button-primary">Guardar Cambios</button>
            <a href="cliente.php?codigo=<?= htmlspecialchars($servicio['CLIENTE']) ?>" class="button button-secondary">Cancelar</a>
        </div>
    </form>

    <form id="form-eliminar-servicio" onsubmit="return confirm('¿Está seguro de eliminar este servicio?');">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" value="<?= htmlspecialchars($servicio['Id']) ?>">
        <button type="submit" class="button boton-eliminar">Eliminar Servicio</button>
    </form>
</div>

<script>
document.getElementById("form-editar-servicio").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("../controllers/serviciosController.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = `cliente.php?codigo=${formData.get("cliente")}`;
        } else {
            alert(data.error || "Error al editar el servicio");
        }
    })
    .catch(error => {
        console.error("Error editando servicio:", error);
        alert("Error al editar el servicio");
    });
});

document.getElementById("form-eliminar-servicio").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("../controllers/serviciosController.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = `cliente.php?codigo=<?= htmlspecialchars($servicio['CLIENTE']) ?>`;
        } else {
            alert(data.error || "Error al eliminar el servicio");
        }
    })
    .catch(error => {
        console.error("Error eliminando servicio:", error);
        alert("Error al eliminar el servicio");
    });
});
</script>

<?php include '../includes/footer.php'; ?>