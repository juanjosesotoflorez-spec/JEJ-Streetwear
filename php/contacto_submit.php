<?php
session_start();
header('Content-Type: text/plain; charset=utf-8');
include("../config/db.php");

// Método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "ERROR: Método inválido";
    exit;
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email  = isset($_POST['email']) ? trim($_POST['email']) : '';
$asunto = isset($_POST['asunto']) ? trim($_POST['asunto']) : '';
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

$usuario_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// Si no hay usuario en sesión, intentamos resolverlo por email (vincular mensaje al usuario)
if (is_null($usuario_id)) {
    $tryStmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    if ($tryStmt) {
        $tryStmt->bind_param('s', $email);
        if ($tryStmt->execute()) {
            $resTry = $tryStmt->get_result();
            if ($rowTry = $resTry->fetch_assoc()) {
                $usuario_id = intval($rowTry['id']);
            }
        }
        $tryStmt->close();
    }
}

// Log de intento de resolución por email (útil en debugging)
@file_put_contents(__DIR__ . '/../logs/contact_success.log', date('c') . " - resolve attempt email={$email} -> user_id=" . var_export($usuario_id, true) . "\n", FILE_APPEND);

if ($nombre === '' || $email === '' || $mensaje === '' || $asunto === '') {
    http_response_code(400);
    echo "ERROR: Datos incompletos";
    exit;
}

// Validación básica de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "ERROR: Email inválido";
    exit;
}

// Prepared statement para insertar
if (is_null($usuario_id)) {
    // Insertar con usuario_id NULL explícito
    $stmt = $conn->prepare("INSERT INTO contactos (usuario_id, nombre, email, asunto, mensaje, creado) VALUES (NULL, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        @file_put_contents(__DIR__ . '/../logs/contact_errors.log', date('c') . " - prepare error (null user): " . $conn->error . "\n", FILE_APPEND);
        http_response_code(500);
        echo "ERROR: prepare failed - " . $conn->error;
        exit;
    }
    $stmt->bind_param('ssss', $nombre, $email, $asunto, $mensaje);
} else {
    $stmt = $conn->prepare("INSERT INTO contactos (usuario_id, nombre, email, asunto, mensaje, creado) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        @file_put_contents(__DIR__ . '/../logs/contact_errors.log', date('c') . " - prepare error (with user): " . $conn->error . "\n", FILE_APPEND);
        http_response_code(500);
        echo "ERROR: prepare failed - " . $conn->error;
        exit;
    }
    $stmt->bind_param('issss', $usuario_id, $nombre, $email, $asunto, $mensaje);
}

if ($stmt->execute()) {
    echo "OK";
    // Log éxito con detalle para debugging: id insertado y usuario asociado
    $inserted_id = $stmt->insert_id;
    @file_put_contents(__DIR__ . '/../logs/contact_success.log', date('c') . " - inserted id={$inserted_id} usuario_id=" . var_export($usuario_id, true) . " email={$email} asunto=" . substr($asunto,0,100) . "\n", FILE_APPEND);
    $stmt->close();
    exit;
} else {
    // log
    @file_put_contents(__DIR__ . '/../logs/contact_errors.log', date('c') . " - execute error: " . $stmt->error . "\n", FILE_APPEND);
    http_response_code(500);
    echo "ERROR: " . $stmt->error;
    $stmt->close();
    exit;
}

?>
