<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';

$cliente = isset($_GET['codigo']) ? $_GET['codigo'] : null;
if (!$cliente) {
    die("<p class='text-center'>No se especific贸 un cliente.</p>");
}
?>

<div class="container">
    <h2>Agregar Nuevo Servicio</h2>
    <form id="form-nuevo-servicio" action="../controllers/serviciosController.php" method="POST">
        <input type="hidden" name="accion" value="agregar">
        <input type="hidden" name="cliente" value="<?= htmlspecialchars($cliente) ?>">

        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required>

        <label for="tecnico">T茅cnico:</label>
        <input type="text" id="tecnico" name="tecnico" required>

        <label for="obscli">Observaciones Cliente:</label>
        <textarea id="obscli" name="obscli"></textarea>

        <label for="obstec">C贸digo T茅cnico:</label>
        <textarea id="obstec" name="obstec"></textarea>

        <label for="importe">Importe:</label>
        <input type="number" id="importe" name="importe" step="0.01">

        <button type="submit" class="button">Guardar Servicio</button>
        <a href="cliente.php?codigo=<?= htmlspecialchars($cliente) ?>" class="button">Cancelar</a>
    </form>
</div>

<script>
document.getElementById("form-nuevo-servicio").addEventListener("submit", function(e) {
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
            alert(data.error || "Error al agregar el servicio");
        }
    })
    .catch(error => {
        console.error("Error agregando servicio:", error);
        alert("Error al agregar el servicio");
    });
});
</script>

<?php include '../includes/footer.php'; ?>