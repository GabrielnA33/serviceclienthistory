<?php 
include '../includes/header.php';
include '../config/database.php';

$mensaje = isset($_GET['msg']) ? $_GET['msg'] : "";

if (!isset($_GET['codigo'])) {
    die("<p class='text-center'>Cliente no encontrado.</p>");
}

$codigo = $_GET['codigo'];
$conexion = conectarDB();

$queryCliente = "SELECT c.*, 
                        COALESCE(c.LDAD, c.LOC) AS LOCALIDAD, 
                        s.OBSTEC
                 FROM clientes c
                 LEFT JOIN (
                     SELECT CLIENTE, OBSTEC
                     FROM servicios
                     WHERE CLIENTE = ?
                     ORDER BY FECHA DESC
                     LIMIT 1
                 ) s ON c.CODIGO = s.CLIENTE
                 WHERE c.CODIGO = ?";

$stmt = mysqli_prepare($conexion, $queryCliente);
mysqli_stmt_bind_param($stmt, "ss", $codigo, $codigo);
mysqli_stmt_execute($stmt);
$resultadoCliente = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($resultadoCliente);

if (!$cliente) {
    die("<p class='text-center'>Cliente no encontrado.</p>");
}

// Filtrar técnicos: solo TECNUM = '19' o técnicos cuyos TECNOM no estén en servicios antes del 20/02/2025
$queryTecnicos = "SELECT TECNOM 
                  FROM tecnicos 
                  WHERE TECNUM = '19' 
                  OR TECNOM NOT IN (
                      SELECT DISTINCT TECNOM 
                      FROM servicios 
                      WHERE FECHA < '2025-02-20' AND TECNOM IS NOT NULL
                  )
                  ORDER BY TECNOM ASC";
$resultadoTecnicos = mysqli_query($conexion, $queryTecnicos);
mysqli_data_seek($resultadoTecnicos, 0);

mysqli_close($conexion);
?>

<div class="container">
    <div id="mensaje-cliente"><?php echo !empty($mensaje) ? "<p class='mensaje-exito'>$mensaje</p>" : ""; ?></div>

    <a href="nuevo-cliente.php" class="button-header">Agregar Cliente</a>
    <div class="cliente-card">
        <h2 id="cliente-nombre"><?php echo htmlspecialchars($cliente['NOMBRE']); ?></h2>
        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($cliente['CALLE']) . " " . htmlspecialchars($cliente['NRO']); ?></p>
        <p><strong>Localidad:</strong> <?php echo htmlspecialchars($cliente['LOCALIDAD']); ?></p>
        <p><strong>OBSTEC:</strong> <?php echo !empty($cliente['OBSTEC']) ? htmlspecialchars($cliente['OBSTEC']) : "No disponible"; ?></p>
        <p id="cliente-telefono">
            <strong>Teléfono:</strong> 
            <?php 
                $telefonos = array_filter([$cliente['TEL1'], $cliente['TEL2'], $cliente['TEL3']]);
                echo !empty($telefonos) ? '<span>' . htmlspecialchars(implode(" / ", $telefonos)) . '</span>' : "No disponible";
            ?>
        </p>
    </div>

    <div class="botones-acciones">
        <a href="editar-cliente.php?codigo=<?= $cliente['CODIGO'] ?>" class="button-header">Editar Cliente</a>
        <button class="button-header" onclick="eliminarCliente(<?= $cliente['CODIGO'] ?>, '<?= htmlspecialchars($cliente['NOMBRE']) ?>')">Eliminar Cliente</button>
    </div>

    <h3>Servicios Realizados</h3>
    <button class="button-add-client" onclick="mostrarModalAgregarServicio(<?= $cliente['CODIGO'] ?>)">Agregar Servicio</button>
    <div id="lista-servicios"></div>
</div>

<div id="modal-servicio" class="modal hidden">
    <div class="modal-content">
        <h3 id="modal-titulo">Agregar Servicio</h3>
        <form id="form-servicio">
            <input type="hidden" name="cliente" id="servicio-cliente" value="<?= htmlspecialchars($cliente['CODIGO']) ?>">
            <input type="hidden" name="id" id="servicio-id">

            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>

            <div class="form-group">
                <label for="tecnico">Técnico:</label>
                <select id="tecnico" name="tecnico" required>
                    <option value="">Seleccionar técnico</option>
                    <?php while ($tecnico = mysqli_fetch_assoc($resultadoTecnicos)): ?>
                        <option value="<?= htmlspecialchars($tecnico['TECNOM']) ?>"><?= htmlspecialchars($tecnico['TECNOM']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="obscli">Detalle:</label>
                <textarea id="obscli" name="obscli" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="obstec">Código:</label>
                <input type="text" id="obstec" name="obstec" maxlength="10" placeholder="Ej: VVDBD-1059">
            </div>

            <div class="form-group">
                <label for="importe">Importe:</label>
                <input type="text" id="importe" name="importe" placeholder="Ej: 570.000">
            </div>

            <div class="modal-buttons">
                <button type="submit" class="button button-primary">Guardar</button>
                <button type="button" id="modal-cerrar-servicio" class="button button-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    let codigoCliente = "<?= $cliente['CODIGO'] ?>";

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatNumber(num) {
        if (!num) return "No especificado";
        return Number(num).toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function parseNumber(str) {
        return str ? parseFloat(str.replace(/\./g, '').replace(',', '.')) : null;
    }

    function formatInputNumber(input) {
        let value = input.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('es-ES');
            input.value = value;
        }
    }

    function formatObstec(input) {
        let value = input.value.replace(/[^A-Za-z0-9-]/g, '').toUpperCase();
        let letters = value.replace(/[^A-Za-z]/g, '').slice(0, 5);
        let numbers = value.replace(/[^0-9]/g, '').slice(0, 4);

        if (letters.length === 5 && numbers.length > 0) {
            input.value = `${letters}-${numbers}`;
        } else if (letters.length === 5) {
            input.value = `${letters}-`;
        } else {
            input.value = letters;
        }
    }

    function cargarServicios() {
        fetch(`../controllers/serviciosController.php?codigo=${codigoCliente}`)
            .then(response => response.json())
            .then(data => {
                console.log("Datos recibidos en cargarServicios:", data);
                let lista = document.getElementById("lista-servicios");
                lista.innerHTML = "";

                if (data.mensaje) {
                    lista.innerHTML = `<p class='text-center text-danger'>${data.mensaje}</p>`;
                    return;
                }

                if (!Array.isArray(data)) {
                    lista.innerHTML = "<p class='text-center text-danger'>Error en la respuesta del servidor</p>";
                    return;
                }

                data.forEach(servicio => {
                    lista.innerHTML += `
                        <div class='servicio-item'>
                            <p><strong>Fecha:</strong> ${servicio.fecha ? formatDate(servicio.fecha) : "No disponible"}</p>
                            <p><strong>Cliente:</strong> ${servicio.clinom || "No disponible"}</p>
                            <p><strong>Dirección:</strong> ${servicio.calle || ""} ${servicio.nro || ""}</p>
                            <p><strong>Técnico:</strong> ${servicio.tecnico || "No asignado"}</p>
                            <p><strong>Detalle:</strong> ${servicio.obscli || "Sin observaciones"}</p>
                            <p><strong>Código:</strong> ${servicio.obstec || "Sin observaciones"}</p>
                            <p><strong>Importe:</strong> $${formatNumber(servicio.importe)}</p>
                            <button class='button' onclick="mostrarModalEditarServicio(${servicio.id})">Editar</button>
                            <button class='button boton-eliminar' onclick="eliminarServicio(${servicio.id})">Eliminar</button>
                        </div>
                    `;
                });
            })
            .catch(error => {
                console.error("Error cargando servicios:", error);
                document.getElementById("lista-servicios").innerHTML = "<p class='text-center text-danger'>Error al cargar servicios</p>";
            });
    }

    function mostrarModalAgregarServicio(codigo) {
        document.getElementById("modal-titulo").innerText = "Agregar Servicio";
        document.getElementById("servicio-cliente").value = codigo;
        document.getElementById("servicio-id").value = "";
        document.getElementById("form-servicio").reset();
        document.getElementById("modal-servicio").classList.remove("hidden");
    }

    function mostrarModalEditarServicio(id) {
        fetch(`../controllers/serviciosController.php?id=${id}`)
            .then(response => response.json())
            .then(servicio => {
                console.log("Servicio recibido para editar:", servicio);
                if (servicio.error) {
                    alert(servicio.error);
                    return;
                }
                if (!servicio.id || servicio.id === 0) {
                    alert("Error: ID del servicio inválido");
                    return;
                }
                document.getElementById("modal-titulo").innerText = "Editar Servicio";
                document.getElementById("servicio-id").value = servicio.id;
                document.getElementById("servicio-cliente").value = servicio.cliente || "";
                document.getElementById("fecha").value = servicio.fecha || "";
                document.getElementById("tecnico").value = servicio.tecnico || "";
                document.getElementById("obscli").value = servicio.obscli || "";
                document.getElementById("obstec").value = servicio.obstec || "";
                document.getElementById("importe").value = servicio.importe ? formatNumber(servicio.importe) : "";
                document.getElementById("modal-servicio").classList.remove("hidden");
            })
            .catch(error => {
                console.error("Error cargando servicio:", error);
                alert("Error al cargar el servicio");
            });
    }

    function eliminarServicio(id) {
        if (confirm("¿Está seguro de eliminar este servicio?")) {
            const formData = new FormData();
            formData.append("accion", "eliminar");
            formData.append("id", id);

            fetch("../controllers/serviciosController.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    cargarServicios();
                    window.location.reload();
                } else {
                    alert(data.error || "Error al eliminar el servicio");
                }
            })
            .catch(error => {
                console.error("Error eliminando servicio:", error);
                alert("Error al eliminar el servicio");
            });
        }
    }

    document.getElementById("modal-cerrar-servicio").addEventListener("click", function() {
        document.getElementById("modal-servicio").classList.add("hidden");
    });

    document.getElementById("form-servicio").addEventListener("submit", function(e) {
        e.preventDefault();
        const tecnico = document.getElementById("tecnico").value;

        const formData = new FormData(this);
        const accion = formData.get("id") ? "editar" : "agregar";
        formData.append("accion", accion);
        const importeInput = document.getElementById("importe").value;
        if (importeInput) {
            formData.set("importe", parseNumber(importeInput));
        }

        fetch("../controllers/serviciosController.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Respuesta al guardar:", data);
            if (data.success) {
                alert(data.message);
                document.getElementById("modal-servicio").classList.add("hidden");
                cargarServicios();
                window.location.reload();
            } else {
                alert(data.error || "Error al guardar el servicio");
            }
        })
        .catch(error => {
            console.error("Error guardando servicio:", error);
            alert("Error al guardar el servicio");
        });
    });

    document.getElementById("importe").addEventListener("input", function(e) {
        formatInputNumber(this);
    });

    document.getElementById("obstec").addEventListener("input", function(e) {
        formatObstec(this);
    });

    function eliminarCliente(codigo, nombre) {
        if (confirm(`¿Está seguro de eliminar al cliente ${nombre}?`)) {
            fetch(`../controllers/clientesController.php?accion=eliminar&codigo=${codigo}`, { method: "GET" })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || data.error);
                    if (data.success) window.location.href = "index.php";
                })
                .catch(error => console.error("Error eliminando cliente:", error));
        }
    }

    cargarServicios();
</script>

<?php include '../includes/footer.php'; ?>