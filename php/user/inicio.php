<?php
session_start();
if ($_SESSION['rol'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

$id = $_SESSION['user_id'];
$pedidos = $conn->query("SELECT * FROM pedidos WHERE usuario_id=$id ORDER BY creado DESC LIMIT 3");
?>
<h2>Bienvenido</h2>
<h3>Pedidos recientes</h3>
<ul>
<?php while($row = $pedidos->fetch_assoc()): ?>
    <li>Pedido #<?= $row['id'] ?> - Estado: <?= $row['estado'] ?> - Total: $<?= $row['total'] ?></li>
<?php endwhile; ?>
</ul>
