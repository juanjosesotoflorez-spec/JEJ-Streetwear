<?php
session_start(); 
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    echo json_encode(['ok'=>false,'error'=>'No autorizado']); 
    exit; 
}
include('../config/db.php');

$id = intval($_GET['id'] ?? 0); 
if ($id <= 0) { 
    echo json_encode(['ok'=>false, 'error'=>'ID inválido']); 
    exit; 
}

$r = $conn->query('SELECT activo FROM cupones WHERE id = ' . $id . ' LIMIT 1'); 
if (!$r || $r->num_rows === 0) { 
    echo json_encode(['ok'=>false, 'error'=>'No encontrado']); 
    exit; 
}

$row = $r->fetch_assoc(); 
$nuevo = $row['activo'] ? 0 : 1;
$stmt = $conn->prepare('UPDATE cupones SET activo = ? WHERE id = ?'); 
$stmt->bind_param('ii', $nuevo, $id); 
if ($stmt->execute()) { 
    echo json_encode(['ok'=>true, 'activo'=> (bool)$nuevo]); 
} else { 
    echo json_encode(['ok'=>false, 'error'=>$stmt->error]);
}
?>
