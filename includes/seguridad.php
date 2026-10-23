<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/funciones.php';

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        exit('La solicitud expiró o no es válida. Recarga la página e inténtalo de nuevo.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        flash('warning', 'Inicia sesión para continuar.');
        redirect('auth/login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['user']['rol'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('No tienes permisos para acceder a esta sección.');
    }
}
