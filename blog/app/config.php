<?php

return [
    'timezone' => 'America/Mexico_City',
    'db_driver' => getenv('MS_DB_DRIVER') ?: 'sqlite',
    'sqlite_path' => dirname(__DIR__) . '/storage/blog.sqlite',
    'mysql' => [
        'host' => getenv('MS_DB_HOST') ?: 'localhost',
        'database' => getenv('MS_DB_NAME') ?: '',
        'username' => getenv('MS_DB_USER') ?: '',
        'password' => getenv('MS_DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ],
    'admin_username' => getenv('MS_ADMIN_USER') ?: 'admin',
    'admin_password' => getenv('MS_ADMIN_PASSWORD') ?: 'marketing123',
    'admin_password_hash' => getenv('MS_ADMIN_PASSWORD_HASH') ?: '',
    'orcid' => [
        'environment' => getenv('MS_ORCID_ENV') ?: 'sandbox',
        'client_id' => getenv('MS_ORCID_CLIENT_ID') ?: '',
        'client_secret' => getenv('MS_ORCID_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('MS_ORCID_REDIRECT_URI') ?: '',
        'scope' => getenv('MS_ORCID_SCOPE') ?: '/authenticate',
    ],
];
