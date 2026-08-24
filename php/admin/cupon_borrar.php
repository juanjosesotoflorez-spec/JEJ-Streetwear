<?php
session_start(); header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { echo json_encode(['success'=>false,'error'=>'No autorizado']); exit; }
include('../config/db.php');
$id = intval($_GET['id'] ?? 0); if($id<=0){ echo json_encode(['success'=>false,'error'=>'ID inválido']); exit; }
$stmt = $conn->prepare('DELETE FROM cupones WHERE id = ?'); $stmt->bind_param('i',$id);
if($stmt->execute()){ echo json_encode(['success'=>true]); } else echo json_encode(['success'=>false,'error'=>$stmt->error]);
