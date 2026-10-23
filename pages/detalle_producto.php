<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Producto no encontrado.');
}
$stmt = $mysqli->prepare("SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, c.nombre categoria FROM productos p JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.activo=1");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
    http_response_code(404);
    exit('Producto no encontrado.');
}
$stmt = $mysqli->prepare('SELECT t.id, t.nombre, pt.existencias FROM producto_tallas pt JOIN tallas t ON t.id=pt.talla_id WHERE pt.producto_id=? ORDER BY t.id');
$stmt->bind_param('i', $id);
$stmt->execute();
$sizes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle = $product['nombre'];
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-5 align-items-start">
  <div class="col-lg-6"><img src="<?= e(app_url($product['imagen'])) ?>" alt="<?= e($product['nombre']) ?>" class="img-fluid w-100 product-detail-image"></div>
  <div class="col-lg-6"><p class="eyebrow"><?= e($product['categoria']) ?></p><h1><?= e($product['nombre']) ?></h1><p class="price mt-4"><?= money($product['precio']) ?></p><p class="muted my-4"><?= nl2br(e($product['descripcion'])) ?></p>
    <?php if ($sizes): ?>
      <form method="post" action="<?= e(app_url('carrito.php')) ?>" class="card app-card p-4">
        <?= csrf_field() ?><input type="hidden" name="accion" value="agregar"><input type="hidden" name="producto_id" value="<?= (int) $product['id'] ?>">
        <div class="mb-3"><label class="form-label" for="talla">Talla disponible</label><select class="form-select" id="talla" name="talla_id" required><option value="">Elige una talla</option><?php foreach ($sizes as $size): ?><option value="<?= (int) $size['id'] ?>" <?= (int) $size['existencias'] === 0 ? 'disabled' : '' ?>><?= e($size['nombre']) ?> — <?= (int) $size['existencias'] ?> disponibles</option><?php endforeach; ?></select></div>
        <div class="mb-3"><label class="form-label" for="cantidad">Cantidad</label><input class="form-control" type="number" id="cantidad" name="cantidad" min="1" max="20" value="1" required></div>
        <button class="btn btn-red" type="submit">AGREGAR AL CARRITO</button>
      </form>
    <?php else: ?><div class="alert alert-warning">Este producto no tiene tallas ni existencias registradas.</div><?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
