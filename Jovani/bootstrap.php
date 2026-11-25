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

function jovani_app_url(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');
    if ($path !== '/') {
        $path = rtrim($path, '/');
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
    $_SERVER['SCRIPT_NAME'] = jovani_app_url('/blog/public/index.php');

    if (!$loaded) {
        require_once __DIR__ . '/../blog/app/i18n.php';
        require_once __DIR__ . '/../blog/app/helpers.php';
        require_once __DIR__ . '/../blog/app/models.php';
        $loaded = true;
    }

    date_default_timezone_set(app_config('timezone', 'America/Mexico_City'));

    if (session_status() !== PHP_SESSION_ACTIVE) {
        $sessionPath = dirname(__DIR__) . '/blog/storage/sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0775, true);
        }
        if (is_writable($sessionPath)) {
            ini_set('session.save_path', $sessionPath);
        }
        session_start();
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
    header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate');
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

function jovani_serve_file(string $file): never
{
    $mime = function_exists('mime_content_type') ? mime_content_type($file) : null;
    if ($mime) {
        header('Content-Type: ' . $mime);
    }
    header('Content-Length: ' . (string) filesize($file));
    readfile($file);
    exit;
}
