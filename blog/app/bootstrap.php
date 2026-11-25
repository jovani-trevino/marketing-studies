<?php

declare(strict_types=1);

require __DIR__ . '/i18n.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/models.php';
require __DIR__ . '/controllers.php';

date_default_timezone_set(app_config('timezone', 'America/Mexico_City'));
ini_set('display_errors', '0');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = (string) app_config('session_path', dirname(__DIR__) . '/storage/sessions');
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0700, true);
    }
    if (!is_dir($sessionPath) || !is_writable($sessionPath)) { http_response_code(503); exit('No pudimos iniciar la sesión. Revisa el almacenamiento del servidor.'); }
    ini_set('session.save_path', $sessionPath);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string) app_config('session_lifetime', 43200));
    session_set_cookie_params(['lifetime'=>0, 'path'=>base_path() . '/' ?: '/', 'secure'=>request_is_secure(), 'httponly'=>true, 'samesite'=>'Lax']);
    if (!session_start()) { http_response_code(503); exit('No pudimos iniciar la sesión. Inténtalo de nuevo.'); }
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if (request_is_secure()) header('Strict-Transport-Security: max-age=31536000');
if (str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/admin')) header('Cache-Control: no-store, private');
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 20971520) {
    http_response_code(413); exit('La solicitud supera el tamaño permitido.');
}

try {
    ensure_runtime_schema();
    dispatch();
} catch (Throwable $error) {
    error_log('Marketing Studies: ' . get_class($error) . ' while processing a request.');
    http_response_code(500);
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Marketing Studies</title><main><h1>No pudimos completar la solicitud.</h1><p>Revisa la configuración del servidor o inténtalo de nuevo.</p></main></html>';
}
