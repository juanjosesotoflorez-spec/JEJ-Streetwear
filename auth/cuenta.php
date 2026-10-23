<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_login();
$userId = (int) $_SESSION['user']['id'];
$stmt = $mysqli->prepare('SELECT nombre,correo,telefono,creado_en FROM usuarios WHERE id=?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
if (!$account) {
    http_response_code(404);
    exit('No se encontró la cuenta.');
}
$stmt = $mysqli->prepare('SELECT id,total,estado,creado_en FROM pedidos WHERE usuario_id=? ORDER BY creado_en DESC LIMIT 50');
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt = $mysqli->prepare("SELECT COUNT(*) pedidos, COALESCE(SUM(CASE WHEN estado IN ('pagado','preparando','enviado') THEN total ELSE 0 END), 0) compras, COALESCE(SUM(CASE WHEN estado IN ('pendiente','pagado','preparando','enviado') THEN 1 ELSE 0 END), 0) activos FROM pedidos WHERE usuario_id=?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$accountStats = $stmt->get_result()->fetch_assoc();
$pageTitle = 'Mi cuenta';
require __DIR__ . '/../includes/header.php';
?>
<p class="eyebrow">TU ESPACIO PERSONAL</p><h1>HOLA, <span class="text-red"><?= e(explode(' ', trim($account['nombre']))[0]) ?></span></h1>
<p class="muted mb-4">Desde aquí puedes consultar tus pedidos y seguir explorando la tienda.</p>
<div class="row g-3 mb-4">
  <div class="col-sm-4"><div class="card app-card p-3 h-100"><span class="muted small">Mis pedidos</span><strong class="h3 mb-0 mt-2"><?= (int) $accountStats['pedidos'] ?></strong></div></div>
  <div class="col-sm-4"><div class="card app-card p-3 h-100"><span class="muted small">Compras aprobadas</span><strong class="h3 mb-0 mt-2"><?= money($accountStats['compras']) ?></strong></div></div>
  <div class="col-sm-4"><div class="card app-card p-3 h-100"><span class="muted small">Pedidos por completar</span><strong class="h3 mb-0 mt-2"><?= (int) $accountStats['activos'] ?></strong></div></div>
</div>
<div class="row g-4 mt-2">
  <div class="col-lg-4"><section class="card app-card p-4"><h2 class="h3">Mis datos</h2><p class="mb-2"><strong><?= e($account['nombre']) ?></strong></p><p class="muted mb-2"><?= e($account['correo']) ?></p><p class="muted mb-0">Teléfono: <?= e($account['telefono'] ?: 'No registrado') ?></p></section><a class="btn btn-red mt-3 me-2" href="<?= e(app_url('catalogo.php')) ?>">EXPLORAR CATÁLOGO</a><a class="btn btn-outline-light mt-3" href="<?= e(app_url('carrito.php')) ?>">MI CARRITO</a></div>
  <div class="col-lg-8"><section class="card app-card p-4"><h2 class="h3">Mis pedidos</h2>
    <?php if (!$orders): ?><p class="muted mb-0">Todavía no tienes pedidos. Explora el catálogo para empezar.</p>
    <?php else: ?><div class="table-responsive"><table class="table table-dark align-middle mb-0"><thead><tr><th>Pedido</th><th>Fecha</th><th>Estado</th><th>Total</th><th></th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td>#<?= (int) $order['id'] ?></td><td><?= e($order['creado_en']) ?></td><td><?= e($order['estado']) ?></td><td><?= money($order['total']) ?></td><td><a class="btn btn-sm btn-outline-light" href="<?= e(app_url('resultado_pago.php?id=' . (int) $order['id'])) ?>">Ver resumen</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
  </section></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
