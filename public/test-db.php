<?php
include '../config/database.php';

$conexion = conectarDB();

if ($conexion) {
    echo "✅ Conexión exitosa a la base de datos.";
} else {
    echo "❌ Error de conexión.";
}

mysqli_close($conexion);
?>
