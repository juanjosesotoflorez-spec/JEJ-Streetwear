<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$revenueStates = "'pagado','preparando','enviado'";
$monthRevenue = $mysqli->query("SELECT COALESCE(SUM(total), 0) total FROM pedidos WHERE estado IN ($revenueStates) AND creado_en >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch_assoc();
$monthOrders = $mysqli->query("SELECT COUNT(*) total FROM pedidos WHERE creado_en >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch_assoc();
$customers = $mysqli->query("SELECT COUNT(*) total FROM usuarios WHERE rol='cliente'")->fetch_assoc();
$lowStock = $mysqli->query('SELECT COUNT(*) total FROM producto_tallas pt JOIN productos p ON p.id=pt.producto_id WHERE p.activo=1 AND pt.existencias <= 3')->fetch_assoc();

$monthStart = new DateTimeImmutable('first day of this month');
$months = [];
$monthLabels = [];
$monthNames = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
for ($offset = 5; $offset >= 0; $offset--) {
    $month = $monthStart->modify("-$offset months");
    $key = $month->format('Y-m');
    $months[$key] = 0.0;
    $monthLabels[] = $monthNames[(int) $month->format('n') - 1] . ' ' . $month->format('y');
}
$salesByMonth = $mysqli->query("SELECT DATE_FORMAT(creado_en, '%Y-%m') mes, SUM(total) total FROM pedidos WHERE estado IN ($revenueStates) AND creado_en >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01') GROUP BY mes ORDER BY mes")->fetch_all(MYSQLI_ASSOC);
foreach ($salesByMonth as $sale) {
    if (array_key_exists($sale['mes'], $months)) {
        $months[$sale['mes']] = (float) $sale['total'];
    }
}

$orderStates = [
    'pendiente' => 'Pendiente',
    'pagado' => 'Pagado',
    'preparando' => 'Preparando',
    'enviado' => 'Enviado',
    'rechazado' => 'Rechazado',
    'cancelado' => 'Cancelado',
];
$ordersByState = array_fill_keys(array_keys($orderStates), 0);
foreach ($mysqli->query('SELECT estado, COUNT(*) total FROM pedidos GROUP BY estado')->fetch_all(MYSQLI_ASSOC) as $state) {
    if (array_key_exists($state['estado'], $ordersByState)) {
        $ordersByState[$state['estado']] = (int) $state['total'];
    }
}

$topProducts = $mysqli->query("SELECT dp.nombre_producto nombre, SUM(dp.cantidad) unidades FROM detalle_pedido dp JOIN pedidos p ON p.id=dp.pedido_id WHERE p.estado IN ($revenueStates) GROUP BY dp.producto_id, dp.nombre_producto ORDER BY unidades DESC, nombre ASC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$maxUnits = max(array_map(static fn(array $product): int => (int) $product['unidades'], $topProducts) ?: [1]);
$recentOrders = $mysqli->query('SELECT p.id, p.nombre_envio, p.total, p.estado, p.creado_en FROM pedidos p ORDER BY p.creado_en DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC);
$chartData = static fn(array $data): string => json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
$pageTitle = 'Administración';
require __DIR__ . '/../includes/header.php';
?>
<p class="eyebrow">PANEL PRIVADO · RESUMEN DEL NEGOCIO</p><h1 class="mb-4">CENTRO DE <span class="text-red">CONTROL</span></h1>
<nav class="admin-nav" aria-label="Herramientas de administración"><a href="<?= e(app_url('admin/productos.php')) ?>">Productos</a><a href="<?= e(app_url('admin/pedidos.php')) ?>">Pedidos</a><a href="<?= e(app_url('admin/pagos.php')) ?>">Pagos simulados</a><a href="<?= e(app_url('catalogo.php')) ?>">Ver tienda</a></nav>
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3"><div class="card app-card p-4 h-100"><p class="muted mb-2">Ventas aprobadas este mes</p><strong class="h2 mb-0"><?= money($monthRevenue['total']) ?></strong></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card app-card p-4 h-100"><p class="muted mb-2">Pedidos recibidos este mes</p><strong class="h2 mb-0"><?= (int) $monthOrders['total'] ?></strong></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card app-card p-4 h-100"><p class="muted mb-2">Clientes registrados</p><strong class="h2 mb-0"><?= (int) $customers['total'] ?></strong></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card app-card p-4 h-100"><p class="muted mb-2">Tallas con pocas existencias</p><strong class="h2 mb-0"><?= (int) $lowStock['total'] ?></strong><small class="muted mt-2">3 unidades o menos</small></div></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-lg-8"><section class="card app-card p-4 h-100"><div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3"><div><p class="eyebrow mb-2">RENDIMIENTO</p><h2 class="h3 mb-0">Ventas de los últimos 6 meses</h2></div><span class="muted small">Pedidos pagados y en proceso</span></div><canvas class="admin-chart" id="sales-chart" role="img" aria-label="Gráfica de ventas de los últimos seis meses"><p>Ventas de los últimos seis meses.</p></canvas></section></div>
  <div class="col-lg-4"><section class="card app-card p-4 h-100"><p class="eyebrow mb-2">OPERACIÓN</p><h2 class="h3">Estado de pedidos</h2><canvas class="admin-chart admin-chart-donut" id="orders-chart" role="img" aria-label="Gráfica con la distribución de pedidos por estado"><p>Distribución de pedidos por estado.</p></canvas><ul class="chart-legend list-unstyled mb-0"><?php foreach ($orderStates as $key => $label): ?><li><span class="legend-dot legend-<?= e($key) ?>"></span><?= e($label) ?><strong><?= $ordersByState[$key] ?></strong></li><?php endforeach; ?></ul></section></div>
</div>
<div class="row g-4">
  <div class="col-lg-5"><section class="card app-card p-4 h-100"><p class="eyebrow mb-2">PRODUCTOS DESTACADOS</p><h2 class="h3 mb-4">Más vendidos</h2><?php if (!$topProducts): ?><p class="muted mb-0">Aún no hay ventas aprobadas para mostrar.</p><?php else: ?><div class="top-products"><?php foreach ($topProducts as $product): ?><div><div class="d-flex justify-content-between gap-3 mb-2"><span><?= e($product['nombre']) ?></span><strong><?= (int) $product['unidades'] ?></strong></div><div class="progress stock-progress" role="progressbar" aria-label="<?= e($product['nombre']) ?>" aria-valuenow="<?= (int) $product['unidades'] ?>" aria-valuemin="0" aria-valuemax="<?= $maxUnits ?>"><div class="progress-bar" style="width: <?= round((int) $product['unidades'] / $maxUnits * 100) ?>%"></div></div></div><?php endforeach; ?></div><?php endif; ?></section></div>
  <div class="col-lg-7"><section class="card app-card p-4 h-100"><div class="d-flex justify-content-between align-items-center mb-3"><div><p class="eyebrow mb-2">ACTIVIDAD RECIENTE</p><h2 class="h3 mb-0">Últimos pedidos</h2></div><a class="text-link" href="<?= e(app_url('admin/pedidos.php')) ?>">VER TODOS</a></div><?php if (!$recentOrders): ?><p class="muted mb-0">Todavía no se han recibido pedidos.</p><?php else: ?><div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Pedido</th><th>Cliente</th><th>Estado</th><th class="text-end">Total</th></tr></thead><tbody><?php foreach ($recentOrders as $order): ?><tr><td>#<?= (int) $order['id'] ?><br><small class="muted"><?= e($order['creado_en']) ?></small></td><td><?= e($order['nombre_envio']) ?></td><td><?= e($order['estado']) ?></td><td class="text-end"><?= money($order['total']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section></div>
</div>
<script type="application/json" id="sales-chart-data"><?= $chartData(['labels' => $monthLabels, 'values' => array_values($months)]) ?></script>
<script type="application/json" id="orders-chart-data"><?= $chartData(['values' => array_values($ordersByState)]) ?></script>
<p class="muted small mt-4">Las ventas se calculan con pedidos pagados, en preparación o enviados. Los pagos son simulados y no representan cobros reales.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
