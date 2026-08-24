<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include(__DIR__ . '/../config/db.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$user_id = intval($_SESSION['user_id']);

$email = null;
$uStmt = $conn->prepare("SELECT email FROM usuarios WHERE id = ? LIMIT 1");
if ($uStmt) {
    $uStmt->bind_param('i', $user_id);
    if ($uStmt->execute()) {
        $ur = $uStmt->get_result();
        if ($rowu = $ur->fetch_assoc()) $email = $rowu['email'];
    }
    $uStmt->close();
}

$out = [];
if ($email) {
    $stmt = $conn->prepare("SELECT id, usuario_id, nombre, email, asunto, mensaje, creado FROM contactos WHERE usuario_id = ? OR email = ? ORDER BY creado DESC LIMIT 200");
    if ($stmt) {
        $stmt->bind_param('is', $user_id, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $out[] = $row;
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT id, usuario_id, nombre, email, asunto, mensaje, creado FROM contactos WHERE usuario_id = ? ORDER BY creado DESC LIMIT 200");
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $out[] = $row;
        $stmt->close();
    }
}

echo json_encode($out);

?>
