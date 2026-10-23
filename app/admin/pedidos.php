<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
    $newState = post_string('estado');
    if (!$orderId || !in_array($newState, ['preparando', 'enviado', 'cancelado'], true)) {
        http_response_code(400);
        exit('La actualización del pedido no es válida.');
    }
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare('SELECT estado,inventario_descontado FROM pedidos WHERE id=? FOR UPDATE');
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        if (!$order || !in_array($order['estado'], ['pagado', 'preparando', 'enviado'], true)) {
            throw new RuntimeException('Solo se pueden actualizar pedidos pagados que no estén cancelados.');
        }
        if ($newState === 'enviado' && $order['estado'] === 'pagado') {
            throw new RuntimeException('Primero cambia el pedido a preparando.');
        }
        if ($newState === 'preparando' && $order['estado'] === 'enviado') {
            throw new RuntimeException('Un pedido enviado no puede regresar a preparación.');
        }
        if ($newState === 'cancelado' && (int) $order['inventario_descontado'] === 1) {
            $stmt = $mysqli->prepare('SELECT producto_id,talla_id,cantidad FROM detalle_pedido WHERE pedido_id=?');
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $line) {
                $stmt = $mysqli->prepare('UPDATE producto_tallas SET existencias=existencias+? WHERE producto_id=? AND talla_id=?');
                $stmt->bind_param('iii', $line['cantidad'], $line['producto_id'], $line['talla_id']);
                $stmt->execute();
            }
            $stmt = $mysqli->prepare('UPDATE pedidos SET inventario_descontado=0 WHERE id=?');
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
        }
        $stmt = $mysqli->prepare('UPDATE pedidos SET estado=? WHERE id=?');
        $stmt->bind_param('si', $newState, $orderId);
        $stmt->execute();
        $mysqli->commit();
        flash('success', 'Estado del pedido actualizado.');
    } catch (RuntimeException $exception) {
        $mysqli->rollback();
        flash('warning', $exception->getMessage());
    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        error_log('JEJ order update failed: ' . $exception->getMessage());
        flash('danger', 'No fue posible actualizar el pedido.');
    }
    redirect('admin/pedidos.php');
}
$orders = $mysqli->query('SELECT p.id,p.nombre_envio,p.correo_envio,p.total,p.estado,p.creado_en,pg.resultado FROM pedidos p LEFT JOIN pagos pg ON pg.pedido_id=p.id ORDER BY p.creado_en DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Administrar pedidos';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">PEDIDOS</h1><nav class="admin-nav"><a href="<?= e(app_url('admin/dashboard.php')) ?>">Panel</a><a href="<?= e(app_url('admin/productos.php')) ?>">Productos</a><a href="<?= e(app_url('admin/pagos.php')) ?>">Pagos</a></nav>
<div class="table-responsive card app-card p-2"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>#</th><th>Comprador</th><th>Total</th><th>Pago demo</th><th>Estado</th><th>Fecha</th><th>Actualizar</th></tr></thead><tbody>
<?php foreach ($orders as $order): ?><tr><td><?= (int) $order['id'] ?></td><td><?= e($order['nombre_envio']) ?><br><small class="muted"><?= e($order['correo_envio']) ?></small></td><td><?= money($order['total']) ?></td><td><?= e($order['resultado'] ?? 'sin registro') ?></td><td><?= e($order['estado']) ?></td><td><?= e($order['creado_en']) ?></td><td><?php if (in_array($order['estado'], ['pagado','preparando','enviado'], true)): ?><form method="post" class="d-flex gap-2"><?= csrf_field() ?><input type="hidden" name="pedido_id" value="<?= (int) $order['id'] ?>"><select class="form-select form-select-sm" name="estado" aria-label="Nuevo estado"><option value="preparando" <?= $order['estado'] === 'preparando' ? 'selected' : '' ?>>Preparando</option><option value="enviado" <?= $order['estado'] === 'enviado' ? 'selected' : '' ?>>Enviado</option><option value="cancelado">Cancelar</option></select><button class="btn btn-sm btn-outline-light">Guardar</button></form><?php else: ?><span class="muted">Sin acciones</span><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
