<?php
session_start();
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

// Obtener categorías existentes para el select
$catRes = $conn->query("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria <> '' ORDER BY categoria ASC");
$categories = [];
if ($catRes) {
    while ($cr = $catRes->fetch_assoc()) { $categories[] = $cr['categoria']; }
    $catRes->free();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = trim($_POST['precio'] ?? '0');
    $stock = trim($_POST['stock'] ?? '0');
    $categoria = trim($_POST['categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validaciones básicas
    if ($nombre === '') $errors[] = 'El nombre es requerido.';
    if (!is_numeric($precio) || $precio < 0) $errors[] = 'Precio inválido.';
    if (!is_numeric($stock) || $stock < 0) $errors[] = 'Stock inválido.';
    // Validar que la categoría seleccionada esté entre las existentes (si hay categorías)
    if (!empty($categories) && $categoria !== '' && !in_array($categoria, $categories)) {
        $errors[] = 'Categoría inválida. Selecciona una de la lista.';
    }

    // Manejo de imagen
    $imagen_path = null;
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
                // Intentar redimensionar la imagen a un máximo (800x800) para ahorrar espacio y homogeneizar
                $maxSize = 800;
                $srcPath = $targetPath;
                $resizedOk = false;
                if (function_exists('getimagesize') && function_exists('imagecreatetruecolor')) {
                    $info = @getimagesize($srcPath);
                    if ($info) {
                        list($width, $height, $type) = $info;
                        $ratio = min($maxSize / $width, $maxSize / $height, 1);
                        $newW = (int) max(1, $width * $ratio);
                        $newH = (int) max(1, $height * $ratio);

                        if ($newW !== $width || $newH !== $height) {
                            switch ($type) {
                                case IMAGETYPE_JPEG:
                                    $srcImg = imagecreatefromjpeg($srcPath);
                                    break;
                                case IMAGETYPE_PNG:
                                    $srcImg = imagecreatefrompng($srcPath);
                                    break;
                                case IMAGETYPE_WEBP:
                                    if (function_exists('imagecreatefromwebp')) {
                                        $srcImg = imagecreatefromwebp($srcPath);
                                    } else {
                                        $srcImg = null;
                                    }
                                    break;
                                case IMAGETYPE_GIF:
                                    $srcImg = imagecreatefromgif($srcPath);
                                    break;
                                default:
                                    $srcImg = null;
                            }

                            if ($srcImg) {
                                $dstImg = imagecreatetruecolor($newW, $newH);
                                // Preserve transparency for PNG/GIF
                                if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
                                    imagealphablending($dstImg, false);
                                    imagesavealpha($dstImg, true);
                                    $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
                                    imagefilledrectangle($dstImg, 0, 0, $newW, $newH, $transparent);
                                }
                                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $width, $height);

                                // Sobrescribir el archivo subido con la versión redimensionada
                                switch ($type) {
                                    case IMAGETYPE_JPEG:
                                        imagejpeg($dstImg, $srcPath, 85);
                                        break;
                                    case IMAGETYPE_PNG:
                                        imagepng($dstImg, $srcPath, 6);
                                        break;
                                    case IMAGETYPE_WEBP:
                                        if (function_exists('imagewebp')) imagewebp($dstImg, $srcPath, 80);
                                        break;
                                    case IMAGETYPE_GIF:
                                        imagegif($dstImg, $srcPath);
                                        break;
                                }

                                imagedestroy($srcImg);
                                imagedestroy($dstImg);
                                $resizedOk = true;
                            }
                        } else {
                            // La imagen ya está dentro del tamaño máximo
                            $resizedOk = true;
                        }
                    }
                }

                // Guardar ruta relativa a proyecto (usar imagen redimensionada o original)
                $imagen_path = 'imagenes/' . $newName;
            }
        }
    }

    if (empty($errors)) {
        // Insertar en DB usando prepared statement
        $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, stock, categoria, descripcion, imagen) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $errors[] = 'Error en la preparación de la consulta: ' . $conn->error;
        } else {
            // tipos: s = string, d = double, i = int
            $stmt->bind_param('sdisss', $nombre, $precio, $stock, $categoria, $descripcion, $imagen_path);
            if ($stmt->execute()) {
                $stmt->close();
                header('Location: productos.php?added=1');
                exit;
            } else {
                $errors[] = 'Error al insertar en la base de datos: ' . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo Producto - JE&J Admin</title>
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
            <h2>Agregar Producto Nuevo</h2>
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
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" step="0.01" id="precio" name="precio" value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <?php if (!empty($categories)): ?>
                        <select id="categoria" name="categoria">
                            <option value="">-- Sin categoría --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_POST['categoria']) && $_POST['categoria'] === $cat) ? 'selected':''; ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select id="categoria" name="categoria">
                            <option value="">-- Sin categorías disponibles --</option>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen (JPG/PNG/WEBP/GIF)</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">Agregar Producto</button>
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
