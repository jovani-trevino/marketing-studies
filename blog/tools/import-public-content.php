<?php
/**
 * Optional recovery of the public Marketing Studies articles.
 *
 * php blog/tools/import-public-content.php --dry-run
 * MS_SQLITE_PATH=/path/to/blog.sqlite php blog/tools/import-public-content.php
 *
 * The application database configuration is reused. This script never runs from
 * a web request, sends mail, creates credentials or replaces existing posts.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/i18n.php';
require dirname(__DIR__) . '/app/helpers.php';
require dirname(__DIR__) . '/app/models.php';

function public_import_output(array $result): void
{
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
}

function public_import_text(mixed $value, string $field, int $limit = 10000, bool $required = false): string
{
    if (!is_string($value)) throw new RuntimeException("El campo {$field} debe ser texto.");
    $value = trim($value);
    if (($required && $value === '') || mb_strlen($value) > $limit) throw new RuntimeException("Revisa el campo {$field} del archivo de recuperación.");
    return $value;
}

function public_import_asset(string $url): string
{
    if ($url === '') return '';
    $safe = safe_content_url($url);
    if ($safe === '') throw new RuntimeException('El archivo contiene una URL de contenido no válida.');
    if (str_starts_with($safe, '/')) {
        $path = (string) parse_url($safe, PHP_URL_PATH);
        $root = realpath(dirname(__DIR__) . '/public');
        $resolved = realpath($root . '/' . ltrim($path, '/'));
        if (!$resolved || !is_file($resolved) || !str_starts_with($resolved, $root . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Falta un archivo público referenciado: ' . $path);
        }
    }
    return $safe;
}

function public_import_date(mixed $value, string $fallback): string
{
    if (!is_string($value) || trim($value) === '') return $fallback;
    $time = strtotime($value);
    if ($time === false) throw new RuntimeException('Hay una fecha no válida en el contenido público.');
    return date('c', $time);
}

/** The sanitized HTML remains the authoritative, fully formatted article. */
function public_import_markdown(string $html): string
{
    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->loadHTML('<?xml encoding="utf-8"?><div id="import-root">' . $html . '</div>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $render = static function (DOMNode $node) use (&$render): string {
        if ($node instanceof DOMText) return $node->data;
        if (!$node instanceof DOMElement) return '';
        $tag = strtolower($node->tagName);
        if ($tag === 'pre') return "\n\n````\n" . trim($node->textContent, "\r\n") . "\n````\n\n";
        if ($tag === 'img') return '![' . str_replace(['[', ']'], '', $node->getAttribute('alt')) . '](' . str_replace(')', '%29', $node->getAttribute('src')) . ')';
        if ($tag === 'iframe') return "\n\n[Video](" . str_replace(')', '%29', $node->getAttribute('src')) . ")\n\n";
        if ($tag === 'br') return "\n";
        if ($tag === 'hr') return "\n\n---\n\n";
        $inside = '';
        foreach ($node->childNodes as $child) $inside .= $render($child);
        if (preg_match('/^h([1-6])$/', $tag, $match)) return "\n\n" . str_repeat('#', max(2, (int) $match[1])) . ' ' . trim($inside) . "\n\n";
        if (in_array($tag, ['strong', 'b'], true)) return '**' . $inside . '**';
        if (in_array($tag, ['em', 'i'], true)) return '*' . $inside . '*';
        if ($tag === 'code') return '`' . str_replace('`', '\\`', $inside) . '`';
        if ($tag === 'a' && $node->getAttribute('href') !== '') return '[' . trim($inside) . '](' . str_replace(')', '%29', $node->getAttribute('href')) . ')';
        if ($tag === 'li') return "\n- " . trim($inside) . "\n";
        if ($tag === 'blockquote') return "\n\n> " . str_replace("\n", "\n> ", trim($inside)) . "\n\n";
        if (in_array($tag, ['td', 'th'], true)) return trim($inside) . "\t";
        if ($tag === 'tr') return trim($inside) . "\n";
        if (in_array($tag, ['p', 'div', 'ul', 'ol', 'figure', 'figcaption', 'table', 'details', 'summary'], true)) return "\n\n" . trim($inside) . "\n\n";
        return $inside;
    };
    $root = $document->getElementById('import-root');
    return trim((string) preg_replace('/\n{3,}/', "\n\n", $root ? $render($root) : strip_tags($html)));
}

function public_import_columns(string $table): array
{
    if (app_config('db_driver', 'sqlite') === 'mysql') {
        return array_column(db_all('SELECT COLUMN_NAME AS column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]), 'column_name');
    }
    return db_columns($table);
}

function public_import_count(string $table): int
{
    if (!public_import_columns($table)) return 0;
    return (int) (db_one("SELECT COUNT(*) AS total FROM {$table}")['total'] ?? 0);
}

function public_import_schema_ready(): bool
{
    $required = [
        'posts' => ['id', 'title', 'slug', 'excerpt', 'content_markdown', 'content_html', 'cover_image', 'author_id', 'author_name', 'category_id', 'seo_title', 'seo_description', 'status', 'is_featured', 'read_time', 'created_at', 'updated_at', 'published_at'],
        'post_translations' => ['id', 'post_id', 'locale', 'title', 'slug', 'excerpt', 'content_markdown', 'content_html', 'seo_title', 'seo_description', 'created_at', 'updated_at'],
        'categories' => ['id', 'name', 'slug', 'name_en', 'name_es', 'slug_en', 'slug_es', 'description', 'description_en', 'description_es', 'accent_color', 'icon_class', 'spotlight', 'spotlight_en', 'spotlight_es', 'show_in_nav', 'nav_order'],
        'authors' => ['id', 'name', 'slug', 'role', 'role_es', 'headline', 'headline_es', 'bio', 'bio_es', 'avatar_url', 'expertise_areas', 'expertise_areas_es', 'highlights', 'highlights_es', 'linkedin_url', 'github_url', 'website_url', 'orcid_id', 'status', 'created_at', 'updated_at'],
        'lab_resources' => ['id', 'title', 'slug', 'description', 'resource_type', 'download_url', 'cover_image', 'author_name', 'source_name', 'source_url', 'status', 'is_featured', 'download_count', 'created_at', 'updated_at', 'published_at'],
        'settings' => ['key', 'value'],
    ];
    foreach ($required as $table => $columns) if (array_diff($columns, public_import_columns($table))) return false;
    return true;
}

function public_import_category(string $canonical, int $order): array
{
    $socialCopy = [
        'en' => ['name' => 'Social Media', 'slug' => 'social-media', 'description' => 'Research on social platforms, audiences and content distribution.', 'spotlight' => 'Understand how social platforms distribute information.'],
        'es' => ['name' => 'Redes sociales', 'slug' => 'redes-sociales', 'description' => 'Investigación sobre plataformas sociales, audiencias y distribución de contenido.', 'spotlight' => 'Comprende cómo circula la información en las plataformas sociales.'],
    ];
    $copy = $canonical === 'redes-sociales' ? $socialCopy : CATEGORY_COPY[$canonical];
    $aliases = array_values(array_unique([$canonical, $copy['en']['slug'], $copy['es']['slug']]));
    $marks = implode(',', array_fill(0, count($aliases), '?'));
    $existing = db_one("SELECT id FROM categories WHERE slug IN ({$marks}) OR slug_en IN ({$marks}) OR slug_es IN ({$marks}) OR name = ? LIMIT 1", [...$aliases, ...$aliases, ...$aliases, $copy['es']['name']]);
    if ($existing) return ['id' => (int) $existing['id'], 'created' => false];
    $icons = ['analitica' => 'bi bi-bar-chart-line', 'negocios' => 'bi bi-briefcase', 'finanzas' => 'bi bi-cash-coin', 'redes-sociales' => 'bi bi-people'];
    db_run('INSERT INTO categories (name, slug, name_en, name_es, slug_en, slug_es, description, description_en, description_es, accent_color, icon_class, spotlight, spotlight_en, spotlight_es, show_in_nav, nav_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
        $copy['es']['name'], $canonical, $copy['en']['name'], $copy['es']['name'], $copy['en']['slug'], $copy['es']['slug'], $copy['es']['description'], $copy['en']['description'], $copy['es']['description'], '#92652E', $icons[$canonical], $copy['es']['spotlight'], $copy['en']['spotlight'], $copy['es']['spotlight'], 1, $order,
    ]);
    return ['id' => (int) db()->lastInsertId(), 'created' => true];
}

function public_import_author(array $profile, array $previousIds, string $now): array
{
    $name = public_import_text($profile['name'] ?? '', 'profile.name', 191, true);
    $slug = slugify($name);
    $author = db_one('SELECT * FROM authors WHERE name = ? OR slug = ? OR slug = ? ORDER BY id LIMIT 1', [$name, $slug, 'jovani']);
    if (!$author) {
        db_run('INSERT INTO authors (name, slug, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?)', [$name, unique_author_slug($slug), 'active', $now, $now]);
        $author = db_one('SELECT * FROM authors WHERE id = ?', [(int) db()->lastInsertId()]);
    }
    $skills = implode(', ', array_filter($profile['skills'] ?? [], 'is_string'));
    $highlights = [];
    foreach (($profile['projects'] ?? []) as $project) {
        if (is_array($project) && !empty($project['name'])) $highlights[] = trim($project['name'] . (!empty($project['summary']) ? ': ' . $project['summary'] : ''));
    }
    $proposed = [
        'name' => $name,
        'role' => (string) ($profile['headline'] ?? ''), 'role_es' => (string) ($profile['headline'] ?? ''),
        'headline' => (string) ($profile['headline'] ?? ''), 'headline_es' => (string) ($profile['headline'] ?? ''),
        'bio' => (string) ($profile['bio'] ?? ''), 'bio_es' => (string) ($profile['bio'] ?? ''),
        'expertise_areas' => $skills, 'expertise_areas_es' => $skills,
        'highlights' => implode("\n", $highlights), 'highlights_es' => implode("\n", $highlights),
        'avatar_url' => public_import_asset((string) ($profile['portrait'] ?? '')),
        'linkedin_url' => safe_content_url((string) ($profile['socials']['linkedin'] ?? '')),
        'github_url' => safe_content_url((string) ($profile['socials']['github'] ?? '')),
        'website_url' => safe_content_url((string) ($profile['website'] ?? '')),
        'orcid_id' => orcid_normalize_id((string) ($profile['orcid'] ?? '')),
    ];
    $newAuthor = !in_array((int) $author['id'], $previousIds, true);
    $updates = []; $params = [];
    foreach ($proposed as $field => $value) {
        if ($value !== '' && ($newAuthor || trim((string) ($author[$field] ?? '')) === '') && ($author[$field] ?? '') !== $value) {
            $updates[] = "{$field} = ?"; $params[] = $value; $author[$field] = $value;
        }
    }
    if ($updates) { $updates[] = 'updated_at = ?'; $params[] = $now; $params[] = (int) $author['id']; db_run('UPDATE authors SET ' . implode(', ', $updates) . ' WHERE id = ?', $params); }
    return ['id' => (int) $author['id'], 'name' => (string) $author['name']];
}

$pdo = null;
try {
    $source = dirname(__DIR__) . '/database/public-content.json';
    $dryRun = false;
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--dry-run') $dryRun = true;
        elseif (str_starts_with($argument, '--source=')) $source = substr($argument, 9);
        elseif (in_array($argument, ['--help', '-h'], true)) {
            echo "Uso: php blog/tools/import-public-content.php [--dry-run] [--source=/ruta/public-content.json]\n";
            echo "Usa la configuración normal (MS_DB_DRIVER/MS_SQLITE_PATH o MySQL). Solo importa si posts está vacío.\n";
            echo "--dry-run valida la fuente y los archivos públicos sin abrir ni modificar la base de datos.\n";
            exit(0);
        } else throw new RuntimeException('Opción no reconocida. Usa --help.');
    }
    if (!is_file($source) || !is_readable($source)) throw new RuntimeException('No se encontró el archivo de contenido público.');
    $seed = json_decode((string) file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($seed) || !is_array($seed['posts'] ?? null) || !is_array($seed['profile'] ?? null) || !is_array($seed['resources'] ?? null)) throw new RuntimeException('El archivo no tiene el esquema de contenido público esperado.');
    date_default_timezone_set((string) app_config('timezone', 'America/Mexico_City'));
    $now = date('c');
    $groups = []; $slugs = []; $categoryNames = [];
    foreach ($seed['posts'] as $item) {
        if (!is_array($item)) throw new RuntimeException('Hay un artículo no válido en la fuente.');
        $group = public_import_text($item['translation_group'] ?? '', 'translation_group', 191, true);
        $locale = $item['locale'] ?? '';
        if (!in_array($locale, ['es', 'en'], true) || isset($groups[$group][$locale])) throw new RuntimeException('Cada artículo debe tener una sola versión por idioma ES/EN.');
        $title = public_import_text($item['title'] ?? '', 'title', 255, true);
        $slug = public_import_text($item['slug'] ?? '', 'slug', 191, true);
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || isset($slugs[$locale . ':' . $slug])) throw new RuntimeException('Los slugs deben ser válidos y únicos por idioma.');
        $slugs[$locale . ':' . $slug] = true;
        $category = $item['category'] ?? '';
        if (!in_array($category, ['analitica', 'negocios', 'redes-sociales', 'finanzas'], true)) throw new RuntimeException('Hay una categoría no contemplada por este importador de recuperación.');
        $categoryNames[$category] = true;
        $body = public_import_text($item['body'] ?? '', 'body', 2097152, true);
        if (!in_array($item['format'] ?? 'html', ['html', 'markdown'], true)) throw new RuntimeException('Formato de artículo no compatible.');
        $html = sanitize_article_html(($item['format'] ?? 'html') === 'markdown' ? markdown_to_html($body) : $body);
        if ($html === '') throw new RuntimeException('Un artículo quedó vacío después de validar el HTML.');
        $publishedAt = public_import_date($item['published_at'] ?? '', $now);
        $groups[$group][$locale] = [
            'title' => $title, 'slug' => $slug, 'excerpt' => public_import_text($item['excerpt'] ?? '', 'excerpt', 10000, true),
            'markdown' => ($item['format'] ?? 'html') === 'markdown' ? $body : public_import_markdown($html), 'html' => $html,
            'category' => $category, 'cover' => public_import_asset((string) ($item['cover'] ?? '')),
            'status' => ($item['status'] ?? '') === 'published' ? 'published' : 'draft', 'featured' => !empty($item['featured']) ? 1 : 0,
            'read_time' => max(1, min(240, (int) ($item['read_minutes'] ?? estimate_read_time($html)))),
            'published_at' => $publishedAt, 'updated_at' => public_import_date($item['updated_at'] ?? '', $publishedAt),
        ];
    }
    if (!$groups) throw new RuntimeException('La fuente no contiene artículos para recuperar.');
    foreach ($groups as $group) if (!isset($group['es'], $group['en'])) throw new RuntimeException('Falta la versión ES o EN de un artículo. No se importó contenido parcial.');
    $resources = [];
    foreach ($seed['resources'] as $resource) {
        if (!is_array($resource) || ($resource['locale'] ?? '') !== 'es') continue;
        $resource['title'] = public_import_text($resource['title'] ?? '', 'resource.title', 191, true);
        $resource['description'] = public_import_text($resource['description'] ?? '', 'resource.description', 10000, true);
        $resource['url'] = public_import_asset(public_import_text($resource['url'] ?? '', 'resource.url', 2000, true));
        $resources[] = $resource;
    }
    public_import_text($seed['profile']['name'] ?? '', 'profile.name', 191, true);
    public_import_asset((string) ($seed['profile']['portrait'] ?? ''));
    $plan = ['articles' => count($groups), 'translations' => count($seed['posts']), 'spanish_resources' => count($resources), 'categories' => array_keys($categoryNames)];
    if ($dryRun) { public_import_output(['ok' => true, 'dry_run' => true, 'database_opened' => false, 'plan' => $plan]); exit(0); }

    $pdo = db();
    // Exit before schema migrations or any DML on an installation with content.
    $existingPosts = public_import_count('posts');
    if ($existingPosts > 0) { public_import_output(['ok' => true, 'skipped' => true, 'reason' => 'La base ya contiene artículos. No se modificó ningún registro.', 'existing_articles' => $existingPosts]); exit(0); }
    $previousAuthors = public_import_columns('authors') ? array_map('intval', array_column(db_all('SELECT id FROM authors'), 'id')) : [];
    if (!public_import_schema_ready()) {
        foreach (['users', 'categories', 'authors', 'posts', 'post_translations', 'settings', 'comments', 'newsletter_subscribers', 'newsletter_campaigns', 'contact_messages', 'lab_resources'] as $table) {
            if (public_import_count($table) > 0) throw new RuntimeException('La base contiene registros y su esquema necesita actualizarse. Ejecuta primero la migración normal del CMS; el importador no modificó esos registros.');
        }
        ensure_runtime_schema();
    }
    $pdo->beginTransaction();
    if (public_import_count('posts') > 0) throw new RuntimeException('Se detectaron artículos antes de importar. La operación se canceló.');
    $categoryIds = []; $createdCategories = 0;
    foreach (array_keys($categoryNames) as $index => $canonical) { $entry = public_import_category($canonical, ($index + 1) * 10); $categoryIds[$canonical] = $entry['id']; $createdCategories += (int) $entry['created']; }
    $author = public_import_author($seed['profile'], $previousAuthors, $now);
    $articleCount = 0; $translationCount = 0;
    foreach ($groups as $versions) {
        $base = $versions['es'];
        db_run('INSERT INTO posts (title, slug, excerpt, content_markdown, content_html, cover_image, seo_title, seo_description, status, is_featured, author_name, author_id, category_id, read_time, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $base['title'], $base['slug'], $base['excerpt'], $base['markdown'], $base['html'], $base['cover'], $base['title'], mb_substr($base['excerpt'], 0, 300), $base['status'], $base['featured'], $author['name'], $author['id'], $categoryIds[$base['category']], $base['read_time'], $base['published_at'], $base['updated_at'], $base['status'] === 'published' ? $base['published_at'] : null,
        ]);
        $postId = (int) $pdo->lastInsertId();
        $articleCount++;
        foreach ($versions as $locale => $version) {
            db_run('INSERT INTO post_translations (post_id, locale, title, slug, excerpt, content_markdown, content_html, seo_title, seo_description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $postId, $locale, $version['title'], $version['slug'], $version['excerpt'], $version['markdown'], $version['html'], $version['title'], mb_substr($version['excerpt'], 0, 300), $version['published_at'], $version['updated_at'],
            ]);
            $translationCount++;
        }
    }
    $resourceCount = 0;
    foreach ($resources as $resource) {
        $slug = slugify($resource['title']);
        if (db_one('SELECT id FROM lab_resources WHERE slug = ? OR download_url = ? LIMIT 1', [$slug, $resource['url']])) continue;
        $status = ($resource['status'] ?? '') === 'published' ? 'published' : 'draft';
        db_run('INSERT INTO lab_resources (title, slug, description, resource_type, download_url, cover_image, author_name, source_name, source_url, status, is_featured, download_count, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $resource['title'], $slug, $resource['description'], (string) ($resource['type'] ?? 'Recurso'), $resource['url'], '', $author['name'], 'Marketing Studies', 'https://marketing-studies.com/', $status, 0, 0, $now, $now, $status === 'published' ? $now : null,
        ]);
        $resourceCount++;
    }
    $settings = is_array($seed['settings'] ?? null) ? $seed['settings'] : [];
    $minimumSettings = ['site_title' => $settings['siteName'] ?? 'Marketing Studies', 'site_tagline' => $settings['tagline'] ?? '', 'logo_url' => '/static/brand/marketing-studies-logo.png', 'contact_email' => $settings['contactEmail'] ?? ''];
    $settingCount = 0;
    foreach ($minimumSettings as $key => $value) {
        if (is_string($value) && trim($value) !== '' && !db_one('SELECT `key` FROM settings WHERE `key` = ?', [$key])) {
            db_run('INSERT INTO settings (`key`, value) VALUES (?, ?)', [$key, trim($value)]);
            $settingCount++;
        }
    }
    $pdo->commit();
    public_import_output(['ok' => true, 'imported_articles' => $articleCount, 'imported_translations' => $translationCount, 'imported_resources' => $resourceCount, 'created_categories' => $createdCategories, 'added_settings' => $settingCount, 'newsletter_sent' => false]);
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    $message = $error instanceof PDOException ? 'No se pudo completar la operación en la base de datos. El contenido de esta importación se revirtió.' : $error->getMessage();
    fwrite(STDERR, 'Importación detenida: ' . $message . PHP_EOL);
    exit(1);
}
