<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Acceso denegado']);
    exit;
}
include('../config/db.php');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['id']) || !isset($data['stock'])) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
    exit;
}

$id = intval($data['id']);
$stock = intval($data['stock']);
if ($id <= 0 || $stock < 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Valores fuera de rango']);
    exit;
}

$stmt = $conn->prepare('UPDATE productos SET stock = ? WHERE id = ?');
if (!$stmt) {
    echo json_encode(['ok'=>false,'error'=>'Error al preparar consulta']);
    exit;
}
$stmt->bind_param('ii', $stock, $id);
if ($stmt->execute()) {
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false,'error'=>$stmt->error]);
}
$stmt->close();
?>
