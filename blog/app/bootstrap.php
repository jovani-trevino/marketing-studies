<?php

declare(strict_types=1);

require __DIR__ . '/i18n.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/models.php';
require __DIR__ . '/controllers.php';

date_default_timezone_set(app_config('timezone', 'America/Mexico_City'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = dirname(__DIR__) . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }
    if (is_writable($sessionPath)) {
        ini_set('session.save_path', $sessionPath);
    }
    session_start();
}

ensure_runtime_schema();

dispatch();
