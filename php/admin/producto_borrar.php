<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

// Obtener la ruta de la imagen para eliminar el archivo
$stmt = $conn->prepare('SELECT imagen FROM productos WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
    exit;
}
$row = $res->fetch_assoc();
$imagen = $row['imagen'];
$stmt->close();

// Eliminar registro
$del = $conn->prepare('DELETE FROM productos WHERE id = ?');
$del->bind_param('i', $id);
if ($del->execute()) {
    // eliminar archivo si existe
    if (!empty($imagen)) {
        $path = realpath(__DIR__ . '/../../' . $imagen);
        if ($path && file_exists($path)) @unlink($path);
    }
    echo json_encode(['success' => true]);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
    exit;
}
