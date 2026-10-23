<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Detectar automáticamente si estamos en entorno local (XAMPP / Laragon / CLI)
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($httpHost, ['localhost', '127.0.0.1', '::1'], true)
    || str_starts_with($httpHost, 'localhost:')
    || str_starts_with($httpHost, '127.0.0.1:')
    || str_ends_with($httpHost, '.trycloudflare.com')
    || str_ends_with($httpHost, '.loca.lt')
    || (PHP_SAPI === 'cli' && empty(getenv('JEJ_DB_HOST')));

if ($isLocal) {
    // =========================================================================
    // 1. ENTORNO LOCAL (XAMPP / Laragon)
    // =========================================================================
    $dbHost     = getenv('JEJ_DB_HOST') ?: '127.0.0.1';
    $dbName     = getenv('JEJ_DB_NAME') ?: 'jej_streetwear';
    $dbUser     = getenv('JEJ_DB_USER') ?: 'root';
    $dbPassword = getenv('JEJ_DB_PASSWORD') ?: '';
} else {
    // =========================================================================
    // 2. ENTORNO HOSTING (InfinityFree / cPanel / Servidor Remoto)
    // Usa variables de entorno o un archivo .env en tu servidor; no publiques
    // credenciales reales dentro del repositorio.
    // =========================================================================
    $dbHost     = getenv('JEJ_DB_HOST') ?: '';
    $dbName     = getenv('JEJ_DB_NAME') ?: '';
    $dbUser     = getenv('JEJ_DB_USER') ?: '';
    $dbPassword = getenv('JEJ_DB_PASSWORD') ?: '';

    if ($dbHost === '' || $dbName === '' || $dbUser === '') {
        http_response_code(500);
        exit("<h1>Configuración de base de datos pendiente</h1><p>Define JEJ_DB_HOST, JEJ_DB_NAME, JEJ_DB_USER y JEJ_DB_PASSWORD en tu entorno antes de publicar el proyecto.</p>");
    }
}

try {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
    $mysqli->set_charset('utf8mb4');

    // Auto-inicializar tablas y datos si la base de datos está vacía
    $tableCheck = $mysqli->query("SHOW TABLES LIKE 'categorias'");
    if ($tableCheck && $tableCheck->num_rows === 0) {
        $sqlPath = __DIR__ . '/../database/jej_streetwear_hosting.sql';
        if (file_exists($sqlPath)) {
            $sqlContent = file_get_contents($sqlPath);
            if ($sqlContent && $mysqli->multi_query($sqlContent)) {
                do {
                    if ($res = $mysqli->store_result()) {
                        $res->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            }
        }
    }
} catch (mysqli_sql_exception $exception) {
    error_log('JEJ database connection failed: ' . $exception->getMessage());
    http_response_code(500);

    // Mensaje de diagnóstico amigable en caso de error de configuración
    $errorMessage = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
    $currentHost  = htmlspecialchars($dbHost, ENT_QUOTES, 'UTF-8');
    $currentUser  = htmlspecialchars($dbUser, ENT_QUOTES, 'UTF-8');
    $currentDb    = htmlspecialchars($dbName, ENT_QUOTES, 'UTF-8');

    exit("
    <!DOCTYPE html>
    <html lang='es'>
    <head>
      <meta charset='utf-8'>
      <meta name='viewport' content='width=device-width, initial-scale=1'>
      <title>Error de Base de Datos - JEJ Streetwear</title>
      <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #0b0b0f; color: #f3f4f6; margin: 0; padding: 40px 20px; }
        .box { max-width: 650px; margin: 0 auto; background: #16161f; border: 1px solid #dc2626; border-radius: 12px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h1 { color: #ef4444; font-size: 1.5rem; margin-top: 0; }
        p { color: #9ca3af; line-height: 1.6; }
        ul { background: #20202e; border-radius: 8px; padding: 15px 25px; margin: 20px 0; list-style: none; }
        li { padding: 5px 0; color: #e5e7eb; }
        code { background: #2d2d3f; color: #f87171; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
        .detail { background: #262638; border-left: 4px solid #ef4444; padding: 12px; margin-top: 20px; font-family: monospace; font-size: 0.85em; color: #fca5a5; word-break: break-all; }
        .help { background: #1a2332; border-left: 4px solid #3b82f6; padding: 12px; margin-top: 15px; font-size: 0.9em; color: #93c5fd; }
      </style>
    </head>
    <body>
      <div class='box'>
        <h1>No fue posible conectar con la base de datos</h1>
        <p>El sitio web no pudo conectarse a MySQL con los datos configurados en <code>config/db.php</code>:</p>
        <ul>
          <li><strong>MySQL Host:</strong> <code>{$currentHost}</code></li>
          <li><strong>Base de datos:</strong> <code>{$currentDb}</code></li>
          <li><strong>Usuario:</strong> <code>{$currentUser}</code></li>
        </ul>
        <div class='detail'><strong>Respuesta de MySQL:</strong> {$errorMessage}</div>
        <div class='help'>
          <strong>¿Cómo solucionarlo en tu hosting?</strong><br>
          Abre el archivo <code>config/db.php</code> en el Administrador de Archivos de tu hosting y escribe los datos exactos que te muestra el panel (MySQL Hostname, Database Name, Username y Contraseña).
        </div>
      </div>
    </body>
    </html>
    ");
}
