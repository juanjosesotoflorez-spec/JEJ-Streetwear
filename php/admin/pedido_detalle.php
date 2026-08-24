<?php
session_start();
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

// Validar ID del pedido
if (!isset($_GET['id'])) {
    header("Location: pedidos.php");
    exit;
}

$pedido_id = intval($_GET['id']);

// Obtener información del pedido y usuario
$sql_pedido = "SELECT p.*, u.nombre AS usuario_nombre, u.email 
               FROM pedidos p
               JOIN usuarios u ON p.usuario_id = u.id
               WHERE p.id = ?";
$stmt = $conn->prepare($sql_pedido);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    echo "<h2>Pedido no encontrado</h2>";
    exit;
}

// Obtener productos del pedido con detalles reales
$sql_detalle = "SELECT d.*, pr.nombre AS producto_nombre, pr.precio AS precio_unitario, pr.imagen 
                FROM pedido_detalle d
                JOIN productos pr ON d.producto_id = pr.id
                WHERE d.pedido_id = ?";
$stmt2 = $conn->prepare($sql_detalle);
$stmt2->bind_param("i", $pedido_id);
$stmt2->execute();
$detalles = $stmt2->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Pedido #<?= $pedido_id ?></title>
    <link rel="stylesheet" href="css/admin_style.css"> 
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0a0a0a;
            color: white;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #a40000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            color: white;
            font-weight: bold;
        }

        header nav a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: 500;
        }

        header nav a:hover {
            color: #ffcccb;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
            background: #1a1a1a;
            padding: 25px 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(255, 0, 0, 0.4);
        }

        h2 {
            text-align: center;
            color: #ff4d4d;
            margin-bottom: 25px;
        }

        .pedido-info {
            background: #2b0000;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            line-height: 1.8;
        }

        .pedido-info strong {
            color: #ff9999;
        }

        .estado {
            padding: 6px 12px;
            border-radius: 8px;
            color: black;
            font-weight: bold;
        }

        .pendiente { background: #f1c40f; }
        .completado { background: #2ecc71; }
        .cancelado { background: #e74c3c; }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #1c1c1c;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #a40000;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }

        table th {
            background-color: #a40000;
            color: white;
            text-transform: uppercase;
        }

        table tr:hover {
            background-color: #330000;
        }

        img.producto-img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #ff4d4d;
        }

        .btn-back {
            display: inline-block;
            background-color: #a40000;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-back:hover {
            background-color: #ff0000;
        }

        .total-final {
            text-align: right;
            font-size: 1.3em;
            color: #ff4d4d;
            font-weight: bold;
            margin-top: 15px;
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: #111;
            color: #999;
            font-size: 0.9em;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<header>
    <div>🛍️ Admin JE&J Streetwear</div>
    <nav>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="productos.php">👕 Productos</a>
        <a href="pedidos.php">📦 Pedidos</a>
        <a href="../auth/logout.php">🚪 Salir</a>
    </nav>
</header>

<main class="container">
    <h2>🧾 Detalle del Pedido #<?= $pedido['id'] ?></h2>

    <section class="pedido-info">
        <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['usuario_nombre']) ?></p>
        <p><strong>Correo:</strong> <?= htmlspecialchars($pedido['email']) ?></p>
        <p><strong>Fecha:</strong> <?= $pedido['creado'] ?></p>
        <p><strong>Estado:</strong> 
            <span class="estado <?= strtolower($pedido['estado']) ?>">
                <?= ucfirst($pedido['estado']) ?>
            </span>
        </p>
        <p><strong>Total:</strong> $<?= number_format($pedido['total'], 2) ?></p>
    </section>

    

   

    <a href="pedidos.php" class="btn-back">⬅️ Volver a pedidos</a>
</main>

<footer>
    © 2025 JE&J Streetwear — Panel de Administración
</footer>

</body>
</html>
