<?php

return [
    // Copy to config.local.php only on the server. Environment values take priority.
    'db_driver' => 'sqlite', // sqlite or mysql
    'app_url' => '', // Public HTTPS origin, including /blog if deployed there.
    'admin_username' => '',
    'admin_password_hash' => '', // Optional external hash; leave empty when using tools/create_admin.php.
    'mysql' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => '',
        'username' => '',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'newsletter_smtp' => [
        'host' => '',
        'port' => 465,
        'encryption' => 'ssl', // ssl, tls, none
        'username' => '',
        'password' => '',
        'timeout' => 20,
    ],
    'orcid' => [
        // Usa sandbox mientras pruebas en https://sandbox.orcid.org/developer-tools
        // Cambia a "production" cuando registres la app final en https://orcid.org/developer-tools
        'environment' => 'sandbox',
        'client_id' => '',
        'client_secret' => '',
        // Debe coincidir exactamente con la Redirect URI registrada en ORCID.
        // En produccion ORCID acepta solo HTTPS.
        'redirect_uri' => '',
        'scope' => '/authenticate',
    ],
];
