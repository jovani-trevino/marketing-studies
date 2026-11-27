<?php

declare(strict_types=1);

// This suite always creates a private, temporary database. It never loads local
// deployment settings, recovered sessions or production newsletter credentials.
$directory = sys_get_temp_dir() . '/marketing-studies-test-' . bin2hex(random_bytes(6));
mkdir($directory, 0700, true);
file_put_contents($directory . '/fixture-config.php', "<?php return ['db_driver'=>'mysql','newsletter_smtp'=>['host'=>'smtp.example.test','port'=>587,'encryption'=>'tls','username'=>'fixture@example.test','password'=>'']];");
foreach (['MS_DB_DRIVER'=>'sqlite', 'MS_SQLITE_PATH'=>$directory . '/test.sqlite', 'MS_STORAGE_PATH'=>$directory, 'MS_SESSION_PATH'=>$directory . '/sessions', 'MS_RATE_LIMIT_PATH'=>$directory . '/rate-limits', 'MS_CONFIG_FILE'=>$directory . '/fixture-config.php', 'MS_ADMIN_USER'=>'', 'MS_ADMIN_PASSWORD'=>'', 'MS_ADMIN_PASSWORD_HASH'=>'', 'MS_APP_URL'=>'http://localhost', 'MS_TRUST_PROXY'=>'false'] as $name => $value) putenv($name . '=' . $value);

require dirname(__DIR__) . '/blog/app/i18n.php';
require dirname(__DIR__) . '/blog/app/helpers.php';
require dirname(__DIR__) . '/blog/app/models.php';
require dirname(__DIR__) . '/blog/app/controllers.php';

$_SERVER = array_merge($_SERVER, ['HTTP_HOST'=>'localhost', 'SCRIPT_NAME'=>'/index.php', 'REQUEST_URI'=>'/', 'REQUEST_METHOD'=>'GET', 'REMOTE_ADDR'=>'127.0.0.1']);
$_SESSION = []; $_POST = []; $_GET = [];
$GLOBALS['locale'] = 'en';
$results = []; $failures = 0; $publicFixture = null; $finalCheck = null;

function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function rejects(callable $callback, string $message): void
{
    try { $callback(); } catch (RuntimeException) { return; }
    throw new RuntimeException($message);
}

function scenario(string $name, callable $callback): void
{
    global $results, $failures;
    try { $callback(); $results[] = 'PASS ' . $name; }
    catch (Throwable $error) { $failures++; $results[] = 'FAIL ' . $name . ': ' . $error->getMessage(); }
}

function capture(callable $callback): string
{
    ob_start();
    try { $callback(); return (string) ob_get_contents(); }
    finally { ob_end_clean(); }
}

function remove_fixture_tree(string $path): void
{
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $child = $path . '/' . $entry;
        if (is_dir($child) && !is_link($child)) remove_fixture_tree($child); else unlink($child);
    }
    rmdir($path);
}

register_shutdown_function(function () use ($directory, &$publicFixture, &$finalCheck): void {
    global $results, $failures;
    if ($finalCheck) scenario('editor commits publication and translations together', $finalCheck);
    close_db();
    if ($publicFixture && is_file($publicFixture)) unlink($publicFixture);
    if (is_dir($directory)) remove_fixture_tree($directory);
    echo implode(PHP_EOL, $results) . PHP_EOL;
    echo count($results) . ' scenarios; ' . $failures . ' failures.' . PHP_EOL;
    if ($failures) exit(1);
});

scenario('first configuration lookup preserves its requested key', function (): void {
    check(app_config('sqlite_path', 'missing') === getenv('MS_SQLITE_PATH'), 'First lookup lost the requested configuration key.');
    check(is_array(app_config()), 'Full configuration is not available.');
});

scenario('fresh SQLite schema initializes without a default account', function (): void {
    ensure_runtime_schema();
    check(db_one('SELECT COUNT(*) AS total FROM users')['total'] === 0, 'An account was seeded unexpectedly.');
    check(admin_account() === null, 'Fresh installation accepts a configured account.');
    check(!admin_password_ok('any-password'), 'Empty credentials were accepted.');
    check((string) app_config('sqlite_path') === (string) getenv('MS_SQLITE_PATH'), 'Database isolation failed.');
});

save_setting('newsletter_enabled', '0');
$GLOBALS['site'] = settings();
$GLOBALS['navCategories'] = nav_categories();
$GLOBALS['allCategories'] = public_category_options(false);

scenario('initial admin setup persists a hash and refuses to reset existing credentials', function (): void {
    $password = 'Fixture only ' . bin2hex(random_bytes(8));
    rejects(fn()=>create_initial_admin('editor', 'short'), 'Short password accepted.');
    $id = create_initial_admin('editor', $password);
    check($id > 0 && admin_password_ok($password, 'editor'), 'Created account cannot authenticate.');
    check(!admin_password_ok('wrong password', 'editor'), 'Wrong password accepted.');
    $before = db_one('SELECT password_hash FROM users WHERE id = ?', [$id])['password_hash'];
    rejects(fn()=>create_initial_admin('replacement', 'Another fixture password'), 'Existing account reset was allowed.');
    check(db_one('SELECT password_hash FROM users WHERE id = ?', [$id])['password_hash'] === $before, 'Existing hash changed.');
    save_setting('cms_username', 'configured-editor'); save_setting('cms_password_hash', $before);
    check(admin_account('editor') === null && admin_account('configured-editor')['source'] === 'settings', 'Configured access reactivated a dormant legacy user.');
    db_run("DELETE FROM settings WHERE `key` IN ('cms_username','cms_password_hash')");
    $second = new PDO('sqlite:' . app_config('sqlite_path'));
    check((int) $second->query('SELECT COUNT(*) FROM users')->fetchColumn() === 1, 'Account did not persist to the database.');
});

scenario('missing, malformed and cross-origin CSRF tokens fail closed', function (): void {
    $_SESSION = []; $_POST = [];
    check(!verify_csrf(), 'Missing tokens were accepted.');
    $token = csrf_token(); $_POST['csrf_token'] = $token;
    check(verify_csrf(), 'Valid token rejected.');
    $_POST['csrf_token'] = [$token]; check(!verify_csrf(), 'Array token accepted.');
    $_POST['csrf_token'] = $token; $_SERVER['HTTP_ORIGIN'] = 'https://untrusted.invalid';
    check(!verify_csrf(), 'Foreign origin accepted.');
    unset($_SERVER['HTTP_ORIGIN']); $_POST = [];
});

scenario('attempt limits survive a new browser session', function (): void {
    check(rate_limit_allowed('test-limit', 2, 60), 'First attempt denied.');
    check(rate_limit_allowed('test-limit', 2, 60), 'Second attempt denied.');
    $_SESSION = [];
    check(!rate_limit_allowed('test-limit', 2, 60), 'New session bypassed the rate limit.');
});

scenario('rich content removes active payloads and preserves Quill, formulas and safe embeds', function (): void {
    $html = sanitize_article_html('<h1>Title</h1><p class="ql-align-center" style="color: #123456; background: url(javascript:bad)">Text <strong>bold</strong></p><span class="ql-formula" data-value="x^2"></span><pre><code>&lt;hello&gt;</code></pre><img src="/uploads/safe.png" onerror="alert(1)"><a href="javascript&#58;alert(1)">bad</a><svg><script>alert(2)</script></svg><iframe src="https://evil.invalid/embed/x" srcdoc="bad"></iframe><iframe class="ql-video" src="https://www.youtube.com/embed/abcdefghijk"></iframe>');
    check(!preg_match('/<script|<svg|onerror|javascript:|srcdoc|evil\.invalid/i', $html), 'Executable rich content survived.');
    check(str_contains($html, '<h2>Title</h2>') && str_contains($html, 'ql-align-center') && str_contains($html, 'data-value="x^2"') && str_contains($html, '<pre><code>'), 'Editor formatting was lost.');
    check(str_contains($html, 'youtube.com/embed/abcdefghijk') && str_contains($html, 'sandbox='), 'Safe video embed was lost.');
    check(!str_contains(render_article_content('<p onclick="bad()">Legacy</p>'), 'onclick'), 'Legacy rendering bypasses the sanitizer.');
    foreach (['javascript:alert(1)','//evil.invalid','data:text/html,bad',"https://safe.invalid\r\nInjected: bad"] as $url) check(safe_content_url($url) === '', 'Unsafe content URL accepted.');
});

scenario('uploads inspect actual MIME and image dimensions', function () use ($directory): void {
    $fake = $directory . '/fake.png'; file_put_contents($fake, '<script>not an image</script>');
    rejects(fn()=>validate_uploaded_content($fake, 'png'), 'Disguised executable upload accepted.');
    $png = $directory . '/real.png'; file_put_contents($png, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlWuoYAAAAASUVORK5CYII='));
    validate_uploaded_content($png, 'png');
    rejects(fn()=>validate_uploaded_content($png, 'pdf'), 'Mismatched extension accepted.');
});

scenario('optional advertising HTML is inert until the consent loader activates it', function (): void {
    $html = render_ad('<script src="https://optional.invalid/script.js"></script><img src="https://optional.invalid/pixel"><iframe src="https://optional.invalid/frame"></iframe>', 'Sponsor', 'Responsive');
    check(str_contains($html, 'data-ms-consent-code') && str_contains($html, 'hidden data-ms-consent-slot'), 'Ad slot was not gated.');
    check(!str_contains($html, '<img') && !str_contains($html, '<iframe') && !str_contains($html, '<script src='), 'Optional elements were emitted as active HTML.');
    check(str_contains(consent_code_payload('</script><img src=x>'), '\u003C'), 'Script end tag escaped the inert payload.');
});

$sampleIds = [];
for ($index = 0; $index < 35; $index++) {
    $status = $index === 34 ? 'draft' : 'published';
    db_run('INSERT INTO posts (title,slug,excerpt,content_markdown,content_html,status,author_name,read_time,created_at,updated_at,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)', ['Fixture article ' . $index, 'fixture-article-' . $index, 'Editorial fixture', 'Body test', '<p>Body test</p>', $status, 'Fixture Author', 1, date('c'), date('c'), $status === 'published' ? date('c') : null]);
    $sampleIds[] = (int) db()->lastInsertId();
}

scenario('translated rich text is searchable, SQL wildcards are literal, and drafts stay private', function () use ($sampleIds): void {
    save_post_translation($sampleIds[0], 'es', ['title'=>'Investigación traducida', 'slug'=>'investigacion-original', 'excerpt'=>'Contenido público', 'content_html'=>'<p>cohorte-especial y 50% del ejemplo</p><script>bad()</script>']);
    $GLOBALS['locale'] = 'es';
    check(count_published_posts('cohorte-especial') === 1, 'Translated HTML is not searchable.');
    check(count(published_posts(8, 0, 0, 'cohorte-especial')) === 1, 'Search count and results disagree.');
    check(count_published_posts('%') === 1, 'Search wildcard was not treated literally.');
    check(find_post('fixture-article-34') === null, 'Draft has a public article route.');
    $GLOBALS['locale'] = 'en';
    check(count_published_posts() === 34, 'Draft leaked into the public count.');
    check(count(published_posts(8, 32)) === 2, 'Last page has the wrong records.');
});

scenario('translation edits preserve existing slugs and sanitize persisted HTML', function () use ($sampleIds): void {
    save_post_translation($sampleIds[0], 'es', ['title'=>'Título revisado', 'excerpt'=>'Revisado', 'content_html'=>'<p onmouseover="bad()">Revisión</p>']);
    $saved = get_post_translation($sampleIds[0], 'es');
    check($saved['slug'] === 'investigacion-original', 'Editing a title changed the URL.');
    check(!str_contains($saved['content_html'], 'onmouseover'), 'Unsafe HTML reached storage.');
});

scenario('CMS pagination and filters query records beyond the first page', function (): void {
    $_SERVER['REQUEST_URI'] = '/admin/posts?page=2'; $_GET = ['page'=>'2'];
    $html = capture(fn()=>admin_posts());
    check(str_contains($html, 'fixture-article-0') || str_contains($html, 'Fixture article 0'), 'Second page omitted older records.');
    $_GET = ['q'=>'Fixture article 34', 'status'=>'draft'];
    $html = capture(fn()=>admin_posts());
    check(str_contains($html, 'Fixture article 34') && !str_contains($html, 'Fixture article 33'), 'Filter did not run over the whole dataset.');
    $_GET = [];
});

scenario('editor validation retains typed text and the established article URL', function () use ($sampleIds): void {
    $_SERVER['REQUEST_METHOD'] = 'POST'; $_SERVER['REQUEST_URI'] = '/admin/posts/' . $sampleIds[0] . '/edit';
    $_POST = ['csrf_token'=>csrf_token(), 'title'=>'Unsaved revision fixture', 'excerpt'=>'', 'content_markdown'=>'Retain this typed body', 'content_html'=>'<p>Retain this typed body</p>', 'status'=>'draft', 'category_id'=>''];
    $html = capture(fn()=>admin_post_form($sampleIds[0]));
    check(str_contains($html, 'Unsaved revision fixture') && str_contains($html, 'Retain this typed body'), 'Validation discarded editor input.');
    check(str_contains($html, 'fixture-article-0'), 'Validation changed the existing slug.');
    check(db_one('SELECT title FROM posts WHERE id = ?', [$sampleIds[0]])['title'] === 'Fixture article 0', 'Invalid edit was written.');
    $_POST = []; $_SERVER['REQUEST_METHOD'] = 'GET';
});

scenario('Lab counts successful local GET delivery but excludes HEAD and missing files', function () use (&$publicFixture): void {
    $name = 'test-download-' . bin2hex(random_bytes(6)) . '.csv';
    $publicFixture = dirname(__DIR__) . '/blog/public/' . $name;
    file_put_contents($publicFixture, "metric,event\ncontacts,contact_submit\n");
    db_run('INSERT INTO lab_resources (title,slug,description,download_url,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?)', ['Fixture resource', 'fixture-resource', 'Test download', '/' . $name, 'published', date('c'), date('c')]);
    $id = (int) db()->lastInsertId();
    $_SERVER['REQUEST_METHOD'] = 'HEAD'; capture(fn()=>lab_download($id));
    check((int) db_one('SELECT download_count FROM lab_resources WHERE id = ?', [$id])['download_count'] === 0, 'HEAD incremented the counter.');
    $_SERVER['REQUEST_METHOD'] = 'GET'; $csv = capture(fn()=>lab_download($id));
    check(str_contains($csv, 'contact_submit'), 'Download did not serve the file.');
    check((int) db_one('SELECT download_count FROM lab_resources WHERE id = ?', [$id])['download_count'] === 1, 'Successful GET was not counted.');
    unlink($publicFixture); $publicFixture = null; capture(fn()=>lab_download($id));
    check(http_response_code() === 404 && (int) db_one('SELECT download_count FROM lab_resources WHERE id = ?', [$id])['download_count'] === 1, 'Missing download incremented the counter.');
    http_response_code(200);
});

scenario('invalid translations roll back the complete editorial edit', function () use ($sampleIds): void {
    $_SERVER['REQUEST_METHOD'] = 'POST'; $_SERVER['REQUEST_URI'] = '/admin/posts/' . $sampleIds[1] . '/edit';
    $_POST = ['csrf_token'=>csrf_token(), 'title'=>'Must roll back this title', 'excerpt'=>'Valid excerpt', 'content_markdown'=>'Valid body', 'content_html'=>'<p>Valid body</p>', 'status'=>'draft', 'category_id'=>'', 'translations'=>['es'=>['title'=>str_repeat('x', 256), 'content_html'=>'<p>Oversized translation title</p>']]];
    capture(fn()=>admin_post_form($sampleIds[1]));
    check(db_one('SELECT title FROM posts WHERE id = ?', [$sampleIds[1]])['title'] === 'Fixture article 1', 'Partial editorial update escaped the transaction.');
    check(get_post_translation($sampleIds[1], 'es') === null, 'Failed translation was persisted.');
    $_POST = []; $_SERVER['REQUEST_METHOD'] = 'GET';
});

scenario('CRM and newsletter retain records without duplicate addresses', function (): void {
    subscribe_or_reactivate_newsletter('reader@example.test', 'en', 'test');
    subscribe_or_reactivate_newsletter('READER@example.test', 'es', 'test');
    check((int) db_one('SELECT COUNT(*) AS total FROM newsletter_subscribers')['total'] === 1, 'Duplicate email created.');
    db_run("INSERT INTO contact_messages (name,email,topic,message,locale,status,created_at) VALUES (?,?,?,?,?,'new',?)", ['Fixture contact','contact@example.test','Collaboration','A useful test message.','en',date('c')]);
    $_SERVER['REQUEST_URI'] = '/admin/contacts'; $_GET = ['q'=>'Fixture contact', 'status'=>'new'];
    $html = capture(fn()=>admin_contacts());
    check(str_contains($html, 'A useful test message.') && str_contains($html, 'Collaboration'), 'CRM lost the contact content.');
    $_GET = [];
});

scenario('empty CMS SMTP defaults preserve the explicitly configured private transport', function (): void {
    $transport = newsletter_mailer_config(default_settings());
    check($transport['host'] === 'smtp.example.test' && $transport['port'] === 587 && $transport['encryption'] === 'tls', 'Empty defaults masked the configured SMTP transport.');
    $override = newsletter_mailer_config(['newsletter_smtp_host'=>'override.example.test', 'newsletter_smtp_port'=>465, 'newsletter_smtp_encryption'=>'ssl']);
    check($override['host'] === 'override.example.test' && $override['port'] === 465, 'Saved CMS transport did not take precedence.');
});

scenario('professional templates are bounded and never overwrite an existing edit', function () use ($sampleIds): void {
    require_once dirname(__DIR__) . '/blog/app/professional.php';
    check(professional_template('../../app/config') === null, 'Template accepts arbitrary paths.');
    $before = (int) db_one('SELECT COUNT(*) AS n FROM posts')['n'];
    $_SERVER['REQUEST_METHOD'] = 'GET'; $_GET = ['template'=>'case-demanda-local'];
    $html = capture(fn()=>admin_post_form());
    check(str_contains($html, 'Capturar intención de búsqueda local') && str_contains($html, 'Alcance de la evidencia'), 'Case template is incomplete.');
    check((int) db_one('SELECT COUNT(*) AS n FROM posts')['n'] === $before, 'Opening a template writes a post.');
    $html = capture(fn()=>admin_post_form($sampleIds[0]));
    check(!str_contains($html, 'Capturar intención de búsqueda local'), 'Template overwrites an existing article.');
    $_GET = [];
});

scenario('professional progress rejects CSRF and unknown settings without changing data', function (): void {
    require_once dirname(__DIR__) . '/blog/app/professional.php';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = ['task'=>'cv','status'=>'done','csrf_token'=>'invalid'];
    capture(fn()=>professional_workspace());
    check(http_response_code() === 403 && !isset(settings()['professional_task_cv']), 'Invalid CSRF updated progress.');
    $_POST = ['task'=>'admin_password','status'=>'done','csrf_token'=>csrf_token()];
    capture(fn()=>professional_workspace());
    check(http_response_code() === 422 && !isset(settings()['professional_task_admin_password']), 'Unknown task updated settings.');
    $_SERVER['REQUEST_METHOD'] = 'GET'; $_POST = []; http_response_code(200);
    $html = capture(fn()=>professional_workspace());
    check(str_contains($html, 'Agenda editorial') && str_contains($html, 'article-30'), 'Workspace omitted the editorial agenda.');
});

scenario('portfolio blog links handle Windows root directories and explicit mounts', function (): void {
    require_once dirname(__DIR__) . '/Jovani/bootstrap.php';
    $server = $_SERVER;
    $environment = getenv('MS_BLOG_BASE_PATH');
    try {
        putenv('MS_BLOG_BASE_PATH');
        unset($_SERVER['MS_BLOG_BASE_PATH']);
        foreach (['/Jovani/index.php'=>'/blog', '/sub/Jovani/router.php'=>'/sub/blog', '/portfolio/index.php'=>''] as $script=>$expected) {
            $_SERVER['JOVANI_ORIGINAL_SCRIPT_NAME'] = $script;
            check(jovani_blog_base_path() === $expected, 'Incorrect blog mount for ' . $script);
        }
        $_SERVER['MS_BLOG_BASE_PATH'] = '/custom/blog';
        check(jovani_blog_base_path() === '/custom/blog', 'Explicit blog mount ignored.');
        $_SERVER['MS_BLOG_BASE_PATH'] = 'https://external.example';
        check(jovani_blog_base_path() === '', 'External blog mount allowed.');
    } finally {
        $_SERVER = $server;
        putenv($environment === false ? 'MS_BLOG_BASE_PATH' : 'MS_BLOG_BASE_PATH=' . $environment);
    }
});

// The successful controller redirects with exit. Verify its committed result in
// the shutdown handler, avoiding a testing backdoor in the production router.
$finalCheck = function (): void {
    $saved = db_one('SELECT * FROM posts WHERE slug = ?', ['transaction-publication']);
    check($saved !== null && $saved['status'] === 'published', 'Publication was not committed.');
    check(!str_contains($saved['content_html'], 'onerror'), 'Publication saved executable HTML.');
    check(get_post_translation((int) $saved['id'], 'es')['slug'] === 'publicacion-transaccional', 'Translation was not committed with the article.');
    check((int) db_one('SELECT COUNT(*) AS total FROM newsletter_campaigns')['total'] === 0, 'Disabled test newsletter attempted an external campaign.');
};
$_SERVER['REQUEST_METHOD'] = 'POST'; $_SERVER['REQUEST_URI'] = '/admin/posts/new';
$_POST = ['csrf_token'=>csrf_token(), 'title'=>'Transaction publication', 'slug'=>'transaction-publication', 'excerpt'=>'Published fixture', 'content_markdown'=>'', 'content_html'=>'<h2>A real rich-text publication</h2><p>Body</p><img src="/safe.png" onerror="bad()">', 'status'=>'published', 'category_id'=>'', 'translations'=>['es'=>['title'=>'Publicación transaccional','slug'=>'publicacion-transaccional','excerpt'=>'Publicación de prueba','content_html'=>'<p>Contenido publicado</p>']]];
capture(fn()=>admin_post_form());
