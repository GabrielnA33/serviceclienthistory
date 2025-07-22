<?php
session_start();
include '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['username'];
    $clave = $_POST['password'];

    $conexion = conectarDB();
    $query = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuarioDB = mysqli_fetch_assoc($resultado);

    if ($usuarioDB && password_verify($clave, $usuarioDB['password'])) {
        $_SESSION['usuario'] = $usuario;
        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contrase単a incorrectos";
    }

    mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Rapigas</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="login-container">
            <h2>Bienvenido a Rapigas</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form class="login-form" action="login.php" method="post">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Usuario" required autocomplete="username">
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Contrase単a" required autocomplete="current-password">
                </div>
                <button type="submit" class="button button-login">Ingresar</button>
            </form>
            <div class="forgot-password">
                <a href="forgot-password.php">多Olvidaste tu contrase単a?</a>
            </div>
        </div>
    </div>
</body>
</html>

