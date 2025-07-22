<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../config/database.php';

$conexion = conectarDB();

// Obtener el próximo código autogenerado
$queryCodigo = "SELECT MAX(CODIGO) AS max_codigo FROM clientes";
$resultado = mysqli_query($conexion, $queryCodigo);
$fila = mysqli_fetch_assoc($resultado);
$proximoCodigo = $fila['max_codigo'] + 1;

mysqli_close($conexion);
?>

<div class="container">
    <h2>Agregar Nuevo Cliente</h2>

    <form id="form-cliente" action="procesar-cliente.php" method="POST">
        <label for="nombre">Nombre del Cliente:</label>
        <input type="text" id="nombre" name="nombre" required>

        <label for="codigo">Código del Cliente:</label>
        <input type="text" id="codigo" name="codigo" value="<?php echo $proximoCodigo; ?>" readonly>

        <label for="calle">Calle y Número:</label>
        <div class="input-group">
            <input type="text" id="calle" name="calle" placeholder="Calle">
            <input type="text" id="nro" name="nro" placeholder="N°">
        </div>

        <label for="localidad">Localidad:</label>
        <input type="text" id="localidad" name="localidad">

        <label for="cpa">Código Postal:</label>
        <input type="text" id="cpa" name="cpa">

        <!-- Switch para Piso y Departamento -->
        <div class="switch-container">
            <label for="switch-piso">¿Tiene Piso y Departamento?</label>
            <input type="checkbox" id="switch-piso">
        </div>
        <div id="piso-depto" class="hidden">
            <label for="piso">Piso:</label>
            <input type="text" id="piso" name="piso">
            
            <label for="dto">Departamento:</label>
            <input type="text" id="dto" name="dto">
        </div>

       <!-- Switch para Teléfonos -->
<div class="switch-container">
    <label for="switch-tel">¿Agregar teléfono?</label>
    <input type="checkbox" id="switch-tel">
</div>
<div id="telefonos" class="hidden">
    <label for="tel1pre">Prefijo Teléfono 1:</label>
    <input type="text" id="tel1pre" name="tel1pre" placeholder="Ej: 011">

    <label for="tel1">Teléfono 1:</label>
    <input type="text" id="tel1" name="tel1">
    
    <div class="switch-container">
        <label for="switch-tel2">¿Agregar segundo teléfono?</label>
        <input type="checkbox" id="switch-tel2">
    </div>
    <div id="tel2-container" class="hidden">
        <label for="tel2pre">Prefijo Teléfono 2:</label>
        <input type="text" id="tel2pre" name="tel2pre" placeholder="Ej: 011">

        <label for="tel2">Teléfono 2:</label>
        <input type="text" id="tel2" name="tel2">
        
        <div class="switch-container">
            <label for="switch-tel3">¿Agregar tercer teléfono?</label>
            <input type="checkbox" id="switch-tel3">
        </div>
        <div id="tel3-container" class="hidden">
            <label for="tel3pre">Prefijo Teléfono 3:</label>
            <input type="text" id="tel3pre" name="tel3pre" placeholder="Ej: 011">

            <label for="tel3">Teléfono 3:</label>
            <input type="text" id="tel3" name="tel3">
        </div>
    </div>
</div>


        <!-- Switch para CUIT e IVA -->
        <div class="switch-container">
            <label for="switch-cuit">¿Tiene CUIT?</label>
            <input type="checkbox" id="switch-cuit">
        </div>
        <div id="cuit-container" class="hidden">
            <label for="cuit">CUIT:</label>
            <input type="text" id="cuit" name="cuit">
            
            <label for="iva">Condición de IVA:</label>
            <select id="iva" name="iva">
                <option value="">Seleccione</option>
                <option value="RI">Responsable Inscripto</option>
                <option value="MT">Monotributista</option>
                <option value="EX">Exento</option>
                <option value="CF">Consumidor Final</option>
            </select>
        </div>

        <!-- Switch para Observaciones -->
        <div class="switch-container">
            <label for="switch-obs">¿Agregar Observaciones?</label>
            <input type="checkbox" id="switch-obs">
        </div>
        <div id="obs-container" class="hidden">
            <label for="obs">Observaciones:</label>
            <textarea id="obs" name="obs"></textarea>
        </div>

        <button type="submit">Guardar Cliente</button>
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
    toggleVisibility("switch-tel", "telefonos");
    toggleVisibility("switch-tel2", "tel2-container");
    toggleVisibility("switch-tel3", "tel3-container");
    toggleVisibility("switch-cuit", "cuit-container");
    toggleVisibility("switch-obs", "obs-container");
});
</script>

<style>
.hidden { display: none; }
.switch-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
</style>

<?php include '../includes/footer.php'; ?>
