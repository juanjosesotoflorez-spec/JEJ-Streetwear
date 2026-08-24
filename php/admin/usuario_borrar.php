<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success'=>false,'error'=>'No autorizado']);
    exit;
}
include('../config/db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { echo json_encode(['success'=>false,'error'=>'ID inválido']); exit; }

// Evitar eliminar al usuario admin actual accidentalmente
if (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) === $id) {
    echo json_encode(['success'=>false,'error'=>'No puedes eliminar tu propia cuenta mientras estás logueado.']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
    exit;
} else {
    echo json_encode(['success'=>false,'error'=>'Error al eliminar: '.$stmt->error]);
    exit;
}
