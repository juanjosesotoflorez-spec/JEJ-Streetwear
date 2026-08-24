<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include(__DIR__ . '/../config/db.php');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$sql = "SELECT c.id, c.usuario_id, IFNULL(u.nombre, 'Anonimo') as usuario, c.nombre, c.email, c.asunto, c.mensaje, c.creado FROM contactos c LEFT JOIN usuarios u ON c.usuario_id=u.id ORDER BY c.creado DESC LIMIT 500";
$res = $conn->query($sql);
$out = [];
if ($res instanceof mysqli_result) {
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
}

echo json_encode($out);

?>
