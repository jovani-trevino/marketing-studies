<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/i18n.php';
require dirname(__DIR__) . '/app/helpers.php';
require dirname(__DIR__) . '/app/models.php';

function read_secret(string $prompt): string
{
    if (!function_exists('shell_exec') || !function_exists('system') || PHP_OS_FAMILY === 'Windows') {
        throw new RuntimeException('Esta terminal no permite ocultar la contraseña. Utiliza JSON por stdin desde una fuente privada.');
    }
    $terminal = trim((string) shell_exec('stty -g 2>/dev/null'));
    if ($terminal === '') throw new RuntimeException('No se pudo proteger la entrada de la terminal.');
    fwrite(STDOUT, $prompt);
    system('stty -echo');
    try {
        $value = fgets(STDIN);
        if ($value === false) throw new RuntimeException('Entrada cancelada.');
        return rtrim($value, "\r\n");
    } finally {
        system('stty ' . escapeshellarg($terminal));
        fwrite(STDOUT, PHP_EOL);
    }
}

try {
    if (in_array('--help', $argv, true)) {
        fwrite(STDOUT, "Uso: php blog/tools/create_admin.php [--stdin]\nSin opciones solicita contraseña oculta; --stdin lee JSON privado con username y password.\n");
        exit;
    }
    $stdinMode = count($argv) === 2 && $argv[1] === '--stdin';
    if (count($argv) > 1 && !$stdinMode) throw new RuntimeException('No pases credenciales como argumentos. Sólo se admite la opción --stdin.');
    if (!$stdinMode && function_exists('stream_isatty') && stream_isatty(STDIN)) {
        fwrite(STDOUT, 'Usuario del CMS: ');
        $username = trim((string) fgets(STDIN));
        $password = read_secret('Contraseña nueva (mínimo 12 caracteres, oculta): ');
        $confirmation = read_secret('Repite la contraseña: ');
        if (!hash_equals($password, $confirmation)) throw new RuntimeException('Las contraseñas no coinciden.');
    } else {
        $input = stream_get_contents(STDIN, 8193);
        if ($input === false || strlen($input) > 8192) throw new RuntimeException('Entrada demasiado grande.');
        $credentials = json_decode($input, true, 8, JSON_THROW_ON_ERROR);
        if (!is_array($credentials) || !is_string($credentials['username'] ?? null) || !is_string($credentials['password'] ?? null)) throw new RuntimeException('Se espera JSON por stdin con username y password.');
        $username = $credentials['username']; $password = $credentials['password'];
    }
    ensure_runtime_schema();
    create_initial_admin($username, $password);
    fwrite(STDOUT, 'Administrador inicial creado. Accede a /admin/login.' . PHP_EOL);
} catch (Throwable $error) {
    $message = $error instanceof PDOException ? 'No se pudo preparar la base de datos. Revisa la configuración privada.' : $error->getMessage();
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}
