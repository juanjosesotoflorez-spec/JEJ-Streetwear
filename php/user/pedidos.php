<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

$usuario_id = $_SESSION['user_id'];
$result = $conn->query("SELECT id, total, estado, creado FROM pedidos WHERE usuario_id = $usuario_id ORDER BY creado DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Pedidos - JE&J Streetwear</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0a0a0a, #1a0000);
    color: #fff;
    margin: 0;
}
h2 { text-align: center; color: #ff3333; margin: 1.5rem 0; }
table {
    width: 90%; margin: 1rem auto;
    border-collapse: collapse;
    background: #111;
    border-radius: 10px;
    overflow: hidden;
}
th, td {
    padding: 1rem;
    text-align: center;
    border-bottom: 1px solid #333;
}
th {
    background: #b30000;
    color: #fff;
}
tr:hover { background: #1a0000; }
.estado.pendiente { color: orange; font-weight: bold; }
.estado.entregado { color: #00cc66; font-weight: bold; }
.estado.enviado { color: #00b3ff; font-weight: bold; }
.estado.cancelado { color: red; font-weight: bold; }
footer {
    text-align: center; padding: 1rem;
    background: #0d0d0d; margin-top: 2rem;
}
</style>
</head>
<body>
<h2>Mis Pedidos</h2>
<table>
<tr>
    <th>ID</th>
    <th>Total ($)</th>
    <th>Estado</th>
    <th>Fecha</th>
</tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['id']) ?></td>
    <td>$<?= number_format($row['total'], 2) ?></td>
    <td class="estado <?= strtolower($row['estado']) ?>"><?= htmlspecialchars($row['estado']) ?></td>
    <td><?= htmlspecialchars($row['creado']) ?></td>
</tr>
<?php endwhile; ?>
</table>
<footer>
<p>© <?= date('Y') ?> JE&J Streetwear</p>
</footer>
</body>
</html>


