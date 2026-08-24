<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

$usuario_id = intval($_SESSION['user_id']);
$total = floatval($_POST['total'] ?? 0);
$estado = "Pendiente"; // Valor por defecto

if ($total <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Total inválido"]);
    exit;
}

// Insertar pedido
$stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total, estado) VALUES (?, ?, ?)");
$stmt->bind_param("ids", $usuario_id, $total, $estado);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "id" => $stmt->insert_id]);
} else {
    echo json_encode(["error" => "Error al guardar el pedido: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>