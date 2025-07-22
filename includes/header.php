<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Rapigas - Gestion de Clientes</title>
    <link rel="stylesheet" href="../public/css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/464427581_8414211551961403_45171987999011987_n.jpg-RyBEU5ulKY0hkWBSPHQI1u4V1ZeJGV.jpeg" alt="Rapigas Logo">
                    <span class="logo-text">Rapigas</span>
                </a>
                <button id="themeToggle" class="button-theme">
                    <i class="fas fa-sun"></i>
                    <i class="fas fa-moon" style="display:none;"></i>
                </button>
                <div class="header-right">
                    <a href="config-tecnicos.php" class="button-header" title="Configuraci贸n de T茅cnicos"><i class="fas fa-cog"></i></a>
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <a href="logout.php" class="button-logout">Cerrar Sesi贸n</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <script src="../public/js/animations.js"></script>
</body>
</html>