<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    flash('warning', 'Agrega productos antes de continuar al pago.');
    redirect('carrito.php');
}
$items = [];
$total = 0.0;
foreach ($cart as $productId => $sizes) {
    foreach ($sizes as $sizeId => $quantity) {
        $productId = (int) $productId;
        $sizeId = (int) $sizeId;
        $quantity = (int) $quantity;
        $stmt = $mysqli->prepare('SELECT p.id, p.nombre, p.precio, t.nombre talla, pt.existencias FROM productos p JOIN producto_tallas pt ON pt.producto_id=p.id JOIN tallas t ON t.id=pt.talla_id WHERE p.id=? AND pt.talla_id=? AND p.activo=1');
        $stmt->bind_param('ii', $productId, $sizeId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        if (!$item || $quantity < 1 || $quantity > (int) $item['existencias']) {
            flash('warning', 'Revisa tu carrito: cambió la disponibilidad de un producto.');
            redirect('carrito.php');
        }
        $item['talla_id'] = $sizeId;
        $item['cantidad'] = $quantity;
        $item['subtotal'] = (float) $item['precio'] * $quantity;
        $total += $item['subtotal'];
        $items[] = $item;
    }
}
$userId = (int) $_SESSION['user']['id'];
$userStmt = $mysqli->prepare('SELECT nombre, correo, telefono FROM usuarios WHERE id=?');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$buyer = $userStmt->get_result()->fetch_assoc();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('nombre');
    $email = strtolower(post_string('correo'));
    $phone = post_string('telefono');
    $address = post_string('direccion');
    $city = post_string('ciudad');
    $simulation = post_string('resultado');
    if (mb_strlen($name) < 3 || mb_strlen($name) > 120) $errors[] = 'Escribe un nombre válido.';
    if (!valid_email($email)) $errors[] = 'Escribe un correo válido.';
    if (mb_strlen($address) < 6 || mb_strlen($address) > 255) $errors[] = 'Escribe una dirección de envío válida.';
    if (mb_strlen($city) < 2 || mb_strlen($city) > 100) $errors[] = 'Escribe una ciudad válida.';
    if (!in_array($simulation, ['aprobado', 'rechazado', 'pendiente'], true)) $errors[] = 'Elige un resultado de simulación válido.';
    if (!$errors) {
        $mysqli->begin_transaction();
        try {
            $lockedItems = [];
            $lockedTotal = 0.0;
            foreach ($cart as $productId => $sizes) {
                foreach ($sizes as $sizeId => $quantity) {
                    $productId = (int) $productId;
                    $sizeId = (int) $sizeId;
                    $quantity = (int) $quantity;
                    $stmt = $mysqli->prepare('SELECT p.nombre, p.precio, t.nombre talla, pt.existencias FROM productos p JOIN producto_tallas pt ON pt.producto_id=p.id JOIN tallas t ON t.id=pt.talla_id WHERE p.id=? AND pt.talla_id=? AND p.activo=1 FOR UPDATE');
                    $stmt->bind_param('ii', $productId, $sizeId);
                    $stmt->execute();
                    $locked = $stmt->get_result()->fetch_assoc();
                    if (!$locked || $quantity < 1 || $quantity > (int) $locked['existencias']) {
                        throw new RuntimeException('El inventario cambió mientras confirmabas. Revisa tu carrito.');
                    }
                    $locked['producto_id'] = $productId;
                    $locked['talla_id'] = $sizeId;
                    $locked['cantidad'] = $quantity;
                    $lockedItems[] = $locked;
                    $lockedTotal += (float) $locked['precio'] * $quantity;
                }
            }
            $stmt = $mysqli->prepare("INSERT INTO pedidos (usuario_id, nombre_envio, correo_envio, telefono_envio, direccion, ciudad, total, estado, inventario_descontado) VALUES (?, ?, ?, NULLIF(?, ''), ?, ?, ?, ?, 0)");
            $orderStatus = $simulation === 'aprobado' ? 'pagado' : $simulation;
            $stmt->bind_param('isssssss', $userId, $name, $email, $phone, $address, $city, $lockedTotal, $orderStatus);
            $stmt->execute();
            $orderId = (int) $mysqli->insert_id;
            foreach ($lockedItems as $line) {
                $stmt = $mysqli->prepare('INSERT INTO detalle_pedido (pedido_id, producto_id, talla_id, nombre_producto, nombre_talla, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('iiissid', $orderId, $line['producto_id'], $line['talla_id'], $line['nombre'], $line['talla'], $line['cantidad'], $line['precio']);
                $stmt->execute();
                if ($simulation === 'aprobado') {
                    $stmt = $mysqli->prepare('UPDATE producto_tallas SET existencias = existencias - ? WHERE producto_id=? AND talla_id=? AND existencias >= ?');
                    $stmt->bind_param('iiii', $line['cantidad'], $line['producto_id'], $line['talla_id'], $line['cantidad']);
                    $stmt->execute();
                    if ($stmt->affected_rows !== 1) throw new RuntimeException('No fue posible reservar el inventario del pedido.');
                }
            }
            if ($simulation === 'aprobado') {
                $stmt = $mysqli->prepare('UPDATE pedidos SET inventario_descontado=1 WHERE id=?');
                $stmt->bind_param('i', $orderId);
                $stmt->execute();
            }
            $reference = 'JEJ-' . strtoupper(bin2hex(random_bytes(6)));
            $stmt = $mysqli->prepare('INSERT INTO pagos (pedido_id, metodo, resultado, referencia) VALUES (?, ?, ?, ?)');
            $method = 'simulacion_educativa';
            $stmt->bind_param('isss', $orderId, $method, $simulation, $reference);
            $stmt->execute();
            $mysqli->commit();
            $_SESSION['last_order_id'] = $orderId;
            if ($simulation === 'aprobado') $_SESSION['cart'] = [];
            redirect('resultado_pago.php?id=' . $orderId);
        } catch (RuntimeException $exception) {
            $mysqli->rollback();
            $errors[] = $exception->getMessage();
        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            error_log('JEJ checkout failed: ' . $exception->getMessage());
            $errors[] = 'No fue posible registrar el pedido. Inténtalo de nuevo.';
        }
    }
}
$pageTitle = 'Checkout';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">CONFIRMA TU <span class="text-red">PEDIDO</span></h1>
<?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<div class="row g-4">
  <div class="col-lg-7"><form method="post" class="card app-card p-4" data-validate novalidate>
    <?= csrf_field() ?><h2 class="h3 mb-3">Datos de envío</h2>
    <div class="mb-3"><label class="form-label" for="nombre">Nombre completo</label><input class="form-control" id="nombre" name="nombre" required maxlength="120" value="<?= e($_POST['nombre'] ?? $buyer['nombre']) ?>"></div>
    <div class="row"><div class="col-md-7 mb-3"><label class="form-label" for="correo">Correo electrónico</label><input class="form-control" id="correo" name="correo" type="email" required maxlength="190" value="<?= e($_POST['correo'] ?? $buyer['correo']) ?>"></div><div class="col-md-5 mb-3"><label class="form-label" for="telefono">Teléfono</label><input class="form-control" id="telefono" name="telefono" maxlength="30" value="<?= e($_POST['telefono'] ?? $buyer['telefono']) ?>"></div></div>
    <div class="mb-3"><label class="form-label" for="direccion">Dirección</label><input class="form-control" id="direccion" name="direccion" required minlength="6" maxlength="255" value="<?= e($_POST['direccion'] ?? '') ?>"></div>
    <div class="mb-4"><label class="form-label" for="ciudad">Ciudad</label><input class="form-control" id="ciudad" name="ciudad" required minlength="2" maxlength="100" value="<?= e($_POST['ciudad'] ?? '') ?>"></div>
    <h2 class="h3 mb-3">Pago de demostración</h2><div class="alert alert-warning">Simulación educativa: no ingreses tarjetas ni datos bancarios. No se realizará ningún cobro.</div>
    <label class="form-label" for="resultado">Resultado de prueba</label><select class="form-select mb-4" name="resultado" id="resultado" required><option value="aprobado">Aprobado (demo)</option><option value="rechazado">Rechazado (demo)</option><option value="pendiente">Pendiente (demo)</option></select>
    <button class="btn btn-red" type="submit">CONFIRMAR PEDIDO DEMOSTRATIVO</button>
  </form></div>
  <div class="col-lg-5"><div class="card app-card p-4"><h2 class="h3">Resumen</h2><?php foreach ($items as $item): ?><div class="d-flex justify-content-between gap-3 py-2 border-bottom border-secondary"><span><?= e($item['nombre']) ?> · <?= e($item['talla']) ?> × <?= (int) $item['cantidad'] ?></span><span><?= money($item['subtotal']) ?></span></div><?php endforeach; ?><div class="d-flex justify-content-between mt-3 price"><span>Total</span><span><?= money($total) ?></span></div></div></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
