<?php
session_start();
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

// KPIs
$ventasDiarias = $conn->query("SELECT SUM(total) as total FROM pedidos WHERE DATE(creado)=CURDATE()")->fetch_assoc()['total'] ?? 0;
$ventasMensuales = $conn->query("SELECT SUM(total) as total FROM pedidos WHERE MONTH(creado)=MONTH(CURDATE())")->fetch_assoc()['total'] ?? 0;
$usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/resumen.css">
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
        <section class="dashboard">
            <h2>📊 Panel Principal / Resumen</h2>
            <div class="cards">
                <div class="card rojo">
                    <h3>Ventas Hoy</h3>
                    <p>$<?= number_format($ventasDiarias, 2) ?></p>
                </div>
                <div class="card negro">
                    <h3>Ventas del Mes</h3>
                    <p>$<?= number_format($ventasMensuales, 2) ?></p>
                </div>
                <div class="card gris">
                    <h3>Usuarios Registrados</h3>
                    <p><?= $usuarios ?></p>
                </div>
            </div>
            <a href="dashboard.php" class="btn-volver">⬅ Volver</a>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> JE&J Streetwear | Panel de Administración</p>
    </footer>
</body>
</html>
