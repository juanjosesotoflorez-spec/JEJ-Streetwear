<?php
$pageTitle = $pageTitle ?? 'JEJ-STREETWEAR';
$currentUser = $_SESSION['user'] ?? null;
$flashMessage = take_flash();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | JEJ-STREETWEAR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css')) ?>">
  <script src="<?= e(app_url('assets/js/app.js')) ?>" defer></script>
</head>
<body>
<header class="site-header">
  <nav class="navbar navbar-expand-lg navbar-dark container py-3" aria-label="Navegación principal">
    <a class="navbar-brand brand-mark" href="<?= e(app_url('index.html')) ?>">JEJ<span>-</span>STREETWEAR</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav" aria-controls="appNav" aria-expanded="false" aria-label="Abrir menú"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="appNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('index.html')) ?>">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('catalogo.php')) ?>">Catálogo</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('contacto.php')) ?>">Contacto</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(app_url('carrito.php')) ?>">Carrito (<?= cart_count() ?>)</a></li>
        <?php if ($currentUser): ?>
          <?php if (($currentUser['rol'] ?? '') === 'admin'): ?><li class="nav-item"><a class="nav-link" href="<?= e(app_url('admin/dashboard.php')) ?>">Administración</a></li><?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?= e(app_url('auth/cuenta.php')) ?>">Mi cuenta</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(app_url('auth/logout.php')) ?>">Salir</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= e(app_url('auth/login.php')) ?>">Iniciar sesión</a></li>
          <li class="nav-item"><a class="btn btn-red btn-sm" href="<?= e(app_url('auth/registro.php')) ?>">Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
</header>
<main class="app-main container">
  <?php if ($flashMessage): ?><div class="alert alert-<?= e($flashMessage['type']) ?> notice" role="status"><?= e($flashMessage['message']) ?></div><?php endif; ?>
