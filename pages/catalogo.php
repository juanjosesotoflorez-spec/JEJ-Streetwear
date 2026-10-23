<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';

$search = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['categoria'] ?? ''));
$sort = (string) ($_GET['orden'] ?? 'recientes');
$available = isset($_GET['disponibles']);
$page = max(1, (int) ($_GET['pagina'] ?? 1));
$pageSize = 12;
$categories = $mysqli->query('SELECT nombre FROM categorias WHERE activa = 1 ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
$where = ['p.activo = 1'];
$types = '';
$params = [];
if ($search !== '') {
    $where[] = '(p.nombre LIKE ? OR p.descripcion LIKE ?)';
    $like = '%' . $search . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}
if ($category !== '') {
    $where[] = 'c.nombre = ?';
    $types .= 's';
    $params[] = $category;
}
if ($available) $where[] = 'EXISTS (SELECT 1 FROM producto_tallas pt WHERE pt.producto_id = p.id AND pt.existencias > 0)';
$whereSql = implode(' AND ', $where);
$countStmt = $mysqli->prepare("SELECT COUNT(DISTINCT p.id) total FROM productos p JOIN categorias c ON c.id = p.categoria_id WHERE $whereSql");
if ($types !== '') bind_values($countStmt, $types, $params);
$countStmt->execute();
$total = (int) $countStmt->get_result()->fetch_assoc()['total'];
$pages = max(1, (int) ceil($total / $pageSize));
$page = min($page, $pages);
$offset = ($page - 1) * $pageSize;
$orderSql = match ($sort) {
    'precio_asc' => 'p.precio ASC, p.id DESC',
    'precio_desc' => 'p.precio DESC, p.id DESC',
    'nombre' => 'p.nombre ASC',
    default => 'p.id DESC',
};
$stmt = $mysqli->prepare("SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, c.nombre categoria,
    COALESCE(SUM(pt.existencias), 0) existencias,
    GROUP_CONCAT(DISTINCT IF(pt.existencias > 0, t.nombre, NULL) ORDER BY t.id SEPARATOR ', ') tallas,
    GROUP_CONCAT(DISTINCT IF(pt.existencias > 0, CONCAT(t.id, '|', t.nombre), NULL) ORDER BY t.id SEPARATOR ',') talla_options
    FROM productos p JOIN categorias c ON c.id = p.categoria_id
    LEFT JOIN producto_tallas pt ON pt.producto_id = p.id LEFT JOIN tallas t ON t.id = pt.talla_id
    WHERE $whereSql GROUP BY p.id, c.nombre ORDER BY $orderSql LIMIT ? OFFSET ?");
$bindTypes = $types . 'ii';
$bindParams = array_merge($params, [$pageSize, $offset]);
bind_values($stmt, $bindTypes, $bindParams);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Catálogo';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
  <div><p class="eyebrow">ENCUENTRA TU FIT</p><h1>EL <span class="text-red">CATÁLOGO</span></h1></div>
  <p class="muted mb-1"><?= $total ?> producto(s)</p>
</div>
<form method="get" class="card app-card p-3 mb-4">
  <div class="row g-2 align-items-end">
    <div class="col-md-4"><label class="form-label" for="q">Buscar</label><input class="form-control" id="q" name="q" value="<?= e($search) ?>" placeholder="Nombre o descripción"></div>
    <div class="col-md-3"><label class="form-label" for="categoria">Categoría</label><select class="form-select" id="categoria" name="categoria"><option value="">Todas</option><?php foreach ($categories as $item): ?><option value="<?= e($item['nombre']) ?>" <?= $category === $item['nombre'] ? 'selected' : '' ?>><?= e($item['nombre']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label" for="orden">Ordenar por</label><select class="form-select" id="orden" name="orden"><option value="recientes" <?= $sort === 'recientes' ? 'selected' : '' ?>>Más recientes</option><option value="precio_asc" <?= $sort === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option><option value="precio_desc" <?= $sort === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option><option value="nombre" <?= $sort === 'nombre' ? 'selected' : '' ?>>Nombre</option></select></div>
    <div class="col-md-2"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="disponibles" id="disponibles" value="1" <?= $available ? 'checked' : '' ?>><label class="form-check-label" for="disponibles">En stock</label></div><button class="btn btn-red w-100 py-2" type="submit">FILTRAR</button></div>
  </div>
</form>
<?php if (!$products): ?><div class="empty-state"><h2>No encontramos productos</h2><p class="muted">Prueba cambiar la búsqueda o los filtros.</p></div><?php else: ?>
<div class="row g-4">
  <?php foreach ($products as $product): ?>
    <div class="col-sm-6 col-lg-4"><article class="card app-card h-100">
      <a href="<?= e(app_url('detalle_producto.php?id=' . (int) $product['id'])) ?>"><img class="product-image" src="<?= e(app_url($product['imagen'])) ?>" alt="<?= e($product['nombre']) ?>"></a>
      <div class="card-body d-flex flex-column"><div class="d-flex justify-content-between gap-2"><span class="badge badge-red"><?= e($product['categoria']) ?></span><span class="small muted"><?= (int) $product['existencias'] ?> disponibles</span></div>
        <h2 class="h4 mt-3"><?= e($product['nombre']) ?></h2><p class="muted small"><?= e(mb_strimwidth($product['descripcion'], 0, 110, '…')) ?></p>
        <p class="small muted mb-2">Tallas: <?= e($product['tallas'] ?: 'Sin existencias') ?></p><p class="price mt-auto"><?= money($product['precio']) ?></p>
        <?php if ($product['talla_options']): ?>
          <form method="post" action="<?= e(app_url('carrito.php')) ?>" class="mb-2">
            <?= csrf_field() ?><input type="hidden" name="accion" value="agregar"><input type="hidden" name="producto_id" value="<?= (int) $product['id'] ?>">
            <label class="form-label small" for="talla-<?= (int) $product['id'] ?>">Talla</label>
            <select class="form-select mb-2" id="talla-<?= (int) $product['id'] ?>" name="talla_id" required>
              <?php foreach (explode(',', $product['talla_options']) as $option): [$sizeId, $sizeName] = explode('|', $option, 2); ?><option value="<?= (int) $sizeId ?>"><?= e($sizeName) ?></option><?php endforeach; ?>
            </select>
            <input type="hidden" name="cantidad" value="1"><button class="btn btn-red w-100" type="submit">AGREGAR AL CARRITO</button>
          </form>
        <?php else: ?><button class="btn btn-secondary w-100 mb-2" type="button" disabled>AGOTADO</button><?php endif; ?>
        <a class="btn btn-red" href="<?= e(app_url('detalle_producto.php?id=' . (int) $product['id'])) ?>">VER DETALLES</a>
      </div>
    </article></div>
  <?php endforeach; ?>
</div>
<?php if ($pages > 1): ?><nav class="mt-4" aria-label="Paginación"><ul class="pagination"><?php for ($p = 1; $p <= $pages; $p++): ?><li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link bg-dark border-secondary text-white" href="?<?= e(http_build_query(array_merge($_GET, ['pagina' => $p]))) ?>"><?= $p ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
