<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Producto no encontrado.'); }
$categories = $mysqli->query('SELECT id,nombre FROM categorias WHERE activa=1 ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
$sizes = $mysqli->query('SELECT id,nombre FROM tallas ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$stmt = $mysqli->prepare('SELECT * FROM productos WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { http_response_code(404); exit('Producto no encontrado.'); }
$stmt = $mysqli->prepare('SELECT talla_id,existencias FROM producto_tallas WHERE producto_id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stockRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stockValues = [];
foreach ($stockRows as $row) $stockValues[(int) $row['talla_id']] = (int) $row['existencias'];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('nombre');
    $description = post_string('descripcion');
    $categoryId = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $postedSizes = $_POST['stock'] ?? [];
    if (mb_strlen($name) < 3 || mb_strlen($name) > 150) $errors[] = 'El nombre debe tener entre 3 y 150 caracteres.';
    if (mb_strlen($description) < 5) $errors[] = 'Escribe una descripción de al menos 5 caracteres.';
    if (!$categoryId || $price === false || $price < 0) $errors[] = 'Selecciona una categoría y un precio válido.';
    if (!is_array($postedSizes)) $postedSizes = [];
    foreach ($postedSizes as $qty) if (filter_var($qty, FILTER_VALIDATE_INT) === false || (int) $qty < 0 || (int) $qty > 10000) $errors[] = 'Las existencias deben ser números entre 0 y 10000.';
    if (!$errors) {
        try {
            $newImage = save_product_image($_FILES['imagen'] ?? []);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
            $newImage = null;
        }
        if (!$errors) {
            $image = $newImage ?? $product['imagen'];
            $mysqli->begin_transaction();
            try {
                $stmt = $mysqli->prepare('UPDATE productos SET categoria_id=?,nombre=?,descripcion=?,precio=?,imagen=? WHERE id=?');
                $stmt->bind_param('issdsi', $categoryId, $name, $description, $price, $image, $id);
                $stmt->execute();
                $stmt = $mysqli->prepare('DELETE FROM producto_tallas WHERE producto_id=?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                foreach ($sizes as $size) {
                    $qty = (int) ($postedSizes[$size['id']] ?? 0);
                    $sizeId = (int) $size['id'];
                    $stmt = $mysqli->prepare('INSERT INTO producto_tallas (producto_id,talla_id,existencias) VALUES (?,?,?)');
                    $stmt->bind_param('iii', $id, $sizeId, $qty);
                    $stmt->execute();
                }
                $mysqli->commit();
                flash('success', 'Producto actualizado correctamente.');
                redirect('admin/productos.php');
            } catch (mysqli_sql_exception $exception) {
                $mysqli->rollback();
                error_log('JEJ product update failed: ' . $exception->getMessage());
                $errors[] = 'No fue posible actualizar el producto.';
            }
        }
    }
}
$pageTitle = 'Editar producto';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4">EDITAR <span class="text-red">PRODUCTO</span></h1><nav class="admin-nav"><a href="<?= e(app_url('admin/productos.php')) ?>">Volver a productos</a></nav>
<?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="card app-card p-4" data-validate novalidate><?= csrf_field() ?>
  <div class="row g-3"><div class="col-md-8"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" required maxlength="150" value="<?= e($_POST['nombre'] ?? $product['nombre']) ?>"></div>
  <div class="col-md-4"><label class="form-label" for="categoria_id">Categoría</label><select class="form-select" id="categoria_id" name="categoria_id" required><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $product['categoria_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><label class="form-label" for="descripcion">Descripción</label><textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?= e($_POST['descripcion'] ?? $product['descripcion']) ?></textarea></div>
  <div class="col-md-4"><label class="form-label" for="precio">Precio COP</label><input class="form-control" type="number" id="precio" name="precio" min="0" step="100" required value="<?= e($_POST['precio'] ?? $product['precio']) ?>"></div>
  <div class="col-md-8"><label class="form-label" for="imagen">Cambiar imagen (JPG, PNG o WebP; máximo 3 MB)</label><input class="form-control" type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp"><small class="muted">Imagen actual: <?= e($product['imagen']) ?></small></div>
  <div class="col-12"><h2 class="h4">Existencias por talla</h2><div class="row g-2"><?php foreach ($sizes as $size): ?><div class="col-6 col-md-3"><label class="form-label" for="stock-<?= (int) $size['id'] ?>"><?= e($size['nombre']) ?></label><input class="form-control" type="number" min="0" max="10000" id="stock-<?= (int) $size['id'] ?>" name="stock[<?= (int) $size['id'] ?>]" value="<?= e($_POST['stock'][$size['id']] ?? (string) ($stockValues[(int) $size['id']] ?? 0)) ?>"></div><?php endforeach; ?></div></div>
  <div class="col-12"><button class="btn btn-red" type="submit">GUARDAR CAMBIOS</button></div></div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
