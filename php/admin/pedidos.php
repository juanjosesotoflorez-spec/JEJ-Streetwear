<?php
session_start();
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

$filtro = isset($_GET['estado']) ? $_GET['estado'] : "";
$sql = "SELECT * FROM pedidos";
if ($filtro != "") $sql .= " WHERE estado='$filtro'";
$pedidos = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos</title>
    <link rel="stylesheet" href="css/pedidos.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo">🛒 Admin JE&J Streetwear</div>
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="productos.php">👕 Productos</a>
            <a href="pedidos.php" class="active">📦 Pedidos</a>
            <a href="../auth/logout.php">🚪 Salir</a>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="container">
        <h2>📦 Gestión de Pedidos</h2>

        <!-- Filtro de estado -->
        <form method="get" class="filter-form">
            <label for="estado">Filtrar por estado:</label>
            <select name="estado" id="estado" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="pendiente" <?= $filtro=="pendiente"?"selected":"" ?>>Pendiente</option>
                <option value="preparacion" <?= $filtro=="preparacion"?"selected":"" ?>>Preparación</option>
                <option value="enviado" <?= $filtro=="enviado"?"selected":"" ?>>Enviado</option>
                <option value="entregado" <?= $filtro=="entregado"?"selected":"" ?>>Entregado</option>
                <option value="cancelado" <?= $filtro=="cancelado"?"selected":"" ?>>Cancelado</option>
            </select>
        </form>

        <!-- Tabla pedidos -->
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while($p = $pedidos->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= $p['usuario_id'] ?></td>
                    <td>$<?= number_format($p['total'], 2) ?></td>
                    <td><span class="estado <?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
                    <td><a class="btn-view" href="pedido_detalle.php?id=<?= $p['id'] ?>">👁️ Ver</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <p>© 2025 JE&J Streetwear | Panel de Administración</p>
    </footer>
</body>
</html>
