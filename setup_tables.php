<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$sqlFile = __DIR__ . '/database/jej_streetwear_hosting.sql';
if (!file_exists($sqlFile)) {
    die('Error: No se encontro el archivo SQL en database/jej_streetwear_hosting.sql');
}

$sql = file_get_contents($sqlFile);

if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

if ($mysqli->errno) {
    echo '<h2>Error al importar: ' . htmlspecialchars($mysqli->error, ENT_QUOTES, 'UTF-8') . '</h2>';
    exit;
}

// Resumen del catálogo
$prodsRes = $mysqli->query("SELECT c.nombre categoria, COUNT(p.id) total FROM categorias c LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1 GROUP BY c.id ORDER BY c.id");
$summary = [];
$totalProds = 0;
while ($row = $prodsRes->fetch_assoc()) {
    $summary[] = $row;
    $totalProds += (int) $row['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Actualización de Catálogo Jordan - JEJ Streetwear</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0b0b0f; color: #f3f4f6; margin: 0; padding: 40px 20px; }
    .container { max-width: 700px; margin: 0 auto; background: #16161f; border: 1px solid #2d2d3f; border-radius: 14px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; }
    h1 { color: #10b981; font-size: 1.8rem; margin-top: 0; }
    p { color: #9ca3af; line-height: 1.6; }
    .badge-count { font-size: 2.2rem; font-weight: bold; color: #ef4444; margin: 15px 0; }
    table { width: 100%; margin: 25px 0; border-collapse: collapse; text-align: left; }
    th, td { padding: 10px 14px; border-bottom: 1px solid #262638; }
    th { color: #9ca3af; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
    td { color: #e5e7eb; }
    .btn { display: inline-block; padding: 14px 28px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; transition: background 0.2s; margin-top: 15px; }
    .btn:hover { background: #dc2626; }
  </style>
</head>
<body>
  <div class="container">
    <h1>✓ Catálogo Jordan Actualizado con Éxito</h1>
    <p>Se han cargado las fotos oficiales y productos auténticos de Jordan en la base de datos.</p>
    <div class="badge-count"><?= $totalProds ?> Productos Activos</div>

    <table>
      <thead>
        <tr><th>Sección / Categoría</th><th style="text-align: right;">Prendas con Fotos Reales</th></tr>
      </thead>
      <tbody>
        <?php foreach ($summary as $item): ?>
          <tr>
            <td><strong><?= htmlspecialchars($item['categoria'], ENT_QUOTES, 'UTF-8') ?></strong></td>
            <td style="text-align: right; color: #10b981; font-weight: bold;">✓ <?= (int) $item['total'] ?> productos</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <a href="pages/catalogo.php" class="btn">Ver Catálogo en Vivo &rarr;</a>
  </div>
</body>
</html>

