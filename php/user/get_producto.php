<?php
header('Content-Type: application/json');
include('../config/db.php');
$pid = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pid <= 0) { echo json_encode(['ok'=>false,'msg'=>'id inválido']); exit; }
$r = $conn->query("SELECT id, nombre, imagen, precio FROM productos WHERE id=$pid LIMIT 1");
if (!$r || $r->num_rows == 0) { echo json_encode(['ok'=>false,'msg'=>'producto no encontrado']); exit; }
$p = $r->fetch_assoc();
echo json_encode(['ok'=>true,'producto'=>$p]);
exit;
