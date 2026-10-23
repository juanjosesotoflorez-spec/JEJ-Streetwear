<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';

echo "<h1>Actualizando Catálogo Jordan en InfinityFree...</h1>\n";

// 1. Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS usuarios (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(120) NOT NULL,
      correo VARCHAR(190) NOT NULL UNIQUE,
      telefono VARCHAR(30) NULL,
      password_hash VARCHAR(255) NOT NULL,
      rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
      creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS categorias (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(80) NOT NULL UNIQUE,
      activa TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS productos (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      categoria_id INT UNSIGNED NOT NULL,
      nombre VARCHAR(150) NOT NULL,
      descripcion TEXT NOT NULL,
      precio DECIMAL(12,2) NOT NULL,
      imagen VARCHAR(255) NOT NULL DEFAULT 'assets/img/product-placeholder.svg',
      activo TINYINT(1) NOT NULL DEFAULT 1,
      creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
      INDEX idx_productos_activo_categoria (activo, categoria_id),
      INDEX idx_productos_nombre (nombre)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS tallas (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(20) NOT NULL UNIQUE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS producto_tallas (
      producto_id BIGINT UNSIGNED NOT NULL,
      talla_id INT UNSIGNED NOT NULL,
      existencias INT UNSIGNED NOT NULL DEFAULT 0,
      PRIMARY KEY (producto_id, talla_id),
      CONSTRAINT fk_producto_tallas_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
      CONSTRAINT fk_producto_tallas_talla FOREIGN KEY (talla_id) REFERENCES tallas(id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS pedidos (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      usuario_id BIGINT UNSIGNED NOT NULL,
      nombre_envio VARCHAR(120) NOT NULL,
      correo_envio VARCHAR(190) NOT NULL,
      telefono_envio VARCHAR(30) NULL,
      direccion VARCHAR(255) NOT NULL,
      ciudad VARCHAR(100) NOT NULL,
      total DECIMAL(12,2) NOT NULL,
      estado ENUM('pendiente','pagado','rechazado','preparando','enviado','cancelado') NOT NULL DEFAULT 'pendiente',
      inventario_descontado TINYINT(1) NOT NULL DEFAULT 0,
      creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS detalle_pedido (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      pedido_id BIGINT UNSIGNED NOT NULL,
      producto_id BIGINT UNSIGNED NOT NULL,
      talla_id INT UNSIGNED NOT NULL,
      nombre_producto VARCHAR(150) NOT NULL,
      nombre_talla VARCHAR(20) NOT NULL,
      cantidad INT UNSIGNED NOT NULL,
      precio_unitario DECIMAL(12,2) NOT NULL,
      CONSTRAINT fk_detalle_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
      CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
      CONSTRAINT fk_detalle_talla FOREIGN KEY (talla_id) REFERENCES tallas(id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS pagos (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      pedido_id BIGINT UNSIGNED NOT NULL,
      metodo VARCHAR(40) NOT NULL DEFAULT 'simulacion_educativa',
      resultado ENUM('aprobado','rechazado','pendiente') NOT NULL,
      referencia VARCHAR(40) NOT NULL UNIQUE,
      creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_pagos_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS mensajes_contacto (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(120) NOT NULL,
      correo VARCHAR(190) NOT NULL,
      asunto VARCHAR(160) NOT NULL,
      mensaje TEXT NOT NULL,
      creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB"
];

foreach ($tables as $tSql) {
    if (!$mysqli->query($tSql)) {
        die("Error creando tabla: " . $mysqli->error);
    }
}
echo "<p>✓ Tablas verificadas y listas.</p>\n";

// 2. Categories & Tallas
$mysqli->query("INSERT IGNORE INTO categorias (nombre) VALUES ('Camisetas'), ('Sudaderas'), ('Pantalones'), ('Zapatillas'), ('Chaquetas'), ('Accesorios')");
$mysqli->query("INSERT IGNORE INTO tallas (nombre) VALUES ('XS'), ('S'), ('M'), ('L'), ('XL'), ('XXL'), ('Única'), ('38'), ('39'), ('40'), ('41'), ('42'), ('43')");
echo "<p>✓ Categorías y tallas registradas.</p>\n";

// Map category IDs
$catsMap = [];
$resCats = $mysqli->query("SELECT id, nombre FROM categorias");
while ($r = $resCats->fetch_assoc()) {
    $catsMap[$r['nombre']] = (int) $r['id'];
}

// Map tallas IDs
$tallasMap = [];
$resTallas = $mysqli->query("SELECT id, nombre FROM tallas");
while ($r = $resTallas->fetch_assoc()) {
    $tallasMap[$r['nombre']] = (int) $r['id'];
}

// 3. Products JSON
$catalogJsonPath = __DIR__ . '/scripts/catalog_data.json';
if (!file_exists($catalogJsonPath)) {
    die("No se encontró catalog_data.json");
}
$products = json_decode(file_get_contents($catalogJsonPath), true);

$stmtCheck = $mysqli->prepare("SELECT id FROM productos WHERE nombre = ?");
$stmtInsert = $mysqli->prepare("INSERT INTO productos (categoria_id, nombre, descripcion, precio, imagen, activo) VALUES (?, ?, ?, ?, ?, 1)");
$stmtUpdate = $mysqli->prepare("UPDATE productos SET categoria_id = ?, descripcion = ?, precio = ?, imagen = ?, activo = 1 WHERE id = ?");
$stmtSize = $mysqli->prepare("INSERT IGNORE INTO producto_tallas (producto_id, talla_id, existencias) VALUES (?, ?, ?)");

$inserted = 0;
$updated = 0;

foreach ($products as $p) {
    $catId = $catsMap[$p['categoria']] ?? 0;
    if (!$catId) continue;

    $stmtCheck->bind_param('s', $p['nombre']);
    $stmtCheck->execute();
    $existing = $stmtCheck->get_result()->fetch_assoc();

    $prodId = 0;
    if ($existing) {
        $prodId = (int) $existing['id'];
        $stmtUpdate->bind_param('isdsi', $catId, $p['descripcion'], $p['precio'], $p['relative_image'], $prodId);
        $stmtUpdate->execute();
        $updated++;
    } else {
        $stmtInsert->bind_param('issds', $catId, $p['nombre'], $p['descripcion'], $p['precio'], $p['relative_image']);
        $stmtInsert->execute();
        $prodId = (int) $mysqli->insert_id;
        $inserted++;
    }

    // Assign sizes & inventory
    if ($p['categoria'] === 'Zapatillas') {
        $shoeSizes = ['38', '39', '40', '41', '42', '43'];
        foreach ($shoeSizes as $s) {
            if (isset($tallasMap[$s])) {
                $tId = $tallasMap[$s];
                $stock = 10;
                $stmtSize->bind_param('iii', $prodId, $tId, $stock);
                $stmtSize->execute();
            }
        }
    } elseif ($p['categoria'] === 'Accesorios') {
        if (isset($tallasMap['Única'])) {
            $tId = $tallasMap['Única'];
            $stock = 15;
            $stmtSize->bind_param('iii', $prodId, $tId, $stock);
            $stmtSize->execute();
        }
    } else {
        // Apparel
        $clothSizes = ['S', 'M', 'L', 'XL'];
        foreach ($clothSizes as $s) {
            if (isset($tallasMap[$s])) {
                $tId = $tallasMap[$s];
                $stock = 8;
                $stmtSize->bind_param('iii', $prodId, $tId, $stock);
                $stmtSize->execute();
            }
        }
    }
}

// 4. Hide old placeholder products
$mysqli->query("UPDATE productos SET activo = 0 WHERE imagen LIKE '%placeholder%'");

// 5. Admin & demo user
$pwdHashAdmin = '$2y$10$LDqB0bJX5ooNZvQ8YJvHSulkQ7LAR0yYPFvlH9bG2oArOxnTWsPey';
$pwdHashCliente = '$2y$10$ruKVqHHvpSBKW6SN/VtNLOrJRCNb6RQbmFOtdeAbnJ1VstZuxCH3i';
$mysqli->query("INSERT INTO usuarios (id, nombre, correo, password_hash, rol) VALUES (1, 'Administrador JEJ', 'admin@jej.com', '$pwdHashAdmin', 'admin') ON DUPLICATE KEY UPDATE rol = VALUES(rol)");
$mysqli->query("INSERT INTO usuarios (id, nombre, correo, password_hash, rol) VALUES (2, 'Cliente de Prueba', 'cliente@jej.com', '$pwdHashCliente', 'cliente') ON DUPLICATE KEY UPDATE rol = VALUES(rol)");

// Summary
$summaryRes = $mysqli->query("SELECT c.nombre cat, COUNT(p.id) total FROM categorias c LEFT JOIN productos p ON p.categoria_id = c.id AND p.activo = 1 GROUP BY c.id ORDER BY c.id");

echo "<h2>✓ Base de datos sincronizada exitosamente</h2>";
echo "<p>Productos nuevos insertados: $inserted | Productos actualizados: $updated</p>";
echo "<ul>";
while ($row = $summaryRes->fetch_assoc()) {
    echo "<li><strong>{$row['cat']}:</strong> {$row['total']} productos activos con fotos reales</li>";
}
echo "</ul>";
echo "<p><a href='/pages/catalogo.php' style='display:inline-block;padding:12px 24px;background:#ef4444;color:white;text-decoration:none;border-radius:6px;font-weight:bold;'>IR AL CATÁLOGO &rarr;</a></p>";

