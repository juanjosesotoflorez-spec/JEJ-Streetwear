<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        exit('Producto inválido.');
    }
    $stmt = $mysqli->prepare('UPDATE productos SET activo = 0 WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('success', 'Producto desactivado; se conserva el historial de pedidos.');
    redirect('admin/productos.php');
}
$products = $mysqli->query('SELECT p.id,p.nombre,p.precio,p.activo,c.nombre categoria,COALESCE(SUM(pt.existencias),0) existencias FROM productos p JOIN categorias c ON c.id=p.categoria_id LEFT JOIN producto_tallas pt ON pt.producto_id=p.id GROUP BY p.id, c.nombre ORDER BY p.id DESC')->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Administrar productos';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"><h1>PRODUCTOS</h1><a class="btn btn-red" href="<?= e(app_url('admin/agregar_producto.php')) ?>">AGREGAR PRODUCTO</a></div>
<nav class="admin-nav"><a href="<?= e(app_url('admin/dashboard.php')) ?>">Panel</a><a href="<?= e(app_url('admin/pedidos.php')) ?>">Pedidos</a><a href="<?= e(app_url('admin/pagos.php')) ?>">Pagos</a></nav>
<div class="table-responsive card app-card p-2"><table class="table table-dark table-hover align-middle mb-0"><thead><tr><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($products as $product): ?><tr><td><?= e($product['nombre']) ?></td><td><?= e($product['categoria']) ?></td><td><?= money($product['precio']) ?></td><td><?= (int) $product['existencias'] ?></td><td><?= $product['activo'] ? 'Activo' : 'Inactivo' ?></td><td class="d-flex gap-2"><a class="btn btn-sm btn-outline-light" href="<?= e(app_url('admin/editar_producto.php?id=' . (int) $product['id'])) ?>">Editar</a><?php if ($product['activo']): ?><form method="post" onsubmit="return confirm('¿Desactivar este producto?');"><?= csrf_field() ?><input type="hidden" name="producto_id" value="<?= (int) $product['id'] ?>"><button class="btn btn-sm btn-outline-danger">Desactivar</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
