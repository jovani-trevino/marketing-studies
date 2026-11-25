<?php

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
        $local = getenv('MS_CONFIG_FILE') ?: __DIR__ . '/config.local.php';
        if (is_file($local)) {
            $config = array_replace_recursive($config, require $local);
        }
        // Explicit environment values must win, including in isolated previews.
        $environment = ['MS_DB_DRIVER'=>'db_driver', 'MS_SQLITE_PATH'=>'sqlite_path', 'MS_SESSION_PATH'=>'session_path', 'MS_STORAGE_PATH'=>'storage_path', 'MS_RATE_LIMIT_PATH'=>'rate_limit_path', 'MS_APP_URL'=>'app_url', 'MS_ADMIN_USER'=>'admin_username', 'MS_ADMIN_PASSWORD'=>'admin_password', 'MS_ADMIN_PASSWORD_HASH'=>'admin_password_hash'];
        foreach ($environment as $variable => $configKey) {
            if (getenv($variable) !== false) $config[$configKey] = (string) getenv($variable);
        }
        foreach (['HOST'=>'host', 'PORT'=>'port', 'NAME'=>'database', 'USER'=>'username', 'PASSWORD'=>'password'] as $suffix => $configKey) {
            if (getenv('MS_DB_' . $suffix) !== false) $config['mysql'][$configKey] = (string) getenv('MS_DB_' . $suffix);
        }
        if (getenv('MS_TRUST_PROXY') !== false) $config['trust_proxy'] = getenv('MS_TRUST_PROXY') === 'true';
    }
    return $key === null ? $config : ($config[$key] ?? $default);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function request_is_secure(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (bool) app_config('trust_proxy', false) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || str_starts_with((string) app_config('app_url', ''), 'https://');
}

function public_origin(): string
{
    $configured = trim((string) app_config('app_url', ''));
    if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_URL)) {
        $parts = parse_url($configured);
        if (in_array($parts['scheme'] ?? '', ['http', 'https'], true) && !isset($parts['user']) && !isset($parts['pass'])) {
            return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        }
    }
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    if (!preg_match('/^(?:[a-z0-9.-]+|\[[a-f0-9:]+\])(?::\d{1,5})?$/i', $host)) $host = 'localhost';
    return (request_is_secure() ? 'https' : 'http') . '://' . $host;
}

function safe_content_url(string $url, bool $allowContact = false): string
{
    $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url) || str_starts_with($url, '//')) return '';
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if ($scheme !== '') {
        if (in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) && !parse_url($url, PHP_URL_USER) && !parse_url($url, PHP_URL_PASS)) return $url;
        if ($allowContact && $scheme === 'mailto' && filter_var(substr($url, 7), FILTER_VALIDATE_EMAIL)) return $url;
        if ($allowContact && $scheme === 'tel' && preg_match('/^tel:\+?[0-9().-]{3,30}$/i', $url)) return $url;
        return '';
    }
    return $url;
}

function rate_limit_allowed(string $scope, int $maximum, int $seconds): bool
{
    $directory = (string) app_config('rate_limit_path');
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) return false;
    $peer = (string) ($_SERVER['REMOTE_ADDR'] ?? 'local');
    if (app_config('trust_proxy', false) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
        $candidate = trim((string) end($forwarded));
        if (filter_var($candidate, FILTER_VALIDATE_IP)) $peer = $candidate;
    }
    $filename = $directory . '/' . hash('sha256', $scope . ':' . $peer) . '.json';
    $handle = fopen($filename, 'c+');
    if (!$handle) return false;
    chmod($filename, 0600);
    try {
        if (!flock($handle, LOCK_EX)) return false;
        $state = json_decode(stream_get_contents($handle) ?: '{}', true) ?: [];
        $now = time();
        if (($state['until'] ?? 0) <= $now) $state = ['until'=>$now + $seconds, 'count'=>0];
        $state['count']++;
        ftruncate($handle, 0); rewind($handle); fwrite($handle, json_encode($state)); fflush($handle);
        return $state['count'] <= $maximum;
    } finally { flock($handle, LOCK_UN); fclose($handle); }
}

function base_path(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($dir === '.' || $dir === '\\' || $dir === '') {
        return '';
    }
    if (str_ends_with($dir, '/public')) {
        $dir = substr($dir, 0, -7) ?: '/';
    }
    return $dir === '/' ? '' : rtrim($dir, '/');
}

function url_path(string $path = '/', ?string $locale = null): string
{
    $path = '/' . ltrim($path, '/');
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    $prefix = $locale === 'es' ? '/es' : '';
    return base_path() . $prefix . $path;
}

function asset_url(string $path): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function versioned_asset_url(string $path): string
{
    $normalizedPath = ltrim($path, '/');
    $absolutePath = dirname(__DIR__) . '/public/' . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
    $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : date('YmdHis');
    return asset_url($normalizedPath) . '?v=' . rawurlencode($version);
}

function media_url(?string $url): string
{
    $url = safe_content_url((string) $url);
    if ($url === '') {
        return '';
    }
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }
    if (str_starts_with($url, '/')) {
        if (base_path() !== '' && str_starts_with($url, base_path() . '/')) return $url;
        return base_path() . $url;
    }
    return asset_url($url);
}

function uploaded_file_url(string $field, string $subdir = 'images', array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'], int $maxBytes = 5242880): string
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir el archivo.');
    }
    $temporary = is_string($file['tmp_name'] ?? null) ? $file['tmp_name'] : '';
    if ($temporary === '' || !is_uploaded_file($temporary)) throw new RuntimeException('Archivo subido no válido.');
    $size = filesize($temporary);
    if ($size === false || $size < 1 || $size > $maxBytes) {
        throw new RuntimeException('El archivo es demasiado grande.');
    }
    $original = (string) ($file['name'] ?? '');
    $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Tipo de archivo no permitido.');
    }
    validate_uploaded_content($temporary, $extension);
    $safeSubdir = trim(preg_replace('/[^a-z0-9_-]+/i', '-', $subdir), '-');
    $uploadDir = dirname(__DIR__) . '/public/uploads/' . ($safeSubdir ?: 'files');
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) throw new RuntimeException('No se pudo preparar la carpeta de archivos.');
    }
    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(5)) . '.' . $extension;
    $target = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($temporary, $target)) {
        throw new RuntimeException('No se pudo guardar el archivo.');
    }
    chmod($target, 0644);
    return '/uploads/' . ($safeSubdir ?: 'files') . '/' . $filename;
}

function validate_uploaded_content(string $path, string $extension): void
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path) ?: '';
    $images = ['jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'png'=>'image/png', 'webp'=>'image/webp', 'gif'=>'image/gif'];
    if (isset($images[$extension])) {
        $dimensions = @getimagesize($path);
        if (!$dimensions || $mime !== $images[$extension] || ($dimensions['mime'] ?? '') !== $images[$extension]
            || $dimensions[0] > 16000 || $dimensions[1] > 16000 || $dimensions[0] * $dimensions[1] > 60000000) {
            throw new RuntimeException('La imagen no coincide con su formato o supera las dimensiones permitidas.');
        }
        return;
    }
    $allowed = [
        'pdf'=>['application/pdf'], 'txt'=>['text/plain'], 'csv'=>['text/plain','text/csv','application/csv'],
        'zip'=>['application/zip','application/x-zip-compressed'],
        'doc'=>['application/msword','application/x-ole-storage','application/CDFV2'],
        'xls'=>['application/vnd.ms-excel','application/x-ole-storage','application/CDFV2'],
        'ppt'=>['application/vnd.ms-powerpoint','application/x-ole-storage','application/CDFV2'],
        'docx'=>['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/zip'],
        'xlsx'=>['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/zip'],
        'pptx'=>['application/vnd.openxmlformats-officedocument.presentationml.presentation','application/zip'],
    ];
    if (!in_array($mime, $allowed[$extension] ?? [], true)) throw new RuntimeException('El contenido del archivo no coincide con su extensión.');
    $signature = file_get_contents($path, false, null, 0, 8);
    if ($extension === 'pdf' && !str_starts_with((string) $signature, '%PDF-')) throw new RuntimeException('El PDF no es válido.');
    if (in_array($extension, ['zip','docx','xlsx','pptx'], true) && !str_starts_with((string) $signature, "PK")) throw new RuntimeException('El archivo comprimido no es válido.');
}

function admin_image_field(string $inputName, string $fileName, string $label, ?string $value = '', array $options = []): void
{
    $fieldId = trim((string) preg_replace('/[^a-z0-9_-]+/i', '-', $options['id'] ?? $inputName), '-');
    $fieldId = $fieldId !== '' ? $fieldId : 'media-field';
    $previewUrl = media_url((string) $value);
    $buttonLabel = trim((string) ($options['button_label'] ?? 'Elegir imagen'));
    $manualLabel = trim((string) ($options['manual_label'] ?? 'Ruta o URL manual'));
    $placeholder = trim((string) ($options['placeholder'] ?? '/uploads/imagenes/mi-imagen.jpg'));
    $hint = trim((string) ($options['hint'] ?? 'Tambien puedes pegar una ruta local o una URL externa si lo prefieres.'));
    $emptyTitle = trim((string) ($options['empty_title'] ?? 'Sin imagen seleccionada'));
    $emptyCopy = trim((string) ($options['empty_copy'] ?? 'Haz clic aqui o usa el boton para subirla desde tu PC.'));
    $dropHint = trim((string) ($options['drop_hint'] ?? 'Tambien puedes arrastrar una imagen a esta area.'));
    $statusReady = trim((string) ($options['status_ready'] ?? 'Imagen lista.'));
    $statusEmpty = trim((string) ($options['status_empty'] ?? 'Sin imagen todavia.'));
    $previewAlt = trim((string) ($options['preview_alt'] ?? $label));
    $inputType = trim((string) ($options['input_type'] ?? 'text'));
    $hideManualInput = !empty($options['hide_manual_input']);
    ?>
    <div class="admin-media-field" data-admin-media-field data-base-path="<?= e(base_path()) ?>">
        <div class="admin-media-card">
            <div class="admin-media-dropzone<?= $previewUrl !== '' ? ' has-image' : '' ?>" data-admin-media-dropzone tabindex="0" role="button" aria-label="<?= e($buttonLabel) ?>">
                <img src="<?= e($previewUrl) ?>" alt="<?= e($previewAlt) ?>" data-admin-media-preview<?= $previewUrl === '' ? ' hidden' : '' ?>>
                <div class="admin-media-empty" data-admin-media-empty<?= $previewUrl !== '' ? ' hidden' : '' ?>>
                    <i class="bi bi-image"></i>
                    <strong><?= e($emptyTitle) ?></strong>
                    <span><?= e($emptyCopy) ?></span>
                </div>
            </div>
            <div class="admin-media-toolbar">
                <input type="file" name="<?= e($fileName) ?>" id="<?= e($fieldId) ?>-file" class="d-none" accept="image/*" data-admin-media-file>
                <button type="button" class="btn btn-dark rounded-pill px-3" data-admin-media-pick>
                    <i class="bi bi-upload me-1"></i><?= e($buttonLabel) ?>
                </button>
                <button type="button" class="btn btn-outline-dark rounded-pill px-3" data-admin-media-clear>
                    <i class="bi bi-x-lg me-1"></i>Quitar
                </button>
            </div>
            <div class="admin-media-meta">
                <span class="admin-media-status" data-admin-media-status><?= e($previewUrl !== '' ? $statusReady : $statusEmpty) ?></span>
                <span class="admin-media-drop-hint"><?= e($dropHint) ?></span>
            </div>
        </div>
        <?php if ($hideManualInput): ?>
            <input type="hidden" name="<?= e($inputName) ?>" id="<?= e($fieldId) ?>" value="<?= e((string) $value) ?>" data-admin-media-url>
        <?php else: ?>
            <div class="admin-media-manual">
                <label class="form-label" for="<?= e($fieldId) ?>"><?= e($manualLabel) ?></label>
                <input type="<?= e($inputType) ?>" name="<?= e($inputName) ?>" id="<?= e($fieldId) ?>" class="form-control" value="<?= e((string) $value) ?>" placeholder="<?= e($placeholder) ?>" data-admin-media-url>
                <small class="text-body-secondary"><?= e($hint) ?></small>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function current_url(): string
{
    return public_origin() . ($_SERVER['REQUEST_URI'] ?? '/');
}

function comparable_url_target(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH);
    if ($path === false || $path === null || $path === '') {
        $path = '/';
    }
    $path = rawurldecode($path);
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }
    if ($path === '') {
        $path = '/';
    }

    $query = parse_url($url, PHP_URL_QUERY);
    if ($query === false || $query === null || $query === '') {
        return $path;
    }

    return $path . '?' . $query;
}

function current_request_target(): string
{
    return comparable_url_target($_SERVER['REQUEST_URI'] ?? '/');
}

function absolute_url(string $path): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return public_origin() . '/' . ltrim($path, '/');
}

function orcid_config(): array
{
    $configured = app_config('orcid', []);
    if (!is_array($configured)) {
        $configured = [];
    }

    $environment = strtolower(trim((string) (getenv('MS_ORCID_ENV') ?: ($configured['environment'] ?? 'sandbox'))));
    if (!in_array($environment, ['sandbox', 'production'], true)) {
        $environment = 'sandbox';
    }

    $host = $environment === 'production' ? 'https://orcid.org' : 'https://sandbox.orcid.org';
    $publicApiHost = $environment === 'production' ? 'https://pub.orcid.org' : 'https://pub.sandbox.orcid.org';
    $redirectUri = trim((string) (getenv('MS_ORCID_REDIRECT_URI') ?: ($configured['redirect_uri'] ?? '')));
    if ($redirectUri === '') {
        $redirectUri = absolute_url(url_path('/admin/orcid/callback'));
    }

    $scope = trim((string) (getenv('MS_ORCID_SCOPE') ?: ($configured['scope'] ?? '/authenticate')));
    if ($scope === '') {
        $scope = '/authenticate';
    }

    return [
        'environment' => $environment,
        'host' => $host,
        'public_api_host' => $publicApiHost,
        'authorize_url' => $host . '/oauth/authorize',
        'token_url' => $host . '/oauth/token',
        'client_id' => trim((string) (getenv('MS_ORCID_CLIENT_ID') ?: ($configured['client_id'] ?? ''))),
        'client_secret' => trim((string) (getenv('MS_ORCID_CLIENT_SECRET') ?: ($configured['client_secret'] ?? ''))),
        'redirect_uri' => $redirectUri,
        'scope' => $scope,
    ];
}

function orcid_is_configured(): bool
{
    $config = orcid_config();
    return $config['client_id'] !== '' && $config['client_secret'] !== '';
}

function orcid_normalize_id(string $value): string
{
    $value = trim($value);
    $value = preg_replace('#^https?://(?:sandbox\.)?orcid\.org/#i', '', $value) ?? $value;
    $value = strtoupper(str_replace(' ', '', trim($value, "/ \t\n\r\0\x0B")));

    return preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $value) ? $value : '';
}

function orcid_profile_url(string $orcidId): string
{
    $orcidId = orcid_normalize_id($orcidId);
    if ($orcidId === '') {
        return '';
    }
    return orcid_config()['host'] . '/' . $orcidId;
}

function orcid_authorization_url(string $state): string
{
    $config = orcid_config();
    return $config['authorize_url'] . '?' . http_build_query([
        'client_id' => $config['client_id'],
        'response_type' => 'code',
        'scope' => $config['scope'],
        'redirect_uri' => $config['redirect_uri'],
        'state' => $state,
    ], '', '&', PHP_QUERY_RFC3986);
}

function orcid_post_form(string $url, array $data): array
{
    $body = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    $headers = [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
    ];

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
        ]);
        $responseBody = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($responseBody === false) {
            throw new RuntimeException('ORCID no respondio: ' . ($error ?: 'error de conexion'));
        }

        return [$statusCode, (string) $responseBody];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ]);
    $responseBody = file_get_contents($url, false, $context);
    if ($responseBody === false) {
        throw new RuntimeException('ORCID no respondio.');
    }

    $statusCode = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches)) {
            $statusCode = (int) $matches[1];
            break;
        }
    }

    return [$statusCode, (string) $responseBody];
}

function orcid_exchange_authorization_code(string $code, ?string $redirectUri = null): array
{
    $config = orcid_config();
    if (!orcid_is_configured()) {
        throw new RuntimeException('Configura ORCID Client ID y Client Secret antes de conectar autores.');
    }

    [$statusCode, $responseBody] = orcid_post_form($config['token_url'], [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirectUri ?: $config['redirect_uri'],
    ]);
    $payload = json_decode($responseBody, true);
    if (!is_array($payload)) {
        throw new RuntimeException('ORCID devolvio una respuesta invalida.');
    }
    if ($statusCode < 200 || $statusCode >= 300) {
        $message = trim((string) ($payload['error_description'] ?? $payload['error'] ?? 'No se pudo completar la conexion con ORCID.'));
        throw new RuntimeException($message);
    }

    return $payload;
}

function locale_og_code(?string $locale = null): string
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    return $locale === 'es' ? 'es_MX' : 'en_US';
}

function media_dimensions(?string $url): array
{
    $url = trim((string) $url);
    if ($url === '') {
        return [];
    }

    $path = parse_url($url, PHP_URL_PATH) ?: $url;
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base . '/')) {
        $path = substr($path, strlen($base)) ?: '';
    }
    if ($path === '' || !str_starts_with($path, '/')) {
        return [];
    }

    $absolutePath = dirname(__DIR__) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (!is_file($absolutePath)) {
        return [];
    }

    $size = @getimagesize($absolutePath);
    if (!is_array($size)) {
        return [];
    }

    return [
        'width' => (int) ($size[0] ?? 0),
        'height' => (int) ($size[1] ?? 0),
        'mime' => (string) ($size['mime'] ?? ''),
    ];
}

function meta_text(string $value, int $limit = 180): string
{
    $value = trim((string) preg_replace('/\s+/', ' ', $value));
    if ($value === '') {
        return '';
    }
    if (mb_strlen($value) <= $limit) {
        return $value;
    }
    $slice = mb_substr($value, 0, max(1, $limit - 3));
    $lastSpace = mb_strrpos($slice, ' ');
    if ($lastSpace !== false && $lastSpace > (int) floor($limit * 0.6)) {
        $slice = mb_substr($slice, 0, $lastSpace);
    }
    return rtrim($slice, " \t\n\r\0\x0B,.;:-") . '...';
}

function comparable_text(string $value): string
{
    $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags($value)));
    if ($plain === '') {
        return '';
    }

    $normalized = mb_strtolower($plain, 'UTF-8');
    $normalized = preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized);

    return trim((string) $normalized);
}

function post_display_excerpt(array $post, int $limit = 180): string
{
    $title = trim((string) ($post['title'] ?? ''));
    $excerpt = meta_text((string) ($post['excerpt'] ?? ''), $limit);

    if ($excerpt !== '' && comparable_text($excerpt) !== comparable_text($title)) {
        return $excerpt;
    }

    $articleContent = render_article_content((string) ($post['content_html'] ?? ''), (string) ($post['content_markdown'] ?? ''));
    $spacedContent = preg_replace('/<\/(p|li|h[1-6]|blockquote|div|section|article|ul|ol)>/i', '$0 ', $articleContent);
    $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $spacedContent)));

    if ($plain === '' || comparable_text($plain) === comparable_text($title)) {
        return '';
    }

    return meta_text($plain, $limit);
}

function t(string $key, ?string $locale = null): string
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    return T[$locale][$key] ?? T[DEFAULT_LOCALE][$key] ?? $key;
}

function slugify(string $value): string
{
    $value = trim($value);
    $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $value = strtolower($converted ?: $value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim((string) $value, '-') ?: 'item';
}

function parse_tag_list(string $value): array
{
    $items = [];
    foreach (preg_split('/[\r\n,;|]+/', $value) ?: [] as $chunk) {
        $item = trim((string) preg_replace('/^\s*[-*]+\s*/', '', trim((string) $chunk)));
        if ($item === '') {
            continue;
        }
        $items[slugify($item)] = $item;
    }
    return array_values($items);
}

function parse_line_list(string $value): array
{
    $items = [];
    foreach (preg_split('/\R+/', $value) ?: [] as $chunk) {
        $item = trim((string) preg_replace('/^\s*[-*]+\s*/', '', trim((string) $chunk)));
        if ($item === '') {
            continue;
        }
        $items[md5($item)] = $item;
    }
    return array_values($items);
}

function category_color_presets(): array
{
    return [
        ['label' => 'Oro editorial', 'value' => '#c9853b'],
        ['label' => 'Azul estudio', 'value' => '#062b52'],
        ['label' => 'Azul profundo', 'value' => '#0c3d69'],
        ['label' => 'Cobre', 'value' => '#b86a3b'],
        ['label' => 'Terracota', 'value' => '#c45b4d'],
        ['label' => 'Coral', 'value' => '#dd7a58'],
        ['label' => 'Esmeralda', 'value' => '#2f8f6b'],
        ['label' => 'Teal', 'value' => '#1b7f85'],
        ['label' => 'Cian', 'value' => '#2b88c9'],
        ['label' => 'Violeta', 'value' => '#6e63c4'],
        ['label' => 'Magenta', 'value' => '#b65788'],
        ['label' => 'Grafito', 'value' => '#44556b'],
    ];
}

function category_icon_catalog(): array
{
    return [
        'bi bi-megaphone-fill' => ['label' => 'Marketing', 'hint' => 'Campanas, marca y adquisicion.'],
        'bi bi-bullseye' => ['label' => 'Performance', 'hint' => 'Paid media, conversion y ROAS.'],
        'bi bi-graph-up-arrow' => ['label' => 'Growth', 'hint' => 'Crecimiento, CRO y optimizacion.'],
        'bi bi-search' => ['label' => 'SEO', 'hint' => 'Busqueda, semantica y visibilidad.'],
        'bi bi-bar-chart-line' => ['label' => 'Analytics', 'hint' => 'Medicion, dashboards y data.'],
        'bi bi-pie-chart' => ['label' => 'Finance', 'hint' => 'Finanzas, negocio y rentabilidad.'],
        'bi bi-briefcase' => ['label' => 'Business', 'hint' => 'Estrategia comercial y management.'],
        'bi bi-layout-text-window-reverse' => ['label' => 'Case studies', 'hint' => 'Breakdowns, casos y learnings.'],
        'bi bi-flask' => ['label' => 'Lab', 'hint' => 'Recursos, plantillas y experimentos.'],
        'bi bi-envelope-paper' => ['label' => 'Email', 'hint' => 'CRM, nurture y automation.'],
        'bi bi-palette' => ['label' => 'Design', 'hint' => 'Branding, visual y craft.'],
        'bi bi-window-stack' => ['label' => 'UX / CRO', 'hint' => 'Landing pages, interfaz y conversion.'],
        'bi bi-code-slash' => ['label' => 'Dev / Web', 'hint' => 'Desarrollo, implementacion y front-end.'],
        'bi bi-command' => ['label' => 'Systems', 'hint' => 'Automatizacion y stack operativo.'],
        'bi bi-cpu' => ['label' => 'Technology', 'hint' => 'Tech, IA y herramientas digitales.'],
        'bi bi-lightbulb' => ['label' => 'Strategy', 'hint' => 'Ideas, posicionamiento y enfoque.'],
        'bi bi-globe2' => ['label' => 'Digital', 'hint' => 'Web, ecosistema y alcance online.'],
        'bi bi-people' => ['label' => 'Audience', 'hint' => 'Comunidades, equipos y usuarios.'],
        'bi bi-journal-richtext' => ['label' => 'Content', 'hint' => 'Editorial, blog y storytelling.'],
        'bi bi-diagram-3' => ['label' => 'Ops', 'hint' => 'Procesos, arquitectura y sistemas.'],
        'bi bi-camera-video' => ['label' => 'Media', 'hint' => 'Video, produccion y assets.'],
        'bi bi-mortarboard' => ['label' => 'Learning', 'hint' => 'Guias, estudio y educacion.'],
        'bi bi-rocket-takeoff' => ['label' => 'Launch', 'hint' => 'Lanzamientos y momentum.'],
        'bi bi-newspaper' => ['label' => 'Newsletter', 'hint' => 'Correo, publicaciones y briefs.'],
    ];
}

function normalize_hex_color(string $value, string $default = '#c9853b'): string
{
    $value = trim(strtolower($value));
    if ($value === '') {
        return strtolower($default);
    }
    if ($value[0] !== '#') {
        $value = '#' . $value;
    }
    if (!preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $value)) {
        return strtolower($default);
    }
    if (strlen($value) === 4) {
        return '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
    }
    return $value;
}

function normalize_bootstrap_icon_class(string $value, string $default = 'bi bi-megaphone-fill'): string
{
    $value = strtolower(trim((string) preg_replace('/\s+/', ' ', $value)));
    if ($value === '') {
        return $default;
    }
    if (!preg_match('/^bi bi-[a-z0-9-]+$/', $value)) {
        return $default;
    }
    return $value;
}

function canonical_category_slug(string $slug): string
{
    $slug = trim($slug, '/');
    if ($slug === '') {
        return '';
    }
    if (function_exists('category_slug_from_alias')) {
        return category_slug_from_alias($slug);
    }
    foreach (CATEGORY_COPY as $canonical => $copy) {
        foreach ($copy as $localeCopy) {
            if (($localeCopy['slug'] ?? '') === $slug) {
                return $canonical;
            }
        }
    }
    return $slug;
}

function localized_category_slug(string $canonical, ?string $locale = null): string
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    $resolved = $canonical;
    if (function_exists('category_slug_from_alias')) {
        $resolved = category_slug_from_alias($canonical);
    }
    if (function_exists('db_one')) {
        $row = db_one("SELECT * FROM categories WHERE slug = ? LIMIT 1", [$resolved]);
        if ($row) {
            $localized = localize_category($row, $locale);
            $localizedSlug = trim((string) ($localized['slug'] ?? ''));
            if ($localizedSlug !== '') {
                return $localizedSlug;
            }
        }
    }
    return CATEGORY_COPY[$resolved][$locale]['slug'] ?? CATEGORY_COPY[$canonical][$locale]['slug'] ?? $resolved;
}

function localize_category(array $category, ?string $locale = null): array
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    $canonicalSource = (string) ($category['canonical_slug'] ?? ($category['slug'] ?? ''));
    $canonical = function_exists('category_slug_from_alias') ? category_slug_from_alias($canonicalSource) : $canonicalSource;
    $copy = CATEGORY_COPY[$canonical][$locale] ?? null;
    $category['canonical_slug'] = $canonical;
    $category['base_name'] = (string) ($category['name'] ?? '');
    $category['base_slug'] = (string) ($category['slug'] ?? '');
    $category['base_description'] = (string) ($category['description'] ?? '');
    $category['base_spotlight'] = (string) ($category['spotlight'] ?? '');

    $localizedName = trim((string) ($category['name_' . $locale] ?? ''));
    $localizedSlug = trim((string) ($category['slug_' . $locale] ?? ''));
    $localizedDescription = trim((string) ($category['description_' . $locale] ?? ''));
    $localizedSpotlight = trim((string) ($category['spotlight_' . $locale] ?? ''));

    $category['name'] = $localizedName !== ''
        ? $localizedName
        : ((string) ($copy['name'] ?? '') !== '' ? (string) $copy['name'] : (string) ($category['base_name'] ?? ''));
    $category['slug'] = $localizedSlug !== ''
        ? $localizedSlug
        : ($copy['slug'] ?? ((string) ($category['base_slug'] ?? $canonical)));
    $category['description'] = $localizedDescription !== ''
        ? $localizedDescription
        : ((string) ($copy['description'] ?? '') !== '' ? (string) $copy['description'] : (string) ($category['base_description'] ?? ''));
    $category['spotlight'] = $localizedSpotlight !== ''
        ? $localizedSpotlight
        : ((string) ($copy['spotlight'] ?? '') !== '' ? (string) $copy['spotlight'] : (string) ($category['base_spotlight'] ?? ''));
    $category['cover_image'] = CATEGORY_COVER_IMAGES[$canonical] ?? CATEGORY_COVER_IMAGES[(string) ($category['slug'] ?? '')] ?? '';
    return $category;
}

function localize_author(array $author, ?string $locale = null): array
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    foreach (['role', 'headline', 'bio', 'expertise_areas', 'profile_labels', 'highlights'] as $field) {
        $author['base_' . $field] = (string) ($author[$field] ?? '');
        $localizedValue = trim((string) ($author[$field . '_' . $locale] ?? ''));
        if ($localizedValue !== '') {
            $author[$field] = $localizedValue;
        }
    }
    return $author;
}

function author_professional_link_catalog(): array
{
    return [
        'linkedin' => [
            'label' => 'LinkedIn',
            'icon' => 'bi bi-linkedin',
            'placeholder' => 'https://www.linkedin.com/in/tuusuario',
            'description' => 'Trayectoria, experiencia y credenciales.',
        ],
        'github' => [
            'label' => 'GitHub',
            'icon' => 'bi bi-github',
            'placeholder' => 'https://github.com/tuusuario',
            'description' => 'Repositorios, automatizacion y trabajo tecnico.',
        ],
        'website' => [
            'label' => 'Website / Portfolio',
            'icon' => 'bi bi-globe2',
            'placeholder' => 'https://tusitio.com/tu-nombre',
            'description' => 'Presencia propia, portfolio y casos centrales.',
        ],
        'behance' => [
            'label' => 'Behance',
            'icon' => 'bi bi-behance',
            'placeholder' => 'https://www.behance.net/tuusuario',
            'description' => 'Branding, direccion visual y casos creativos.',
        ],
        'dribbble' => [
            'label' => 'Dribbble',
            'icon' => 'bi bi-dribbble',
            'placeholder' => 'https://dribbble.com/tuusuario',
            'description' => 'UI, motion, interfaces y visual craft.',
        ],
        'medium' => [
            'label' => 'Medium',
            'icon' => 'bi bi-medium',
            'placeholder' => 'https://medium.com/@tuusuario',
            'description' => 'Articulos, reflexiones y autoridad escrita.',
        ],
        'substack' => [
            'label' => 'Substack',
            'icon' => 'bi bi-newspaper',
            'placeholder' => 'https://tu-publicacion.substack.com',
            'description' => 'Newsletter, ensayos y publicacion directa.',
        ],
    ];
}

function author_professional_links(array $author): array
{
    $catalog = author_professional_link_catalog();
    $raw = trim((string) ($author['professional_links'] ?? ''));
    $stored = json_decode($raw, true);
    if (!is_array($stored)) {
        $stored = [];
    }

    $legacy = [
        'linkedin' => trim((string) ($author['linkedin_url'] ?? '')),
        'github' => trim((string) ($author['github_url'] ?? '')),
        'website' => trim((string) ($author['website_url'] ?? '')),
    ];

    $items = [];
    foreach ($catalog as $key => $definition) {
        $storedItem = is_array($stored[$key] ?? null) ? $stored[$key] : [];
        $url = safe_content_url(trim((string) ($storedItem['url'] ?? ($legacy[$key] ?? ''))));
        $enabledRaw = $storedItem['enabled'] ?? null;
        $enabled = $enabledRaw === null
            ? $url !== ''
            : ($enabledRaw === true || $enabledRaw === 1 || in_array(strtolower((string) $enabledRaw), ['1', 'true', 'on', 'yes'], true));
        $items[] = array_merge($definition, [
            'key' => $key,
            'url' => $url,
            'enabled' => $enabled,
        ]);
    }

    return $items;
}

function author_visible_professional_links(array $author): array
{
    return array_values(array_filter(author_professional_links($author), static function (array $item): bool {
        return !empty($item['enabled']) && trim((string) ($item['url'] ?? '')) !== '';
    }));
}

function localize_post(array $post, ?string $locale = null): array
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    if (!empty($post['category_slug'])) {
        $category = localize_category([
            'slug' => $post['category_slug'],
            'name' => $post['category_name'] ?? '',
            'description' => $post['category_description'] ?? '',
            'spotlight' => $post['category_spotlight'] ?? '',
            'accent_color' => $post['category_color'] ?? '#c9853b',
            'icon_class' => $post['category_icon'] ?? 'bi bi-megaphone-fill',
        ], $locale);
        $post['category_name'] = $category['name'];
        $post['category_slug'] = $category['slug'];
        $post['category_canonical_slug'] = $category['canonical_slug'] ?? $post['category_slug'];
        $post['category_description'] = $category['description'];
        $post['category_spotlight'] = $category['spotlight'];
    }
    if (!empty($post['author_role']) || !empty($post['author_bio']) || !empty($post['author_role_en']) || !empty($post['author_role_es']) || !empty($post['author_bio_en']) || !empty($post['author_bio_es'])) {
        $authorSummary = localize_author([
            'role' => $post['author_role'] ?? '',
            'role_en' => $post['author_role_en'] ?? '',
            'role_es' => $post['author_role_es'] ?? '',
            'bio' => $post['author_bio'] ?? '',
            'bio_en' => $post['author_bio_en'] ?? '',
            'bio_es' => $post['author_bio_es'] ?? '',
        ], $locale);
        $post['author_role'] = $authorSummary['role'];
        $post['author_bio'] = $authorSummary['bio'];
    }
    if (function_exists('apply_post_translation')) {
        $post = apply_post_translation($post, $locale);
    }
    return $post;
}

function date_label(?string $date): string
{
    if (!$date) {
        return '';
    }
    return date('d M Y', strtotime($date));
}

function text_word_count(string $content): int
{
    preg_match_all("/[\p{L}\p{N}]+(?:['’-][\p{L}\p{N}]+)*/u", strip_tags($content), $matches);
    return count($matches[0] ?? []);
}

function estimate_read_time(string $content): int
{
    $words = text_word_count($content);
    return max(1, (int) ceil($words / 220));
}

function markdown_to_html(string $markdown): string
{
    $markdown = trim($markdown);
    if ($markdown === '') {
        return '';
    }
    $lines = preg_split('/\R/', $markdown) ?: [];
    $html = '';
    $list = null;
    $inCodeBlock = false;
    $codeLines = [];

    $closeList = static function () use (&$html, &$list): void {
        if ($list) {
            $html .= "</{$list}>";
            $list = null;
        }
    };

    $flushCodeBlock = static function () use (&$html, &$inCodeBlock, &$codeLines): void {
        if (!$inCodeBlock) {
            return;
        }
        $html .= '<pre><code>' . e(implode("\n", $codeLines)) . '</code></pre>';
        $inCodeBlock = false;
        $codeLines = [];
    };

    foreach ($lines as $line) {
        $line = rtrim($line);
        $trimmed = trim($line);

        if (preg_match('/^```/', $trimmed)) {
            $closeList();
            if ($inCodeBlock) {
                $flushCodeBlock();
            } else {
                $inCodeBlock = true;
                $codeLines = [];
            }
            continue;
        }

        if ($inCodeBlock) {
            $codeLines[] = $line;
            continue;
        }

        if ($trimmed === '') {
            $closeList();
            continue;
        }

        if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed)) {
            $closeList();
            $html .= '<hr>';
            continue;
        }

        if (preg_match('/^###\s+(.+)/', $trimmed, $m)) {
            $closeList();
            $html .= '<h3>' . inline_markdown($m[1]) . '</h3>';
            continue;
        }

        if (preg_match('/^##\s+(.+)/', $trimmed, $m)) {
            $closeList();
            $html .= '<h2>' . inline_markdown($m[1]) . '</h2>';
            continue;
        }

        if (preg_match('/^#\s+(.+)/', $trimmed, $m)) {
            $closeList();
            $html .= '<h1>' . inline_markdown($m[1]) . '</h1>';
            continue;
        }

        if (preg_match('/^>\s?(.+)/', $trimmed, $m)) {
            $closeList();
            $html .= '<blockquote>' . inline_markdown($m[1]) . '</blockquote>';
            continue;
        }

        if (preg_match('/^\d+\.\s+(.+)/', $trimmed, $m)) {
            if ($list !== 'ol') {
                $closeList();
                $html .= '<ol>';
                $list = 'ol';
            }
            $html .= '<li>' . inline_markdown($m[1]) . '</li>';
            continue;
        }

        if (preg_match('/^[-*]\s+(.+)/', $trimmed, $m)) {
            if ($list !== 'ul') {
                $closeList();
                $html .= '<ul>';
                $list = 'ul';
            }
            $html .= '<li>' . inline_markdown($m[1]) . '</li>';
            continue;
        }

        $closeList();
        $html .= '<p>' . inline_markdown($trimmed) . '</p>';
    }

    $closeList();
    $flushCodeBlock();

    return $html;
}

function inline_markdown(string $text): string
{
    $tokens = [];
    $counter = 0;

    $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', static function (array $matches) use (&$tokens, &$counter): string {
        $token = "%%MD_MEDIA_{$counter}%%";
        $counter++;
        $url = markdown_media_url($matches[2]);
        $tokens[$token] = $url !== ''
            ? '<img src="' . e($url) . '" alt="' . e($matches[1]) . '" loading="lazy">'
            : e($matches[1]);
        return $token;
    }, $text) ?? $text;

    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', static function (array $matches) use (&$tokens, &$counter): string {
        $token = "%%MD_LINK_{$counter}%%";
        $counter++;
        $url = markdown_media_url($matches[2]);
        $tokens[$token] = $url !== ''
            ? '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">' . e($matches[1]) . '</a>'
            : e($matches[1]);
        return $token;
    }, $text) ?? $text;

    $html = e($text);
    $html = preg_replace('/`([^`\n]+)`/', '<code>$1</code>', $html);
    $html = preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '<em>$1</em>', $html);

    return strtr((string) $html, $tokens);
}

function render_article_content(string $html, ?string $markdown = null): string
{
    $html = trim($html);
    if ($html === '' && trim((string) $markdown) !== '') {
        $html = markdown_to_html((string) $markdown);
    }

    if ($html === '') {
        return '';
    }
    return sanitize_article_html($html);
}

function sanitize_article_html(string $html): string
{
    if ($html === '') return '';
    if (!class_exists(DOMDocument::class)) return nl2br(e(strip_tags($html)));
    $previous = libxml_use_internal_errors(true);
    $source = new DOMDocument('1.0', 'UTF-8');
    $loaded = $source->loadHTML('<?xml encoding="utf-8"?><div id="ms-sanitizer-root">' . $html . '</div>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors(); libxml_use_internal_errors($previous);
    if (!$loaded || !($root = $source->getElementById('ms-sanitizer-root'))) return '';
    $output = new DOMDocument('1.0', 'UTF-8');
    $container = $output->createElement('div'); $output->appendChild($container);
    $allowed = ['p','br','div','span','h1','h2','h3','h4','h5','h6','strong','b','em','i','u','s','del','sub','sup','blockquote','ul','ol','li','pre','code','a','img','hr','figure','figcaption','table','thead','tbody','tfoot','tr','th','td','caption','details','summary','iframe'];
    $drop = ['script','style','svg','math','object','embed','applet','template','noscript','form','input','textarea','button','select','link','meta','base'];
    $copy = function (DOMNode $node, DOMNode $parent) use (&$copy, $output, $allowed, $drop): void {
        if ($node instanceof DOMText) { $parent->appendChild($output->createTextNode($node->data)); return; }
        if (!($node instanceof DOMElement)) return;
        $tag = strtolower($node->tagName);
        if (in_array($tag, $drop, true)) return;
        if (!in_array($tag, $allowed, true)) { foreach ($node->childNodes as $child) $copy($child, $parent); return; }
        $element = $output->createElement($tag === 'h1' ? 'h2' : $tag);
        if ($tag === 'iframe') {
            $url = safe_content_url($node->getAttribute('src'));
            $parts = parse_url($url);
            $host = strtolower((string) ($parts['host'] ?? ''));
            $path = $parts['path'] ?? '';
            $video = in_array($host, ['www.youtube.com','youtube.com','www.youtube-nocookie.com','youtube-nocookie.com'], true) && preg_match('#^/embed/[a-zA-Z0-9_-]+$#', $path)
                || $host === 'player.vimeo.com' && preg_match('#^/video/\d+$#', $path);
            if (($parts['scheme'] ?? '') !== 'https' || !$video || isset($parts['port'])) return;
            $element->setAttribute('src', $url);
            $element->setAttribute('title', $node->getAttribute('title') ?: 'Video');
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation');
            $element->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            $element->setAttribute('allowfullscreen', '');
        }
        if ($tag === 'a') {
            $href = safe_content_url($node->getAttribute('href'), true);
            if ($href !== '') $element->setAttribute('href', $href);
            if ($node->getAttribute('target') === '_blank') $element->setAttribute('target', '_blank');
            $element->setAttribute('rel', 'noopener noreferrer');
        }
        if ($tag === 'img') {
            $src = safe_content_url($node->getAttribute('src'));
            if ($src === '' || str_starts_with($src, '#')) return;
            $element->setAttribute('src', $src); $element->setAttribute('alt', $node->getAttribute('alt')); $element->setAttribute('loading', 'lazy');
        }
        foreach (['title','lang','dir'] as $attribute) {
            if ($node->hasAttribute($attribute)) $element->setAttribute($attribute, mb_substr($node->getAttribute($attribute), 0, 500));
        }
        foreach (['width','height','colspan','rowspan','start','value'] as $attribute) {
            if (preg_match('/^\d{1,5}$/', $node->getAttribute($attribute))) $element->setAttribute($attribute, $node->getAttribute($attribute));
        }
        $classes = [];
        foreach (preg_split('/\s+/', $node->getAttribute('class')) ?: [] as $class) {
            if (preg_match('/^(?:ql-(?:align-(?:center|right|justify)|indent-[1-8]|direction-rtl|size-(?:small|large|huge)|font-(?:serif|monospace)|code-block(?:-container)?|syntax|formula|video|ui)|language-[a-z0-9_-]{1,30})$/i', $class)) $classes[] = $class;
        }
        if ($classes) $element->setAttribute('class', implode(' ', array_unique($classes)));
        if (in_array('ql-formula', $classes, true) && $node->hasAttribute('data-value')) $element->setAttribute('data-value', mb_substr($node->getAttribute('data-value'), 0, 2000));
        if ($tag === 'li' && in_array($node->getAttribute('data-list'), ['bullet','ordered','checked','unchecked'], true)) $element->setAttribute('data-list', $node->getAttribute('data-list'));
        if ($tag === 'code' || in_array('ql-code-block', $classes, true)) {
            $language = $node->getAttribute('data-language');
            if (preg_match('/^[a-z0-9_-]{1,30}$/i', $language)) $element->setAttribute('data-language', $language);
        }
        $styles = [];
        foreach (explode(';', $node->getAttribute('style')) as $declaration) {
            $pair = explode(':', $declaration, 2);
            if (count($pair) !== 2) continue;
            [$property, $value] = array_map('trim', $pair); $property = strtolower($property);
            $valid = in_array($property, ['color','background-color'], true) && preg_match('/^(?:#[a-f0-9]{3,8}|[a-z]{1,24}|rgba?\([\d.,%\s]+\))$/i', $value)
                || $property === 'text-align' && in_array($value, ['left','center','right','justify'], true)
                || $property === 'font-size' && preg_match('/^(?:[1-9]\d?(?:\.\d+)?(?:px|pt)|[0-9](?:\.\d+)?em)$/', $value)
                || $property === 'white-space' && in_array($value, ['pre','pre-wrap'], true);
            if ($valid) $styles[] = $property . ': ' . $value;
        }
        if ($styles) $element->setAttribute('style', implode('; ', $styles));
        $parent->appendChild($element);
        if ($tag !== 'iframe') foreach ($node->childNodes as $child) $copy($child, $element);
    };
    foreach ($root->childNodes as $node) $copy($node, $container);
    $result = '';
    foreach ($container->childNodes as $node) $result .= $output->saveHTML($node);
    return $result;
}

function article_content_sections(string $html): ?array
{
    if (!class_exists(DOMDocument::class)) {
        return null;
    }

    $flags = 0;
    if (defined('LIBXML_HTML_NOIMPLIED')) {
        $flags |= LIBXML_HTML_NOIMPLIED;
    }
    if (defined('LIBXML_HTML_NODEFDTD')) {
        $flags |= LIBXML_HTML_NODEFDTD;
    }

    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $loaded = $document->loadHTML('<?xml encoding="utf-8" ?><div id="article-content-root">' . $html . '</div>', $flags);

    if ($previous !== null) {
        libxml_use_internal_errors($previous);
    }
    libxml_clear_errors();

    if (!$loaded) {
        return null;
    }

    $root = $document->getElementById('article-content-root');
    if (!$root) {
        return null;
    }

    $leadNodes = [];
    $sections = [];
    $currentSection = null;

    foreach (iterator_to_array($root->childNodes) as $node) {
        if ($node instanceof DOMElement && strtolower($node->tagName) === 'h2') {
            if ($currentSection !== null) {
                $sections[] = $currentSection;
            }

            $currentSection = [
                'title' => trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? ''),
                'nodes' => [],
            ];
            continue;
        }

        if ($currentSection === null) {
            $leadNodes[] = $node;
            continue;
        }

        $currentSection['nodes'][] = $node;
    }

    if ($currentSection !== null) {
        $sections[] = $currentSection;
    }

    if (!$sections) {
        return null;
    }

    $normalizedSections = [];
    foreach ($sections as $index => $section) {
        $bodyHtml = article_nodes_html($section['nodes']);
        $normalizedSections[] = [
            'title' => $section['title'] ?: article_section_fallback_title($index + 1),
            'body_html' => $bodyHtml,
        ];
    }

    return [
        'lead_html' => article_nodes_html($leadNodes),
        'sections' => $normalizedSections,
    ];
}

function article_nodes_html(array $nodes): string
{
    $html = '';

    foreach ($nodes as $node) {
        $document = $node instanceof DOMNode ? $node->ownerDocument : null;
        if ($document) {
            $html .= $document->saveHTML($node);
        }
    }

    return trim($html);
}

function article_section_teaser(string $html, int $limit = 140): string
{
    $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    if ($plain === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($plain) <= $limit) {
            return $plain;
        }
        return rtrim(mb_substr($plain, 0, max(0, $limit - 3))) . '...';
    }

    if (strlen($plain) <= $limit) {
        return $plain;
    }

    return rtrim(substr($plain, 0, max(0, $limit - 3))) . '...';
}

function article_section_fallback_title(int $position): string
{
    $locale = $GLOBALS['locale'] ?? DEFAULT_LOCALE;
    return $locale === 'es' ? 'Bloque ' . $position : 'Section ' . $position;
}

function markdown_media_url(string $url): string
{
    $url = safe_content_url(trim($url, " \t\n\r\0\x0B<>"), true);
    if ($url === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $url) || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
        return $url;
    }
    $base = base_path();
    if ($base !== '' && str_starts_with($url, $base . '/')) {
        return $url;
    }
    return media_url($url);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $expected = $_SESSION['csrf_token'] ?? null;
    $provided = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!is_string($expected) || !is_string($provided) || strlen($expected) < 32 || !hash_equals($expected, $provided)) return false;
    if (!empty($_SERVER['HTTP_ORIGIN']) && rtrim((string) $_SERVER['HTTP_ORIGIN'], '/') !== public_origin()) return false;
    return true;
}

function flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function consume_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function redirect_to(string $url, int $status = 302): never
{
    header('Location: ' . $url, true, $status);
    exit;
}

function redirect_to_if_changed(string $url, int $status = 302): void
{
    if (comparable_url_target($url) === current_request_target()) {
        return;
    }
    redirect_to($url, $status);
}

function setting_is_enabled(mixed $value): bool
{
    return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
}

function author_avatar_url(string $author, ?string $avatarUrl = null): string
{
    if (trim((string) $avatarUrl) !== '') {
        return media_url($avatarUrl);
    }
    $slug = slugify($author);
    return match ($slug) {
        'jovani', 'jovani-trevino' => asset_url('static/brand/jovani-trevino.jpg'),
        default => asset_url('static/brand/marketing-studies-icon.png'),
    };
}

function rating_stars(float|int $rating, bool $muted = false): string
{
    $rounded = (int) round($rating);
    $class = $muted ? ' rating-stars-muted' : '';
    $html = '<span class="rating-stars' . $class . '" aria-label="' . e($rating) . ' / 5">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<i class="bi ' . ($i <= $rounded ? 'bi-star-fill' : 'bi-star') . '"></i>';
    }
    return $html . '</span>';
}

function share_links(string $title, string $url): array
{
    $text = trim($title . ' ' . $url);
    return [
        'native' => $text,
        'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($url),
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url),
        'whatsapp' => 'https://wa.me/?text=' . rawurlencode($text),
        'instagram' => 'https://www.instagram.com/',
        'x' => 'https://twitter.com/intent/tweet?text=' . rawurlencode($title) . '&url=' . rawurlencode($url),
    ];
}

function post_url(array $post, ?string $locale = null): string
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    if (!empty($post['id']) && function_exists('apply_post_translation')) {
        $post = apply_post_translation($post, $locale);
    }
    $canonical = (string) ($post['category_canonical_slug']
        ?? canonical_category_slug((string) ($post['category_slug'] ?? 'marketing')));
    $categorySlug = localized_category_slug($canonical, $locale);
    if ($categorySlug === '') {
        $categorySlug = trim((string) ($post['category_slug'] ?? ''));
    }
    return url_path('/' . $categorySlug . '/' . ($post['slug'] ?? ''), $locale);
}

function post_comment_url(array $post, ?string $locale = null): string
{
    return post_url($post, $locale) . '/comment';
}

function render_ad(?string $slotHtml, string $label, string $size): string
{
    if ($slotHtml) {
        return '<div class="ad-slot" hidden data-ms-consent-slot><div class="ad-live">' . consent_code_payload($slotHtml) . '</div></div>';
    }
    return '';
}

function consent_code_payload(string $html): string
{
    if (trim($html) === '') return '';
    // Encoding keeps script end tags, tracking pixels and iframes inert in HTML.
    $payload = json_encode(['html'=>$html], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
    return '<script type="application/json" data-ms-consent-code>' . $payload . '</script>';
}

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $site = $GLOBALS['site'] ?? [];
    $locale = $GLOBALS['locale'] ?? DEFAULT_LOCALE;
    $navCategories = $GLOBALS['navCategories'] ?? [];
    $allCategories = $GLOBALS['allCategories'] ?? [];
    $flashes = consume_flashes();
    ob_start();
    require __DIR__ . '/views/' . $template . '.php';
    $content = ob_get_clean();
    require __DIR__ . '/views/layout.php';
}

function verify_recaptcha(array $site): bool
{
    if (!setting_is_enabled($site['recaptcha_enabled'] ?? '0')) {
        return true;
    }
    $secret = trim((string) ($site['recaptcha_secret_key'] ?? ''));
    $response = trim((string) ($_POST['g-recaptcha-response'] ?? ''));
    if ($secret === '' || $response === '') {
        return false;
    }
    $payload = http_build_query([
        'secret' => $secret,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 8,
        ],
    ]);
    $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $json = $result ? json_decode($result, true) : null;
    return !empty($json['success']);
}
