<?php

return [
    'timezone' => 'America/Mexico_City',
    'db_driver' => getenv('MS_DB_DRIVER') ?: 'sqlite',
    'app_url' => getenv('MS_APP_URL') ?: '',
    'uploads_path' => getenv('MS_UPLOADS_PATH') ?: dirname(__DIR__) . '/public/uploads',
    'cv_owner_username' => getenv('MS_CV_OWNER_USERNAME') ?: '',
    'storage_path' => getenv('MS_STORAGE_PATH') ?: dirname(__DIR__) . '/storage',
    'sqlite_path' => getenv('MS_SQLITE_PATH') ?: (getenv('MS_STORAGE_PATH') ?: dirname(__DIR__) . '/storage') . '/blog.sqlite',
    'session_path' => getenv('MS_SESSION_PATH') ?: (getenv('MS_STORAGE_PATH') ?: dirname(__DIR__) . '/storage') . '/sessions',
    'rate_limit_path' => getenv('MS_RATE_LIMIT_PATH') ?: (getenv('MS_STORAGE_PATH') ?: dirname(__DIR__) . '/storage') . '/rate-limits',
    'session_lifetime' => 43200,
    'trust_proxy' => false,
    'mysql' => [
        'host' => getenv('MS_DB_HOST') ?: 'localhost',
        'port' => (int) (getenv('MS_DB_PORT') ?: 3306),
        'database' => getenv('MS_DB_NAME') ?: '',
        'username' => getenv('MS_DB_USER') ?: '',
        'password' => getenv('MS_DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'admin_username' => getenv('MS_ADMIN_USER') ?: '',
    // Legacy explicit passwords remain compatible; prefer a password_hash value.
    'admin_password' => getenv('MS_ADMIN_PASSWORD') ?: '',
    'admin_password_hash' => getenv('MS_ADMIN_PASSWORD_HASH') ?: '',
    'orcid' => [
        'environment' => getenv('MS_ORCID_ENV') ?: 'sandbox',
        'client_id' => getenv('MS_ORCID_CLIENT_ID') ?: '',
        'client_secret' => getenv('MS_ORCID_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('MS_ORCID_REDIRECT_URI') ?: '',
        'scope' => getenv('MS_ORCID_SCOPE') ?: '/authenticate',
    ],
];
