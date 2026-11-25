<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$path = jovani_request_path();
$localRoot = realpath(__DIR__) ?: __DIR__;

// The portfolio is a public page. PHP source and private files are never static assets.
if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}

if ($path === '/site.css' || $path === '/site.js' || str_starts_with($path, '/assets/')) {
    $localFile = realpath(__DIR__ . $path);
    if ($localFile && jovani_is_safe_path($localFile, $localRoot) && is_file($localFile)) {
        jovani_serve_file($localFile);
    }
}

// Preserve historic /blog links while the blog keeps its own configured base path.
if ($path === '/blog' || str_starts_with($path, '/blog/')) {
    $target = jovani_app_url($path);
    $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    if (is_string($query) && $query !== '') {
        $target .= '?' . $query;
    }
    header('Location: ' . $target, true, 307);
    exit;
}

if ($path === '/cv' || $path === '/cv/' || $path === '/cv.html' || str_starts_with($path, '/cv/')) {
    $cvRoot = realpath(__DIR__ . '/../cv') ?: (__DIR__ . '/../cv');
    $isEntry = in_array($path, ['/cv', '/cv/', '/cv.html', '/cv/index.php', '/cv/cv.html'], true);
    $relativePath = $isEntry ? '/cv.html' : substr($path, 3);
    $cvFile = realpath($cvRoot . $relativePath);
    if ($cvFile && jovani_is_safe_path($cvFile, $cvRoot) && is_file($cvFile)) {
        if ($isEntry) {
            jovani_send_noindex_headers();
        }
        jovani_serve_file($cvFile, $isEntry);
    }
}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
echo 'Página no encontrada.';
