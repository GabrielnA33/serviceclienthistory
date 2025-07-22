<?php
function conectarDB() {
    // Cargar variables de entorno desde un archivo .env (recomendado)
    $servername = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USER') ?: 'rapigasc_angulus';
    $password = getenv('DB_PASS') ?: 'nji9(UHBVGY/2024';
    $dbname = getenv('DB_NAME') ?: 'rapigasc_rapigas';

    // Opcional: habilitar informes de errores para depuración
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conexion = new mysqli($servername, $username, $password, $dbname);
        $conexion->set_charset("utf8mb4"); // Configurar codificación
        return $conexion;
    } catch (Exception $e) {
        error_log("❌ Error de conexión a la base de datos: " . $e->getMessage());
        return null; // Evitar exponer detalles del error al usuario
    }
}
?>
