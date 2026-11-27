<?php
/** Unified public document root. Application sources and persistent data stay outside it. */
declare(strict_types=1);

$root = dirname(__DIR__);
$path = rawurldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

function ms_public_file(string $base, string $relative, array $extensions): never
{
    $root = realpath($base);
    $file = realpath($base . '/' . ltrim($relative, '/'));
    $extension = strtolower(pathinfo((string) $file, PATHINFO_EXTENSION));
    if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file) || !in_array($extension, $extensions, true)) {
        http_response_code(404);
        exit('Archivo no encontrado.');
    }
    $mimes = ['css'=>'text/css','js'=>'text/javascript','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml','ico'=>'image/x-icon','woff'=>'font/woff','woff2'=>'font/woff2','txt'=>'text/plain','md'=>'text/markdown','csv'=>'text/csv','pdf'=>'application/pdf','zip'=>'application/zip'];
    header('Content-Type: ' . ($mimes[$extension] ?? 'application/octet-stream'));
    header('Cache-Control: public, max-age=86400');
    header('Content-Length: ' . filesize($file));
    if (!in_array($extension, ['css','js','png','jpg','jpeg','gif','webp','svg','ico','woff','woff2'], true)) header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') readfile($file);
    exit;
}

if (str_contains($path, "\0") || str_contains($path, '\\') || preg_match('#(?:^|/)\.[^/]*(?:/|$)#', $path)) {
    http_response_code(404);
    exit('Página no encontrada.');
}
if (preg_match('#^/(?:static|uploads)/#', $path)) {
    $isUpload = str_starts_with($path, '/uploads/');
    if ($isUpload) {
        require_once $root . '/blog/app/helpers.php';
        ms_public_file((string) app_config('uploads_path'), substr($path, 9), ['png','jpg','jpeg','gif','webp','txt','md','csv','pdf','zip']);
    }
    ms_public_file($root . '/blog/public/static', substr($path, 8), ['css','js','png','jpg','jpeg','gif','webp','svg','ico','woff','woff2','txt','md','csv','pdf','zip']);
}
if ($path === '/cv' || $path === '/cv/' || $path === '/cv.html') {
    header('Content-Type: text/html; charset=utf-8');
    header('X-Robots-Tag: noindex, follow');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') readfile($root . '/cv/cv.html');
    exit;
}
if (in_array($path, ['/jovani-trevino', '/jovani-trevino/'], true)) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
    } else {
        header('Location: /portfolio/', true, 301);
    }
    exit;
}
if (preg_match('#^/(casos-anonimos|anonymous-case-studies)(?:/([a-z0-9-]{1,64}))?/?$#D', $path, $caseRoute)) {
    $caseLocale = $caseRoute[1] === 'casos-anonimos' ? 'es' : 'en';
    $caseSlug = $caseRoute[2] ?? '';
    $caseMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($caseMethod, ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        exit;
    }
    require $root . '/cv/cases.php';
    exit;
}
if ($path === '/etica/documento.pdf') {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        exit;
    }
    ms_public_file($root . '/cv', 'codigo-de-etica-profesional.pdf', ['pdf']);
}
if (in_array($path, ['/etica', '/etica/', '/es/etica', '/es/etica/', '/ethics', '/ethics/'], true)) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        exit;
    }
    $ethicsLocale = str_starts_with($path, '/ethics') ? 'en' : 'es';
    $ethicsPath = $ethicsLocale === 'en' ? '/ethics' : '/etica';
    if ($path !== $ethicsPath) {
        header('Location: ' . $ethicsPath, true, 301);
        exit;
    }
    header('Content-Type: text/html; charset=UTF-8');
    if ($method !== 'HEAD') require $root . '/cv/ethics.php';
    exit;
}
if (str_starts_with($path, '/cv/')) ms_public_file($root . '/cv', substr($path, 4), ['css','js','jpg','jpeg','png','webp','svg','woff','woff2']);
if ($path === '/Jovani' || str_starts_with($path, '/Jovani/')) {
    header('Location: /portfolio' . substr($path, 7) . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : ''), true, 301);
    exit;
}
if ($path === '/portfolio' || str_starts_with($path, '/portfolio/')) {
    $_SERVER['JOVANI_ORIGINAL_SCRIPT_NAME'] = '/portfolio/index.php';
    $_SERVER['SCRIPT_NAME'] = '/portfolio/index.php';
    require $root . '/Jovani/router.php';
    exit;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/blog/app/bootstrap.php';
