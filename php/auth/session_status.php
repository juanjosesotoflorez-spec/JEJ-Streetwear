<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$response = [
    'logged' => false,
    'role' => null,
    'display_name' => null,
];

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $response['logged'] = true;
    $response['role'] = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
    // If there's a display name in session, use it; otherwise fallback to email or user_id
    if (isset($_SESSION['display_name']) && !empty($_SESSION['display_name'])) {
        $response['display_name'] = $_SESSION['display_name'];
    } elseif (isset($_SESSION['email'])) {
        $response['display_name'] = $_SESSION['email'];
    } else {
        $response['display_name'] = $_SESSION['user_id'];
    }
}

echo json_encode($response);
exit;

?>
