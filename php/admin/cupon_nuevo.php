<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
include('../config/db.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $descuento = floatval($_POST['descuento'] ?? 0);
    $fecha = $_POST['fecha_expiracion'] ?? null;
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validaciones
    if ($codigo === '') $errors[] = 'Código requerido.';
    if ($descuento <= 0) $errors[] = 'Descuento inválido.';
    if ($fecha === '') $errors[] = 'Fecha de expiración requerida.';

    if (empty($errors)) {
        // Inserta el nuevo cupón en la base de datos
        $stmt = $conn->prepare('INSERT INTO cupones (codigo, descuento, fecha_expiracion, activo) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sdsi', $codigo, $descuento, $fecha, $activo);
        if ($stmt->execute()) {
            // Redirige a la página de marketing con un mensaje de éxito
            header('Location: marketing.php?added=1');
            exit;
        } else {
            $errors[] = 'Error al insertar: ' . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo Cupón</title>
    <link rel="stylesheet" href="css/marketing.css">
</head>
<body>
<header class="main-header">
    <div class="logo">📢 Marketing JE&J</div>
</header>

<main class="container">
    <h2>Nuevo Cupón</h2>
    <?php if(!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post">
        <label>Código <input name="codigo" required></label><br>
        <label>Descuento (%) <input name="descuento" type="number" step="0.01" required></label><br>
        <label>Fecha expiración <input name="fecha_expiracion" type="date" required></label><br>
        <label><input type="checkbox" name="activo" checked> Activo</label><br>
        <button type="submit">Crear Cupón</button>
        <a href="marketing.php">Cancelar</a>
    </form>
</main>
</body>
</html>
