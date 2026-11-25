<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$path = jovani_request_path();
$localRoot = realpath(__DIR__) ?: __DIR__;

if ($path !== '/') {
    $localFile = realpath(__DIR__ . $path);
    if ($localFile && jovani_is_safe_path($localFile, $localRoot) && is_file($localFile)) {
        jovani_send_noindex_headers();
        jovani_serve_file($localFile);
    }
}

if (preg_match('#^/blog/(es/)?newsletter$#', $path)) {
    jovani_handle_newsletter(jovani_form_locale($path));
}

if (preg_match('#^/blog/(es/)?contact$#', $path)) {
    jovani_handle_contact(jovani_form_locale($path));
}

if ($path === '/blog' || $path === '/blog/' || str_starts_with($path, '/blog/')) {
    $blogPublic = realpath(__DIR__ . '/../blog/public') ?: (__DIR__ . '/../blog/public');
    $relativePath = substr($path, 5) ?: '/';
    $mappedFile = realpath($blogPublic . $relativePath);

    if ($mappedFile && jovani_is_safe_path($mappedFile, $blogPublic) && is_file($mappedFile)) {
        jovani_serve_file($mappedFile);
    }

    $_SERVER['SCRIPT_NAME'] = '/blog/public/index.php';
    require $blogPublic . '/index.php';
    exit;
}

if ($path === '/cv' || $path === '/cv/' || $path === '/cv.html' || str_starts_with($path, '/cv/')) {
    $cvRoot = realpath(__DIR__ . '/../cv') ?: (__DIR__ . '/../cv');
    $cvEntry = realpath($cvRoot . '/cv.html');

    if ($path === '/cv' || $path === '/cv/' || $path === '/cv.html') {
        if ($cvEntry && is_file($cvEntry)) {
            jovani_send_noindex_headers();
            jovani_serve_file($cvEntry);
        }
    }

    $relativePath = substr($path, 3) ?: '/';
    $mappedFile = realpath($cvRoot . $relativePath);

    if ($mappedFile && jovani_is_safe_path($mappedFile, $cvRoot) && is_file($mappedFile)) {
        jovani_send_noindex_headers();
        jovani_serve_file($mappedFile);
    }
}

jovani_send_noindex_headers();
require __DIR__ . '/index.php';
