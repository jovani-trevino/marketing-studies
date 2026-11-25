<?php

declare(strict_types=1);

// Render the real profile against an isolated database; never load deployment
// settings, recovered sessions, production content or external services.
$directory = sys_get_temp_dir() . '/marketing-studies-profile-test-' . bin2hex(random_bytes(6));
mkdir($directory, 0700, true);
foreach (['MS_DB_DRIVER'=>'sqlite', 'MS_SQLITE_PATH'=>$directory . '/test.sqlite', 'MS_STORAGE_PATH'=>$directory, 'MS_SESSION_PATH'=>$directory . '/sessions', 'MS_RATE_LIMIT_PATH'=>$directory . '/rate-limits', 'MS_CONFIG_FILE'=>$directory . '/no-config.php', 'MS_ADMIN_USER'=>'', 'MS_ADMIN_PASSWORD'=>'', 'MS_ADMIN_PASSWORD_HASH'=>'', 'MS_APP_URL'=>'http://localhost', 'MS_TRUST_PROXY'=>'false'] as $name => $value) putenv($name . '=' . $value);

require dirname(__DIR__) . '/blog/app/i18n.php';
require dirname(__DIR__) . '/blog/app/helpers.php';
require dirname(__DIR__) . '/blog/app/models.php';
require dirname(__DIR__) . '/blog/app/controllers.php';

$_SERVER = array_merge($_SERVER, ['HTTP_HOST'=>'localhost', 'SCRIPT_NAME'=>'/index.php', 'REQUEST_URI'=>'/jovani', 'REQUEST_METHOD'=>'GET', 'REMOTE_ADDR'=>'127.0.0.1']);
$_SESSION = []; $_POST = []; $_GET = [];
$GLOBALS['locale'] = 'en';
$results = []; $failures = 0;

function profile_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function profile_scenario(string $name, callable $callback): void
{
    global $results, $failures;
    try { $callback(); $results[] = 'PASS ' . $name; }
    catch (Throwable $error) { $failures++; $results[] = 'FAIL ' . $name . ': ' . $error->getMessage(); }
}

function profile_render(string $uri = '/jovani', string $script = '/index.php'): DOMXPath
{
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = $script;
    ob_start();
    try { dispatch(); $html = (string) ob_get_contents(); }
    finally { ob_end_clean(); }
    $document = new DOMDocument();
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
    return new DOMXPath($document);
}

function profile_value(DOMXPath $page, string $query): string
{
    return trim((string) $page->evaluate('string(' . $query . ')'));
}

function profile_post(string $slug, string $name, ?int $authorId, string $publishedAt, string $status = 'published', ?int $categoryId = null): int
{
    db_run('INSERT INTO posts (title,slug,excerpt,content_markdown,content_html,status,author_name,author_id,category_id,read_time,created_at,updated_at,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', ['Title ' . $slug, $slug, 'Isolated profile fixture.', 'Fixture body', '<p>Fixture body</p>', $status, $name, $authorId, $categoryId, 3, $publishedAt, $publishedAt, $publishedAt]);
    return (int) db()->lastInsertId();
}

function profile_remove_fixture(string $path): void
{
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $child = $path . '/' . $entry;
        if (is_dir($child) && !is_link($child)) profile_remove_fixture($child); else unlink($child);
    }
    rmdir($path);
}

register_shutdown_function(function () use ($directory): void {
    global $results, $failures;
    close_db();
    if (is_dir($directory)) profile_remove_fixture($directory);
    echo implode(PHP_EOL, $results) . PHP_EOL;
    echo count($results) . ' profile scenarios; ' . $failures . ' failures.' . PHP_EOL;
    if ($failures) exit(1);
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

ensure_runtime_schema();
db_run('DELETE FROM posts');
db_run('DELETE FROM authors');
save_setting('author_name', 'Different Site Author');
save_setting('newsletter_enabled', '0');

profile_scenario('empty profile has localized metadata and never adopts the site author', function (): void {
    profile_post('unrelated-site-article', 'Different Site Author', null, '2026-01-30T12:00:00+00:00');
    foreach (['/jovani'=>'en', '/es/jovani'=>'es'] as $uri => $locale) {
        $page = profile_render($uri);
        profile_check(profile_value($page, '//html/@lang') === $locale, 'Incorrect document language.');
        profile_check(str_contains(profile_value($page, '//title'), 'Jovani Treviño'), 'Profile adopted the global site author.');
        profile_check(profile_value($page, '//meta[@property="og:type"]/@content') === 'profile', 'Missing profile metadata.');
        profile_check(str_contains(profile_value($page, '//meta[@name="description"]/@content'), $locale === 'es' ? 'Conoce su trabajo' : 'Explore his work'), 'Fallback metadata is not localized.');
        profile_check(!str_contains(profile_value($page, '//body'), 'Title unrelated-site-article'), 'Another author appeared on an empty profile.');
        profile_check((int) $page->evaluate('count(//h1)') === 1, 'Profile must have one main heading.');
        profile_check((int) $page->evaluate('count(//*[contains(concat(" ",normalize-space(@class)," ")," ms-profile-publication-count ")])') === 0, 'Empty profile displays a publication counter.');
        profile_check(str_starts_with(profile_value($page, '//meta[@property="og:image"]/@content'), 'http://localhost/static/brand/jovani-profile.webp?v='), 'Empty profile lost its fallback portrait.');
    }
});

profile_scenario('legacy posts match the exact Jovani name when no CMS author exists', function (): void {
    profile_post('unrelated-jovani-namesake', 'Jovani Other', null, '2026-01-31T12:00:00+00:00');
    profile_post('legacy-jovani-article', 'Jovani Treviño', null, '2026-01-10T12:00:00+00:00');
    $page = profile_render();
    $body = profile_value($page, '//body');
    profile_check(str_contains($body, 'Title legacy-jovani-article'), 'Legacy Jovani article was lost.');
    profile_check(!str_contains($body, 'Title unrelated-site-article') && !str_contains($body, 'Title unrelated-jovani-namesake'), 'Fallback matched an unrelated author.');
    $archiveUrl = profile_value($page, '//section[@id="publications"]//a[contains(@href,"/author/")]/@href');
    profile_check($archiveUrl !== '', 'Legacy author archive link is missing.');
    $archiveBody = profile_value(profile_render($archiveUrl), '//body');
    profile_check(str_contains($archiveBody, 'Title legacy-jovani-article') && !str_contains($archiveBody, 'Title unrelated-jovani-namesake') && !str_contains($archiveBody, 'Title unrelated-site-article'), 'Legacy archive link selected an unrelated author.');
});

db_run('DELETE FROM posts');
db_run('INSERT INTO authors (name,slug,role,headline_en,headline_es,bio_en,bio_es,avatar_url,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)', ['Jovani Treviño', 'jovani-trevino', 'Default role fixture', 'Evidence-led profile fixture', 'Perfil de investigación localizada', 'Unique English author biography fixture.', 'Biografía única del autor en español.', '/static/brand/jovani-trevino.jpg', 'active', date('c'), date('c')]);
$authorId = (int) db()->lastInsertId();
db_run('INSERT INTO authors (name,slug,status,created_at,updated_at) VALUES (?,?,?,?,?)', ['Other Author', 'other-author', 'active', date('c'), date('c')]);
$otherAuthorId = (int) db()->lastInsertId();
$categoryId = (int) (db_one('SELECT id FROM categories WHERE slug = ?', ['casos-de-estudio'])['id'] ?? 0);
profile_check($categoryId > 0, 'Case-study category fixture is missing.');
profile_post('oldest-jovani-article', 'Historical display name', $authorId, '2026-01-01T12:00:00+00:00');
profile_post('older-jovani-article', 'Historical display name', $authorId, '2026-01-02T12:00:00+00:00');
profile_post('recent-jovani-analysis', 'Historical display name', $authorId, '2026-01-03T12:00:00+00:00');
$caseId = profile_post('recent-jovani-case', 'Historical display name', $authorId, '2026-01-04T12:00:00+00:00', 'published', $categoryId);
profile_post('recent-legacy-contribution', 'Jovani Treviño', null, '2026-01-05T12:00:00+00:00');
profile_post('private-jovani-draft', 'Jovani Treviño', $authorId, '2026-01-07T12:00:00+00:00', 'draft');
profile_post('foreign-latest-article', 'Other Author', $otherAuthorId, '2026-01-08T12:00:00+00:00');
save_post_translation($caseId, 'es', ['title'=>'Caso traducido de Jovani', 'slug'=>'caso-traducido-jovani', 'excerpt'=>'Caso de estudio localizado.', 'content_html'=>'<p>Contenido de prueba.</p>']);

profile_scenario('CMS profile localizes its biography and selects only three recent published contributions', function (): void {
    foreach (['/jovani'=>'en', '/es/jovani'=>'es'] as $uri => $locale) {
        $page = profile_render($uri);
        $body = profile_value($page, '//body');
        $headline = $locale === 'es' ? 'Perfil de investigación localizada' : 'Evidence-led profile fixture';
        $bio = $locale === 'es' ? 'Biografía única del autor en español.' : 'Unique English author biography fixture.';
        profile_check(str_contains($body, $headline) && str_contains($body, $bio), 'Localized CMS profile fields were not rendered.');
        profile_check(str_starts_with(profile_value($page, '//meta[@name="description"]/@content'), $headline), 'Profile metadata ignored the localized CMS headline.');
        foreach (['recent-legacy-contribution', 'recent-jovani-analysis'] as $slug) profile_check(str_contains($body, 'Title ' . $slug), 'Recent author contribution missing: ' . $slug);
        profile_check(str_contains($body, $locale === 'es' ? 'Caso traducido de Jovani' : 'Title recent-jovani-case'), 'Published case study or its translation was omitted.');
        foreach (['oldest-jovani-article', 'older-jovani-article', 'private-jovani-draft', 'foreign-latest-article'] as $slug) profile_check(!str_contains($body, 'Title ' . $slug), 'Profile leaked an unrelated, draft or older article: ' . $slug);
        $counter = profile_value($page, '//*[contains(concat(" ",normalize-space(@class)," ")," ms-profile-publication-count ")]');
        profile_check(preg_match('/^5\s/', $counter) === 1, 'Publication count must include all five published contributions and exclude drafts/other authors.');
    }
});

profile_scenario('both public routes keep canonical, locale and media URLs correct under /blog', function () use ($authorId): void {
    db_run('UPDATE authors SET avatar_url = ? WHERE id = ?', ['/blog/static/brand/jovani-trevino.jpg', $authorId]);
    foreach (['/blog/jovani'=>'en', '/blog/about'=>'en', '/blog/es/jovani'=>'es', '/blog/es/about'=>'es'] as $uri => $locale) {
        $page = profile_render($uri, '/blog/public/index.php');
        $canonical = 'http://localhost/blog/' . ($locale === 'es' ? 'es/' : '') . 'jovani';
        profile_check(profile_value($page, '//link[@rel="canonical"]/@href') === $canonical, 'Alias or mounted canonical URL is incorrect.');
        profile_check(profile_value($page, '//meta[@property="og:url"]/@content') === $canonical, 'Open Graph URL differs from canonical.');
        profile_check(profile_value($page, '//link[@hreflang="en"]/@href') === 'http://localhost/blog/jovani', 'English alternate URL lost the mount.');
        profile_check(profile_value($page, '//link[@hreflang="es"]/@href') === 'http://localhost/blog/es/jovani', 'Spanish alternate URL lost the mount.');
        profile_check(str_starts_with(profile_value($page, '//meta[@property="og:image"]/@content'), 'http://localhost/blog/static/brand/jovani-profile.webp?v='), 'Legacy avatar was not upgraded with the correct mount.');
        $articleLink = profile_value($page, '//a[contains(@href,"recent-legacy-contribution")]/@href');
        profile_check(str_starts_with($articleLink, '/blog/' . ($locale === 'es' ? 'es/' : '')), 'Article link lost its mount or locale.');
    }
});

profile_scenario('explicit safe CMS portraits are preserved and unsafe URLs fall back', function () use ($authorId): void {
    foreach ([
        '/uploads/authors/custom-profile.webp'=>'http://localhost/blog/uploads/authors/custom-profile.webp',
        'https://images.example.test/custom-profile.webp'=>'https://images.example.test/custom-profile.webp',
        'javascript:alert(1)'=>'http://localhost/blog/static/brand/jovani-profile.webp?v=',
    ] as $avatar => $expected) {
        db_run('UPDATE authors SET avatar_url = ? WHERE id = ?', [$avatar, $authorId]);
        $page = profile_render('/blog/jovani', '/blog/public/index.php');
        $actual = profile_value($page, '//meta[@property="og:image"]/@content');
        profile_check(str_ends_with($expected, '?v=') ? str_starts_with($actual, $expected) : $actual === $expected, 'CMS avatar was incorrectly replaced, prefixed or accepted.');
    }
});

profile_scenario('the canonical jovani CMS record takes precedence over its legacy slug', function (): void {
    db_run('INSERT INTO authors (name,slug,headline_en,bio_en,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?)', ['Jovani Treviño', 'jovani', 'Primary Jovani record fixture', 'Primary biography fixture.', 'active', date('c'), date('c')]);
    $page = profile_render('/about');
    profile_check(str_contains(profile_value($page, '//body'), 'Primary Jovani record fixture'), 'Legacy CMS slug unexpectedly overrode the primary record.');
    profile_check(profile_value($page, '//meta[@property="profile:username"]/@content') === 'jovani', 'Profile username ignored the selected author record.');
    profile_check(profile_value($page, '//link[@rel="canonical"]/@href') === 'http://localhost/jovani', 'Root alias canonical URL is incorrect.');
});
