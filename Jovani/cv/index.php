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

// This entry belongs to the historic sibling-directory layout, including subfolders.
// Keep the shared static CV intact for the unified public root.
$portfolioBase = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/Jovani/cv/index.php'))), '/');
$siteBase = str_replace('\\', '/', dirname($portfolioBase));
$siteBase = in_array($siteBase, ['/', '.'], true) ? '' : rtrim($siteBase, '/');
$attribute = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$html = strtr((string) file_get_contents($source), [
    'href="/portfolio/"' => 'href="' . $attribute($portfolioBase . '/') . '"',
    'href="/"' => 'href="' . $attribute($siteBase . '/blog/') . '"',
    'href="/cv/' => 'href="' . $attribute($siteBase . '/cv/'),
    'src="/cv/' => 'src="' . $attribute($siteBase . '/cv/'),
]);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
    echo $html;
}
