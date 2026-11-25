<?php

return [
    'db_driver' => 'mysql',
    'mysql' => [
        'host' => 'localhost',
        'database' => 'cpanel_usuario_basedatos',
        'username' => 'cpanel_usuario_dbuser',
        'password' => 'password_segura',
        'charset' => 'utf8mb4',
    ],
    'newsletter_smtp' => [
        'host' => 'mail.marketing-studies.com',
        'port' => 465,
        'encryption' => 'ssl', // ssl, tls, none
        'username' => 'newsletter@marketing-studies.com',
        'password' => 'password_del_buzon',
        'timeout' => 20,
    ],
    'orcid' => [
        // Usa sandbox mientras pruebas en https://sandbox.orcid.org/developer-tools
        // Cambia a "production" cuando registres la app final en https://orcid.org/developer-tools
        'environment' => 'sandbox',
        'client_id' => 'APP-XXXXXXXXXXXXXXXX',
        'client_secret' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        // Debe coincidir exactamente con la Redirect URI registrada en ORCID.
        // En produccion ORCID acepta solo HTTPS.
        'redirect_uri' => 'https://marketing-studies.com/blog/admin/orcid/callback',
        'scope' => '/authenticate',
    ],
];
