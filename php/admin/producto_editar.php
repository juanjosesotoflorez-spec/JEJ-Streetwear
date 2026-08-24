<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
include('../config/db.php');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: productos.php');
    exit;
}

$errors = [];
// Obtener producto
$stmt = $conn->prepare('SELECT * FROM productos WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
$stmt->close();
if (!$product) {
    header('Location: productos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = trim($_POST['precio'] ?? '0');
    $stock = trim($_POST['stock'] ?? '0');
    $categoria = trim($_POST['categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '') $errors[] = 'El nombre es requerido.';
    if (!is_numeric($precio) || $precio < 0) $errors[] = 'Precio inválido.';
    if (!is_numeric($stock) || $stock < 0) $errors[] = 'Stock inválido.';

    $imagen_path = $product['imagen']; // por defecto, conservar
    // Manejo de imagen (si se sube una nueva)
    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowed)) {
            $errors[] = 'Tipo de imagen no permitido. Usa JPG, PNG, WEBP o GIF.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^A-Za-z0-9\-_.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
            $newName = $safeName . '_' . time() . '.' . $ext;
            $targetDir = realpath(__DIR__ . '/../../imagenes') ?: __DIR__ . '/../../imagenes';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $newName;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $errors[] = 'Error al guardar la imagen en el servidor.';
            } else {
                // intentar redimensionar (como en producto_nuevo)
                if (function_exists('getimagesize') && function_exists('imagecreatetruecolor')) {
                    $info = @getimagesize($targetPath);
                    if ($info) {
                        list($width, $height, $type) = $info;
                        $maxSize = 800;
                        $ratio = min($maxSize / $width, $maxSize / $height, 1);
                        $newW = (int) max(1, $width * $ratio);
                        $newH = (int) max(1, $height * $ratio);
                        if ($newW !== $width || $newH !== $height) {
                            switch ($type) {
                                case IMAGETYPE_JPEG: $srcImg = imagecreatefromjpeg($targetPath); break;
                                case IMAGETYPE_PNG: $srcImg = imagecreatefrompng($targetPath); break;
                                case IMAGETYPE_WEBP: $srcImg = function_exists('imagecreatefromwebp') ? imagecreatefromwebp($targetPath) : null; break;
                                case IMAGETYPE_GIF: $srcImg = imagecreatefromgif($targetPath); break;
                                default: $srcImg = null;
                            }
                            if ($srcImg) {
                                $dstImg = imagecreatetruecolor($newW, $newH);
                                if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
                                    imagealphablending($dstImg, false);
                                    imagesavealpha($dstImg, true);
                                    $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
                                    imagefilledrectangle($dstImg, 0, 0, $newW, $newH, $transparent);
                                }
                                imagecopyresampled($dstImg, $srcImg, 0,0,0,0, $newW, $newH, $width, $height);
                                switch ($type) {
                                    case IMAGETYPE_JPEG: imagejpeg($dstImg, $targetPath, 85); break;
                                    case IMAGETYPE_PNG: imagepng($dstImg, $targetPath, 6); break;
                                    case IMAGETYPE_WEBP: if (function_exists('imagewebp')) imagewebp($dstImg, $targetPath, 80); break;
                                    case IMAGETYPE_GIF: imagegif($dstImg, $targetPath); break;
                                }
                                imagedestroy($srcImg);
                                imagedestroy($dstImg);
                            }
                        }
                    }
                }
                // borrar imagen antigua si existía y era un path local
                if (!empty($product['imagen']) && strpos($product['imagen'], 'http') !== 0) {
                    $oldPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($product['imagen'], '/');
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $imagen_path = 'imagenes/' . $newName;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('UPDATE productos SET nombre=?, precio=?, stock=?, categoria=?, descripcion=?, imagen=? WHERE id=?');
        if (!$stmt) {
            $errors[] = 'Error al preparar la consulta: ' . $conn->error;
        } else {
            $stmt->bind_param('sdisssi', $nombre, $precio, $stock, $categoria, $descripcion, $imagen_path, $id);
            if ($stmt->execute()) {
                $stmt->close();
                header('Location: productos.php?updated=1');
                exit;
            } else {
                $errors[] = 'Error al actualizar el producto: ' . $stmt->error;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar Producto - JE&J Admin</title>
    <link rel="stylesheet" href="css/productos.css">
    <style>
        .form-container{ max-width:800px; margin:2rem auto; background:#fff; padding:1.5rem; border-radius:8px; color:#222; }
        .form-group{ margin-bottom:1rem; }
        label{ display:block; font-weight:600; margin-bottom:0.4rem; }
        input[type=text], input[type=number], textarea, select{ width:100%; padding:0.6rem; border:1px solid #ccc; border-radius:6px; }
        .errors{ background:#ffe6e6; border:1px solid #ffb3b3; padding:0.8rem; margin-bottom:1rem; border-radius:6px; color:#900; }
        .actions{ display:flex; gap:0.6rem; }
        .btn{ padding:0.6rem 1rem; border-radius:6px; text-decoration:none; display:inline-block; }
        .btn-primary{ background:#b30000; color:#fff; border:none; }
        .btn-secondary{ background:#eee; color:#222; border:none; }
    </style>
</head>
<body>
    <header>
        <div class="logo">JE&J <span>Admin</span></div>
        <nav>
            <a href="resumen_admin.php">Resumen</a>
            <a href="productos.php">Productos</a>
            <a href="pedidos.php">Pedidos</a>
            <a href="usuarios.php">Usuarios</a>
            <a href="../auth/logout.php">Salir</a>
        </nav>
    </header>

    <main>
        <section class="form-container">
            <h2>Editar Producto</h2>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? $product['nombre']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" step="0.01" id="precio" name="precio" value="<?= htmlspecialchars($_POST['precio'] ?? $product['precio']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? $product['stock']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($_POST['categoria'] ?? $product['categoria']) ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($_POST['descripcion'] ?? $product['descripcion']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Imagen actual</label>
                    <div>
                        <?php if (!empty($product['imagen'])): ?>
                            <img src="/<?= ltrim($product['imagen'], '/') ?>" alt="" style="max-width:160px; display:block; margin-bottom:8px;" />
                        <?php else: ?>
                            <div style="width:160px;height:100px;background:#eee;display:flex;align-items:center;justify-content:center;color:#666;">Sin imagen</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagen">Cambiar imagen (opcional)</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> JE&J Streetwear | Panel Admin</p>
    </footer>
</body>
</html>
