<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
?>

<div class="container">
    <h2>Lista de Clientes</h2>
    <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></p>

    <div class="search-container">
        <input type="text" id="busqueda" placeholder="Buscar cliente..." aria-label="Buscar cliente">
        <a href="nuevo-cliente.php" class="button-add-client">Agregar Cliente</a>
    </div>

    <div id="clientes-lista" class="clientes-grid"></div>
</div>

<script src="js/funciones.js"></script>
<?php include '../includes/footer.php'; ?>