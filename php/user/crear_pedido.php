<?php
session_start();
if ($_SESSION['rol'] != 'user') { header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

// Espera POST JSON con carrito y total
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$uid = $_SESSION['user_id'];
if (!$data || !isset($data['items'])) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'msg'=>'Formato inválido']);
    exit;
}

$items = $conn->real_escape_string(json_encode($data['items']));
$total = floatval($data['total'] ?? 0);

// Insertar pedido simple
$stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, items_json, total, estado, creado) VALUES (?, ?, ?, 'Pendiente', NOW())");
$stmt->bind_param('isd', $uid, $items, $total);
if ($stmt->execute()) {
    $orderId = $stmt->insert_id;
    echo json_encode(['ok'=>true,'order_id'=>$orderId]);
} else {
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'No se pudo crear el pedido']);
}
$stmt->close();
