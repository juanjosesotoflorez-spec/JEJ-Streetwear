<?php
session_start();
$logged = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$role = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JE&J Streetwear</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
   <nav class="navbar">
    <div class="logo">JE&J</div>
    <div class="menu-icon" id="menu-icon">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </div>
    <ul class="navbar-links" id="navbar-links">
<?php if (!$logged): ?>
        <li><a href="php/auth/login.php"><i class="fas fa-user"></i> Iniciar Sesión</a></li>
        <li><a href="php/auth/register.php"><i class="fas fa-user-plus"></i> Regístrate</a></li>
<?php else: ?>
        <?php if ($role === 'admin'): ?>
            <li><a href="php/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <?php else: ?>
            <li><a href="php/user/dashboard.php"><i class="fas fa-tachometer-alt"></i> Mi Cuenta</a></li>
        <?php endif; ?>
        <li><a href="php/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
<?php endif; ?>
        <li><a href="ropa/catalogo.html"><i class="fas fa-tshirt"></i> Catálogo</a></li>
        <li><a href="ropa_subida/ropasubida.html"><i class="fas fa-upload"></i> Ropa subida</a></li>
     
    </ul>
</nav>


    <div class="video-background">
        <video autoplay muted loop id="video-bg">
            <source src="videos/nike99.mp4" type="video/mp4">
            Tu navegador no soporta el formato de video.
        </video>
    </div>

    <div class="content">
        <h1>JE&J Streetwear Collection</h1>
        <p>Descubre la excelencia en cada detalle. Nuestra colección exclusiva fusiona elegancia atemporal con diseño contemporáneo.</p>
        <a href="coleccion/coleccion.html" class="btn"><i class="fas fa-shopping-bag"></i> Explorar Colección</a>
    </div>

    <script src="js/js.js"></script>
</body>
</html>