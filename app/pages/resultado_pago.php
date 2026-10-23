<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_login();
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId || $orderId < 1) {
    http_response_code(404);
    exit('Pedido no encontrado.');
}
$userId = (int) $_SESSION['user']['id'];
$stmt = $mysqli->prepare('SELECT p.id, p.total, p.estado, p.creado_en, p.nombre_envio, p.direccion, p.ciudad, pg.resultado, pg.referencia FROM pedidos p JOIN pagos pg ON pg.pedido_id=p.id WHERE p.id=? AND p.usuario_id=?');
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) {
    http_response_code(404);
    exit('Pedido no encontrado.');
}
$stmt = $mysqli->prepare('SELECT nombre_producto, nombre_talla, cantidad, precio_unitario FROM detalle_pedido WHERE pedido_id=?');
$stmt->bind_param('i', $orderId);
$stmt->execute();
$lines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$heading = match ($order['resultado']) {
    'aprobado' => 'SIMULACIÓN APROBADA',
    'rechazado' => 'SIMULACIÓN RECHAZADA',
    default => 'SIMULACIÓN PENDIENTE',
};
$pageTitle = 'Resultado del pago';
require __DIR__ . '/../includes/header.php';
?>
<div class="card app-card p-4 p-md-5">
  <p class="eyebrow">RESULTADO DE PRUEBA · NO ES UN COBRO REAL</p><h1><?= e($heading) ?></h1>
  <p class="muted mt-3">Pedido #<?= (int) $order['id'] ?> · Referencia <?= e($order['referencia']) ?> · Estado: <?= e($order['estado']) ?></p>
  <?php if ($order['resultado'] !== 'aprobado'): ?><div class="alert alert-warning">El pago no fue aprobado. No se marcó como pagado y no se descontó inventario.</div><?php else: ?><div class="alert alert-success">El pedido de demostración quedó registrado como pagado. No se realizó un cobro.</div><?php endif; ?>
  <h2 class="h3 mt-4">Resumen del pedido</h2>
  <div class="table-responsive"><table class="table table-dark"><thead><tr><th>Producto</th><th>Talla</th><th>Cantidad</th><th>Precio</th></tr></thead><tbody><?php foreach ($lines as $line): ?><tr><td><?= e($line['nombre_producto']) ?></td><td><?= e($line['nombre_talla']) ?></td><td><?= (int) $line['cantidad'] ?></td><td><?= money($line['precio_unitario']) ?></td></tr><?php endforeach; ?></tbody></table></div>
  <p class="price">Total <?= money($order['total']) ?></p><p class="muted">Envío a <?= e($order['nombre_envio']) ?> · <?= e($order['direccion']) ?>, <?= e($order['ciudad']) ?></p>
  <a class="btn btn-red align-self-start mt-2" href="<?= e(app_url('catalogo.php')) ?>">VOLVER AL CATÁLOGO</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
