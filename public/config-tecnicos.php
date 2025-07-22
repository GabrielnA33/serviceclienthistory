<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include '../includes/header.php';
include '../config/database.php';

$conexion = conectarDB();

// Manejo de acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $tecnum = mysqli_real_escape_string($conexion, trim($_POST['tecnum']));
    $tecnom = mysqli_real_escape_string($conexion, trim($_POST['tecnom']));
    $calletec = mysqli_real_escape_string($conexion, trim($_POST['calletec']) ?: '');
    $nrotec = mysqli_real_escape_string($conexion, trim($_POST['nrotec']) ?: '');
    $teltec = mysqli_real_escape_string($conexion, trim($_POST['teltec']) ?: '');
    $dnitec = mysqli_real_escape_string($conexion, trim($_POST['dnitec']) ?: '');
    $teccom = !empty($_POST['teccom']) ? floatval($_POST['teccom']) : 0.00;

    if ($_POST['accion'] === 'agregar') {
        $query = "INSERT INTO tecnicos (TECNUM, TECNOM, CALLETEC, NROTEC, TELTEC, DNITEC, TECCOM) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssd", $tecnum, $tecnom, $calletec, $nrotec, $teltec, $dnitec, $teccom);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Técnico agregado correctamente";
            } else {
                $error = "Error al agregar técnico: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparando la consulta: " . mysqli_error($conexion);
        }
    } elseif ($_POST['accion'] === 'editar') {
        $tecnum_original = mysqli_real_escape_string($conexion, trim($_POST['tecnum_original']));
        $query = "UPDATE tecnicos SET TECNUM = ?, TECNOM = ?, CALLETEC = ?, NROTEC = ?, TELTEC = ?, DNITEC = ?, TECCOM = ? WHERE TECNUM = ?";
        $stmt = mysqli_prepare($conexion, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssds", $tecnum, $tecnom, $calletec, $nrotec, $teltec, $dnitec, $teccom, $tecnum_original);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Técnico editado correctamente";
            } else {
                $error = "Error al editar técnico: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparando la consulta: " . mysqli_error($conexion);
        }
    } elseif ($_POST['accion'] === 'eliminar') {
        $query = "DELETE FROM tecnicos WHERE TECNUM = ?";
        $stmt = mysqli_prepare($conexion, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $tecnum);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Técnico eliminado correctamente";
            } else {
                $error = "Error al eliminar técnico: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparando la consulta: " . mysqli_error($conexion);
        }
    }
}

$mensaje = isset($mensaje) ? $mensaje : (isset($_GET['msg']) ? $_GET['msg'] : "");
$error = isset($error) ? $error : "";

$queryTecnicos = "SELECT * FROM tecnicos ORDER BY TECNOM ASC";
$resultadoTecnicos = mysqli_query($conexion, $queryTecnicos);
?>

<div class="container">
    <h2>Configuración de Técnicos</h2>
    <a href="index.php" class="button-header">Volver al Inicio</a>

    <?php if ($mensaje) echo "<p class='mensaje-exito'>$mensaje</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <h3>Agregar Técnico</h3>
    <form method="POST" id="form-agregar-tecnico" class="modal-form">
        <input type="hidden" name="accion" value="agregar">
        <div class="form-group">
            <label for="tecnum">Número de Técnico:</label>
            <input type="text" id="tecnum" name="tecnum" required maxlength="10" placeholder="Ej: T0001">
        </div>
        <div class="form-group">
            <label for="tecnom">Nombre:</label>
            <input type="text" id="tecnom" name="tecnom" required maxlength="100">
        </div>
        <div class="form-group">
            <label for="calletec">Calle:</label>
            <input type="text" id="calletec" name="calletec" maxlength="255">
        </div>
        <div class="form-group">
            <label for="nrotec">Número:</label>
            <input type="text" id="nrotec" name="nrotec" maxlength="10">
        </div>
        <div class="form-group">
            <label for="teltec">Teléfono:</label>
            <div style="display: flex; gap: 10px;">
                <select id="prefijo" name="prefijo">
                    <option value="+54">+54 (Argentina)</option>
                    <option value="+1">+1 (EE.UU.)</option>
                    <option value="+55">+55 (Brasil)</option>
                </select>
                <input type="text" id="teltec" name="teltec" maxlength="20" placeholder="Ej: 9 011-5524-3969">
            </div>
        </div>
        <div class="form-group">
            <label for="dnitec">DNI:</label>
            <input type="text" id="dnitec" name="dnitec" maxlength="12" placeholder="Ej: 12.345.678">
        </div>
        <div class="form-group">
            <label for="teccom">Comisión (%):</label>
            <input type="number" id="teccom" name="teccom" step="0.01" min="0" max="100" placeholder="Ej: 15.50">
        </div>
        <button type="submit" class="button button-primary">Guardar Técnico</button>
    </form>

    <h3>Técnicos Registrados</h3>
    <div class="clientes-grid">
        <?php while ($tecnico = mysqli_fetch_assoc($resultadoTecnicos)): ?>
            <div class="cliente-card">
                <h3><?php echo htmlspecialchars($tecnico['TECNOM']); ?></h3>
                <p><strong>Número:</strong> <?php echo htmlspecialchars($tecnico['TECNUM']); ?></p>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($tecnico['CALLETEC']) . " " . htmlspecialchars($tecnico['NROTEC']) ?: "No especificada"; ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($tecnico['TELTEC']) ?: "No especificado"; ?></p>
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($tecnico['DNITEC']) ?: "No especificado"; ?></p>
                <p><strong>Comisión:</strong> <?php echo htmlspecialchars($tecnico['TECCOM']) . "%"; ?></p>
                <button class="button" onclick="editarTecnico('<?php echo htmlspecialchars($tecnico['TECNUM']); ?>', '<?php echo htmlspecialchars($tecnico['TECNOM']); ?>', '<?php echo htmlspecialchars($tecnico['CALLETEC']); ?>', '<?php echo htmlspecialchars($tecnico['NROTEC']); ?>', '<?php echo htmlspecialchars($tecnico['TELTEC']); ?>', '<?php echo htmlspecialchars($tecnico['DNITEC']); ?>', '<?php echo htmlspecialchars($tecnico['TECCOM']); ?>')">Editar</button>
                <button class="button boton-eliminar" onclick="eliminarTecnico('<?php echo htmlspecialchars($tecnico['TECNUM']); ?>', '<?php echo htmlspecialchars($tecnico['TECNOM']); ?>')">Eliminar</button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    document.getElementById("dnitec").addEventListener("input", function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 8) value = value.slice(0, 8);
        if (value.length > 2 && value.length <= 5) {
            this.value = value.slice(0, 2) + '.' + value.slice(2);
        } else if (value.length > 5) {
            this.value = value.slice(0, 2) + '.' + value.slice(2, 5) + '.' + value.slice(5);
        } else {
            this.value = value;
        }
    });

    document.getElementById("teltec").addEventListener("input", function(e) {
        let value = this.value.replace(/\D/g, '');
        let prefijo = document.getElementById("prefijo").value;
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 1 && value.length <= 4) {
            this.value = `${prefijo} 9 ${value}`;
        } else if (value.length > 4 && value.length <= 8) {
            this.value = `${prefijo} 9 ${value.slice(0, 4)}-${value.slice(4)}`;
        } else if (value.length > 8) {
            this.value = `${prefijo} 9 ${value.slice(0, 4)}-${value.slice(4, 8)}-${value.slice(8)}`;
        } else {
            this.value = prefijo + (value ? " " + value : "");
        }
    });

    document.getElementById("prefijo").addEventListener("change", function() {
        let currentValue = document.getElementById("teltec").value.replace(/\D/g, '');
        document.getElementById("teltec").value = this.value + (currentValue ? " " + currentValue : "");
    });

    function editarTecnico(tecnum, tecnom, calletec, nrotec, teltec, dnitec, teccom) {
        document.getElementById("form-agregar-tecnico").innerHTML = `
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="tecnum_original" value="${tecnum}">
            <div class="form-group">
                <label for="tecnum">Número de Técnico:</label>
                <input type="text" id="tecnum" name="tecnum" required maxlength="10" value="${tecnum}">
            </div>
            <div class="form-group">
                <label for="tecnom">Nombre:</label>
                <input type="text" id="tecnom" name="tecnom" required maxlength="100" value="${tecnom}">
            </div>
            <div class="form-group">
                <label for="calletec">Calle:</label>
                <input type="text" id="calletec" name="calletec" maxlength="255" value="${calletec}">
            </div>
            <div class="form-group">
                <label for="nrotec">Número:</label>
                <input type="text" id="nrotec" name="nrotec" maxlength="10" value="${nrotec}">
            </div>
            <div class="form-group">
                <label for="teltec">Teléfono:</label>
                <div style="display: flex; gap: 10px;">
                    <select id="prefijo" name="prefijo">
                        <option value="+54" ${teltec.startsWith('+54') ? 'selected' : ''}>+54 (Argentina)</option>
                        <option value="+1" ${teltec.startsWith('+1') ? 'selected' : ''}>+1 (EE.UU.)</option>
                        <option value="+55" ${teltec.startsWith('+55') ? 'selected' : ''}>+55 (Brasil)</option>
                    </select>
                    <input type="text" id="teltec" name="teltec" maxlength="20" value="${teltec}">
                </div>
            </div>
            <div class="form-group">
                <label for="dnitec">DNI:</label>
                <input type="text" id="dnitec" name="dnitec" maxlength="12" value="${dnitec}">
            </div>
            <div class="form-group">
                <label for="teccom">Comisión (%):</label>
                <input type="number" id="teccom" name="teccom" step="0.01" min="0" max="100" value="${teccom}">
            </div>
            <button type="submit" class="button button-primary">Actualizar Técnico</button>
            <button type="button" class="button button-secondary" onclick="location.reload()">Cancelar</button>
        `;

        document.getElementById("dnitec").addEventListener("input", function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 8) value = value.slice(0, 8);
            if (value.length > 2 && value.length <= 5) {
                this.value = value.slice(0, 2) + '.' + value.slice(2);
            } else if (value.length > 5) {
                this.value = value.slice(0, 2) + '.' + value.slice(2, 5) + '.' + value.slice(5);
            } else {
                this.value = value;
            }
        });

        document.getElementById("teltec").addEventListener("input", function(e) {
            let value = this.value.replace(/\D/g, '');
            let prefijo = document.getElementById("prefijo").value;
            if (value.length > 11) value = value.slice(0, 11);
            if (value.length > 1 && value.length <= 4) {
                this.value = `${prefijo} 9 ${value}`;
            } else if (value.length > 4 && value.length <= 8) {
                this.value = `${prefijo} 9 ${value.slice(0, 4)}-${value.slice(4)}`;
            } else if (value.length > 8) {
                this.value = `${prefijo} 9 ${value.slice(0, 4)}-${value.slice(4, 8)}-${value.slice(8)}`;
            } else {
                this.value = prefijo + (value ? " " + value : "");
            }
        });

        document.getElementById("prefijo").addEventListener("change", function() {
            let currentValue = document.getElementById("teltec").value.replace(/\D/g, '');
            document.getElementById("teltec").value = this.value + (currentValue ? " " + currentValue : "");
        });
    }

    function eliminarTecnico(tecnum, tecnom) {
        if (confirm(`¿Está seguro de eliminar al técnico ${tecnom}?`)) {
            const formData = new FormData();
            formData.append("accion", "eliminar");
            formData.append("tecnum", tecnum);

            fetch(window.location.href, {
                method: "POST",
                body: formData
            })
            .then(response => response.text()) // Cambié a text() para depurar
            .then(data => {
                console.log("Respuesta del servidor:", data); // Depuración
                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        alert(jsonData.message);
                        location.reload();
                    } else {
                        alert(jsonData.error || "Error al eliminar el técnico");
                    }
                } catch (e) {
                    alert("Error al procesar la respuesta: " + e.message);
                }
            })
            .catch(error => {
                console.error("Error eliminando técnico:", error);
                alert("Error al eliminar el técnico");
            });
        }
    }
</script>

<?php include '../includes/footer.php'; ?>