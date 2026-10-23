<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_admin();
$payments = $mysqli->query('SELECT pg.id,pg.pedido_id,pg.metodo,pg.resultado,pg.referencia,pg.creado_en,p.total,p.estado FROM pagos pg JOIN pedidos p ON p.id=pg.pedido_id ORDER BY pg.creado_en DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Pagos simulados';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">PAGOS <span class="text-red">SIMULADOS</span></h1><div class="alert alert-warning">Este módulo muestra registros de prueba. No existe conexión con bancos ni pasarelas reales.</div>
<nav class="admin-nav"><a href="<?= e(app_url('admin/dashboard.php')) ?>">Panel</a><a href="<?= e(app_url('admin/productos.php')) ?>">Productos</a><a href="<?= e(app_url('admin/pedidos.php')) ?>">Pedidos</a></nav>
<div class="table-responsive card app-card p-2"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Pago</th><th>Pedido</th><th>Referencia</th><th>Método</th><th>Resultado</th><th>Total</th><th>Estado pedido</th><th>Fecha</th></tr></thead><tbody><?php foreach ($payments as $payment): ?><tr><td><?= (int) $payment['id'] ?></td><td>#<?= (int) $payment['pedido_id'] ?></td><td><?= e($payment['referencia']) ?></td><td><?= e($payment['metodo']) ?></td><td><?= e($payment['resultado']) ?></td><td><?= money($payment['total']) ?></td><td><?= e($payment['estado']) ?></td><td><?= e($payment['creado_en']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
