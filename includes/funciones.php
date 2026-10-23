<?php
declare(strict_types=1);

function app_url(string $path = ''): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.html');
    $separator = strrpos($script, '/');
    $base = $separator === false ? '' : substr($script, 0, $separator);
    $lastSeparator = strrpos($base, '/');
    $directoryName = $lastSeparator === false ? $base : substr($base, $lastSeparator + 1);
    if (in_array($directoryName, ['auth', 'admin', 'pages'], true)) {
        $base = $lastSeparator === false ? '' : substr($base, 0, $lastSeparator);
    }
    $base = rtrim($base, '/');
    $path = ltrim($path, '/');
    $queryPosition = strpos($path, '?');
    $route = $queryPosition === false ? $path : substr($path, 0, $queryPosition);
    $query = $queryPosition === false ? '' : substr($path, $queryPosition);
    $publicPages = [
        'catalogo.php',
        'carrito.php',
        'checkout.php',
        'contacto.php',
        'detalle_producto.php',
        'resultado_pago.php',
    ];
    if (in_array($route, $publicPages, true)) {
        $path = 'pages/' . $route . $query;
    }
    return $base . ($path === '' ? '' : '/' . $path);
}

function e(mixed $value): string
{
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($message) ? $message : null;
}

function money(int|float|string $amount): string
{
    return '$' . number_format((float) $amount, 0, ',', '.') . ' COP';
}

function cart_count(): int
{
    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $sizes) {
        foreach ($sizes as $quantity) {
            $count += (int) $quantity;
        }
    }
    return $count;
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function bind_values(mysqli_stmt $statement, string $types, array $values): void
{
    $arguments = [$types];
    foreach ($values as $index => &$value) {
        $arguments[] = &$value;
    }
    call_user_func_array([$statement, 'bind_param'], $arguments);
}

function save_product_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 3 * 1024 * 1024) {
        throw new RuntimeException('La imagen no se pudo subir o supera el límite de 3 MB.');
    }
    $temporaryPath = $file['tmp_name'] ?? '';
    if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
        throw new RuntimeException('El archivo enviado no es una imagen válida.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!is_string($mime) || !isset($extensions[$mime])) {
        throw new RuntimeException('Usa una imagen JPG, PNG o WebP.');
    }
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('No fue posible preparar la carpeta de imágenes.');
    }
    $fileName = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($temporaryPath, $directory . DIRECTORY_SEPARATOR . $fileName)) {
        throw new RuntimeException('No fue posible guardar la imagen.');
    }
    return 'assets/uploads/' . $fileName;
}
