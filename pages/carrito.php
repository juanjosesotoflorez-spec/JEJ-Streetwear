<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post_string('accion');
    if ($action === 'agregar') {
        $productId = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
        $sizeId = filter_input(INPUT_POST, 'talla_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
        if (!$productId || !$sizeId || !$quantity || $quantity < 1 || $quantity > 20) {
            flash('danger', 'Selecciona un producto, una talla y una cantidad válida.');
            redirect('catalogo.php');
        }
        $stmt = $mysqli->prepare('SELECT pt.existencias FROM producto_tallas pt JOIN productos p ON p.id=pt.producto_id WHERE pt.producto_id=? AND pt.talla_id=? AND p.activo=1');
        $stmt->bind_param('ii', $productId, $sizeId);
        $stmt->execute();
        $stock = $stmt->get_result()->fetch_assoc();
        $already = (int) ($_SESSION['cart'][$productId][$sizeId] ?? 0);
        if (!$stock || $already + $quantity > (int) $stock['existencias']) {
            flash('warning', 'La cantidad solicitada supera las existencias disponibles.');
            redirect('detalle_producto.php?id=' . $productId);
        }
        $_SESSION['cart'][$productId][$sizeId] = $already + $quantity;
        flash('success', 'Producto agregado al carrito.');
        redirect('carrito.php');
    }
    if ($action === 'actualizar') {
        $quantities = $_POST['cantidades'] ?? [];
        if (!is_array($quantities)) $quantities = [];
        foreach ($quantities as $key => $value) {
            if (!preg_match('/^(\d+)-(\d+)$/', (string) $key, $matches)) continue;
            $productId = (int) $matches[1];
            $sizeId = (int) $matches[2];
            $quantity = filter_var($value, FILTER_VALIDATE_INT);
            if ($quantity === false || $quantity < 0 || $quantity > 20) {
                flash('warning', 'Usa cantidades entre 0 y 20. El valor 0 elimina el artículo.');
                redirect('carrito.php');
            }
            if ($quantity === 0) {
                unset($_SESSION['cart'][$productId][$sizeId]);
            } else {
                $stmt = $mysqli->prepare('SELECT existencias FROM producto_tallas WHERE producto_id=? AND talla_id=?');
                $stmt->bind_param('ii', $productId, $sizeId);
                $stmt->execute();
                $stock = $stmt->get_result()->fetch_assoc();
                if (!$stock || $quantity > (int) $stock['existencias']) {
                    flash('warning', 'Una cantidad supera las existencias actuales; no se actualizó el carrito.');
                    redirect('carrito.php');
                }
                $_SESSION['cart'][$productId][$sizeId] = $quantity;
            }
            if (empty($_SESSION['cart'][$productId])) unset($_SESSION['cart'][$productId]);
        }
        flash('success', 'Carrito actualizado.');
        redirect('carrito.php');
    }
    if ($action === 'vaciar') {
        $_SESSION['cart'] = [];
        flash('success', 'Carrito vaciado.');
        redirect('carrito.php');
    }
}

$items = [];
$total = 0.0;
$cart = $_SESSION['cart'] ?? [];
foreach ($cart as $productId => $sizes) {
    foreach ($sizes as $sizeId => $quantity) {
        $stmt = $mysqli->prepare('SELECT p.id, p.nombre, p.precio, p.imagen, t.nombre talla, pt.existencias FROM productos p JOIN producto_tallas pt ON pt.producto_id=p.id JOIN tallas t ON t.id=pt.talla_id WHERE p.id=? AND pt.talla_id=? AND p.activo=1');
        $productIdInt = (int) $productId;
        $sizeIdInt = (int) $sizeId;
        $stmt->bind_param('ii', $productIdInt, $sizeIdInt);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        if (!$item) {
            unset($_SESSION['cart'][$productId][$sizeId]);
            continue;
        }
        $item['cantidad'] = (int) $quantity;
        $item['talla_id'] = $sizeIdInt;
        $item['subtotal'] = (float) $item['precio'] * (int) $quantity;
        $total += $item['subtotal'];
        $items[] = $item;
    }
    if (empty($_SESSION['cart'][$productId])) unset($_SESSION['cart'][$productId]);
}
$pageTitle = 'Carrito';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">TU <span class="text-red">CARRITO</span></h1>
<?php if (!$items): ?><div class="empty-state"><h2>Tu carrito está vacío</h2><p class="muted">Explora la colección para encontrar tu próximo fit.</p><a class="btn btn-red" href="<?= e(app_url('catalogo.php')) ?>">IR AL CATÁLOGO</a></div>
<?php else: ?>
  <form method="post" class="card app-card p-3">
    <?= csrf_field() ?><input type="hidden" name="accion" value="actualizar">
    <div class="table-responsive"><table class="table table-dark align-middle"><thead><tr><th>Producto</th><th>Precio</th><th>Talla</th><th class="quantity-column">Cantidad</th><th>Subtotal</th></tr></thead><tbody>
      <?php foreach ($items as $item): ?><tr><td><div class="d-flex gap-3 align-items-center"><img src="<?= e(app_url($item['imagen'])) ?>" alt="" class="cart-thumb"><span><?= e($item['nombre']) ?></span></div></td><td><?= money($item['precio']) ?></td><td><?= e($item['talla']) ?></td><td><input class="form-control" type="number" min="0" max="20" name="cantidades[<?= (int) $item['id'] ?>-<?= (int) $item['talla_id'] ?>]" value="<?= (int) $item['cantidad'] ?>" aria-label="Cantidad"></td><td><?= money($item['subtotal']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><button class="btn btn-outline-light" type="submit">ACTUALIZAR CANTIDADES</button><strong class="price">Total: <?= money($total) ?></strong></div>
  </form>
  <div class="d-flex justify-content-between flex-wrap gap-2 mt-3">
    <form method="post"><?= csrf_field() ?><input type="hidden" name="accion" value="vaciar"><button class="btn btn-outline-danger" type="submit">VACIAR CARRITO</button></form>
    <a class="btn btn-red" href="<?= e(app_url('checkout.php')) ?>">CONTINUAR AL CHECKOUT →</a>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
