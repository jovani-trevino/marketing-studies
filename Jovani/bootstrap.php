<?php

declare(strict_types=1);

function jovani_request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $basePath = jovani_base_path();

    if ($basePath !== '') {
        if ($path === $basePath) {
            return '/';
        }
        if (str_starts_with($path, $basePath . '/')) {
            $trimmed = substr($path, strlen($basePath));
            return $trimmed !== '' ? $trimmed : '/';
        }
    }

    return $path;
}

function jovani_base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $scriptName = (string) ($_SERVER['JOVANI_ORIGINAL_SCRIPT_NAME'] ?? $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));

    if ($dir === '.' || $dir === '\\' || $dir === '') {
        $basePath = '';
        return $basePath;
    }

    $basePath = $dir === '/' ? '' : rtrim($dir, '/');
    return $basePath;
}

function jovani_blog_base_path(): string
{
    $configured = (string) ($_SERVER['MS_BLOG_BASE_PATH'] ?? getenv('MS_BLOG_BASE_PATH') ?: '');
    $configured = '/' . trim($configured, '/');
    if ($configured === '/') {
        return '';
    }
    // Only local path prefixes are valid; URLs and traversal segments are rejected.
    return preg_match('#^(?:/[A-Za-z0-9_-]+)+$#', $configured) === 1 ? $configured : '';
}

function jovani_app_url(string $path = '/'): string
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }

    if ($path === '/blog' || str_starts_with($path, '/blog/')) {
        $suffix = '/' . ltrim(substr($path, 5), '/');
        return jovani_blog_base_path() . $suffix;
    }

    $basePath = jovani_base_path();
    if ($basePath === '') {
        return $path;
    }

    return $path === '/' ? $basePath . '/' : $basePath . $path;
}

function jovani_boot_blog(string $locale = 'es'): void
{
    static $loaded = false;

    $_SERVER['JOVANI_ORIGINAL_SCRIPT_NAME'] ??= $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $_SERVER['SCRIPT_NAME'] = jovani_blog_base_path() . '/index.php';

    if (!$loaded) {
        require_once __DIR__ . '/../blog/app/i18n.php';
        require_once __DIR__ . '/../blog/app/helpers.php';
        require_once __DIR__ . '/../blog/app/models.php';
        $loaded = true;
    }

    date_default_timezone_set(app_config('timezone', 'America/Mexico_City'));

    if (session_status() !== PHP_SESSION_ACTIVE) {
        $sessionPath = (string) app_config('session_path', dirname(__DIR__) . '/blog/storage/sessions');
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0700, true);
        }
        if (is_writable($sessionPath)) {
            ini_set('session.save_path', $sessionPath);
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) app_config('session_lifetime', 43200));
        session_set_cookie_params([
            'lifetime' => 0, 'path' => base_path() . '/',
            'secure' => request_is_secure(), 'httponly' => true, 'samesite' => 'Lax',
        ]);
        session_start();
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (request_is_secure()) {
        header('Strict-Transport-Security: max-age=31536000');
    }

    ensure_runtime_schema();

    $GLOBALS['locale'] = in_array($locale, SUPPORTED_LOCALES, true) ? $locale : 'es';
    $GLOBALS['site'] = settings();
    $GLOBALS['navCategories'] = nav_categories();
}

function jovani_local_asset_url(string $path): string
{
    $cleanPath = ltrim($path, '/');
    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);
    $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : date('YmdHis');
    return jovani_app_url('/' . $cleanPath) . '?v=' . rawurlencode($version);
}

function jovani_send_noindex_headers(): void
{
    header('X-Robots-Tag: noindex, follow');
}

function jovani_redirect(string $target): never
{
    header('Location: ' . $target, true, 302);
    exit;
}

function jovani_form_locale(string $path): string
{
    return str_contains($path, '/es/') ? 'es' : 'en';
}

function jovani_handle_newsletter(string $locale): never
{
    jovani_boot_blog($locale);

    if (!verify_csrf()) {
        flash('Formulario expirado.', 'danger');
        jovani_redirect('/#newsletter');
    }

    $email = trim((string) ($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Agrega un email valido.', 'danger');
        jovani_redirect('/#newsletter');
    }

    subscribe_or_reactivate_newsletter($email, $locale, (string) ($_POST['source'] ?? 'home'));
    flash('Ya estas en la lista.', 'success');
    jovani_redirect('/#newsletter');
}

function jovani_handle_contact(string $locale): never
{
    jovani_boot_blog($locale);

    if (!verify_csrf()) {
        flash('Formulario expirado.', 'danger');
        jovani_redirect('/#contact');
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $topic = trim((string) ($_POST['topic'] ?? ''));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
        flash('Agrega nombre, email y mensaje.', 'danger');
        jovani_redirect('/#contact');
    }

    db_run(
        "INSERT INTO contact_messages (name, email, topic, message, locale, status, created_at) VALUES (?, ?, ?, ?, ?, 'new', ?)",
        [$name, $email, $topic, $message, $locale, date('c')]
    );

    flash('Mensaje recibido. Lo revisamos pronto.', 'success');
    jovani_redirect('/#contact');
}

function jovani_is_safe_path(string $path, string $baseDir): bool
{
    $normalizedPath = str_replace('\\', '/', $path);
    $normalizedBase = rtrim(str_replace('\\', '/', $baseDir), '/');
    return $normalizedPath === $normalizedBase || str_starts_with($normalizedPath, $normalizedBase . '/');
}

function jovani_serve_file(string $file, bool $allowHtml = false): never
{
    $types = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'text/javascript; charset=UTF-8',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'avif' => 'image/avif', 'gif' => 'image/gif',
        'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
        'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'otf' => 'font/otf',
    ];
    if ($allowHtml) {
        $types['html'] = 'text/html; charset=UTF-8';
    }
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!is_file($file) || !isset($types[$extension])) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Recurso no encontrado.';
        exit;
    }
    header('Content-Type: ' . $types[$extension]);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: ' . ($extension === 'html' ? 'no-cache' : 'public, max-age=86400'));
    header('Content-Length: ' . (string) filesize($file));
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        readfile($file);
    }
    exit;
}
