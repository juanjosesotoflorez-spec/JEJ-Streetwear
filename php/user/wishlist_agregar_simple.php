<?php
// Endpoint simple: acepta POST form-encoded y devuelve texto plano.
session_start();
include('../config/db.php');

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'AUTH';
    exit;
}

$usuario_id = $_SESSION['user_id'];
$producto_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($producto_id <= 0) {
    http_response_code(400);
    echo 'BADID';
    exit;
}

$check = $conn->prepare("SELECT id FROM wishlist WHERE usuario_id=? AND producto_id=?");
if (!$check) { http_response_code(500); echo 'ERROR:prepare_check '.$conn->error; exit; }
$check->bind_param('ii', $usuario_id, $producto_id);
$check->execute();
$res = $check->get_result();
if ($res && $res->num_rows > 0) { echo 'EXISTS'; exit; }

// Verificar que el producto exista en la tabla productos para evitar violación de FK
$pcheck = $conn->prepare("SELECT id FROM productos WHERE id=?");
if (!$pcheck) { http_response_code(500); echo 'ERROR:prepare_pcheck '.$conn->error; exit; }
$pcheck->bind_param('i', $producto_id);
$pcheck->execute();
$pres = $pcheck->get_result();
if (!$pres || $pres->num_rows == 0) { echo 'NOPRODUCT'; exit; }
$pcheck->close();
// Si el producto no existe, intentar buscar por nombre si se proporcionó
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
if (!$pres || $pres->num_rows == 0) {
    if ($nombre !== '') {
        $nn = $conn->real_escape_string($nombre);
        $s = $conn->query("SELECT id FROM productos WHERE nombre LIKE '%$nn%' LIMIT 1");
        if ($s && $s->num_rows > 0) {
            $row = $s->fetch_assoc();
            $producto_id = intval($row['id']);
            // continuar con inserción usando nuevo producto_id
        } else {
            echo 'NOPRODUCT'; exit;
        }
    } else {
        echo 'NOPRODUCT'; exit;
    }
}

$stmt = $conn->prepare("INSERT INTO wishlist (usuario_id, producto_id, creado) VALUES (?, ?, NOW())");
if (!$stmt) { http_response_code(500); echo 'ERROR:prepare_insert '.$conn->error; exit; }
$stmt->bind_param('ii', $usuario_id, $producto_id);
if ($stmt->execute()) {
    echo 'OK';
} else {
    http_response_code(500);
    echo 'ERROR:'.$stmt->error;
}

$stmt->close();
$conn->close();
exit;
