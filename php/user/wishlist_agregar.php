<?php
header('Content-Type: application/json');
session_start();
include("../config/db.php");

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Debes iniciar sesión para usar wishlist."]);
    exit;
}

$usuario_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$producto_id = intval($data['product_id'] ?? 0);

// Validar producto
if ($producto_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID de producto inválido."]);
    exit;
}

// Verificar si ya está en la wishlist
$check = $conn->prepare("SELECT id FROM wishlist WHERE usuario_id=? AND producto_id=?");
if (!$check) {
    http_response_code(500);
    echo json_encode(["success"=>false, "message"=>"Error interno (prepare check): " . $conn->error]);
    exit;
}
$check->bind_param("ii", $usuario_id, $producto_id);
$check->execute();
$result = $check->get_result();

if ($result && $result->num_rows > 0) {
    echo json_encode(["success" => false, "exists" => true, "message" => "Producto ya en wishlist."]);
    exit;
}

// Insertar nuevo producto en wishlist
$stmt = $conn->prepare("INSERT INTO wishlist (usuario_id, producto_id) VALUES (?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success"=>false, "message"=>"Error interno (prepare insert): " . $conn->error]);
    exit;
}
$stmt->bind_param("ii", $usuario_id, $producto_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Agregado a wishlist."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al agregar a wishlist: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

