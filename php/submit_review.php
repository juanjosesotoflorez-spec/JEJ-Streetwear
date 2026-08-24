<?php
// Endpoint público para recibir mensajes, reseñas o comentarios desde el frontend
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html'); exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');
$tipo = trim($_POST['tipo'] ?? 'mensaje'); // ejemplo: reseña, pregunta, comentario

if ($nombre === '' || $mensaje === '') {
    header('Location: ../contacto.html?error=1'); exit;
}

$stmt = $conn->prepare('INSERT INTO resenas (nombre, email, tipo, mensaje, created_at) VALUES (?, ?, ?, ?, NOW())');
$stmt->bind_param('ssss', $nombre, $email, $tipo, $mensaje);
if ($stmt->execute()) {
    header('Location: ../contacto.html?sent=1'); exit;
} else {
    header('Location: ../contacto.html?error=1'); exit;
}
