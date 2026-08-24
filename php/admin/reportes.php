<?php
session_start();
if ($_SESSION['rol'] != 'admin') { header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

$ventas = $conn->query("SELECT SUM(total) as total FROM pedidos")->fetch_assoc()['total'] ?? 0;
$mas_vendido = $conn->query("SELECT producto_id, SUM(cantidad) as total FROM pedido_detalle GROUP BY producto_id ORDER BY total DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes y Estadísticas</title>
    <link rel="stylesheet" href="css/reportes.css">
</head>
<body>

<header class="main-header">
    <div class="logo">JE&J Streetwear</div>
    <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="reportes.php">Reportes</a>
    </nav>
</header>

<div class="container">
    <h2>📊 Reportes y Estadísticas</h2>

    <div class="actions">
        <a href="dashboard.php" class="btn-back">⬅ Volver</a>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Total Ventas Acumuladas</h3>
            <p class="stat">$<?= number_format($ventas, 2) ?></p>
        </div>

        <?php if($mas_vendido): ?>
        <div class="card">
            <h3>Producto Más Vendido</h3>
            <p class="stat">ID <?= $mas_vendido['producto_id'] ?></p>
            <p><?= $mas_vendido['total'] ?> unidades</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer class="main-footer">
    &copy; <?= date("Y") ?> JE&J Streetwear | Panel Admin
</footer>

</body>
</html>

