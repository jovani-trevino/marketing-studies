<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, follow');
header('X-Content-Type-Options: nosniff');

$source = realpath(__DIR__ . '/../../cv/cv.html');

if (!$source || !is_file($source)) {
    http_response_code(404);
    echo 'CV no disponible.';
    exit;
}

readfile($source);
