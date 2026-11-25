<?php
declare(strict_types=1);

// Invoked once per scenario by scripts/test.mjs. Every database belongs to the
// runner's freshly created temporary directory; no deployment config is loaded.
$case = $argv[1] ?? '';
$fixtureDirectory = $argv[2] ?? '';
if (!in_array($case, ['dry', 'empty', 'repeat', 'existing', 'preserve', 'rollback'], true) || !is_dir($fixtureDirectory)) {
    fwrite(STDERR, "Ejecuta esta suite con npm test.\n");
    exit(2);
}
$repository = dirname(__DIR__);
$databasePath = $fixtureDirectory . '/' . ($case === 'repeat' ? 'empty' : $case) . '.sqlite';
$snapshotPath = $fixtureDirectory . '/after-import.json';
foreach (['MS_DB_DRIVER'=>'sqlite', 'MS_SQLITE_PATH'=>$databasePath, 'MS_STORAGE_PATH'=>$fixtureDirectory, 'MS_SESSION_PATH'=>$fixtureDirectory . '/sessions', 'MS_RATE_LIMIT_PATH'=>$fixtureDirectory . '/rate-limits', 'MS_CONFIG_FILE'=>$fixtureDirectory . '/no-private-config.php', 'MS_ADMIN_USER'=>'', 'MS_ADMIN_PASSWORD'=>'', 'MS_ADMIN_PASSWORD_HASH'=>'', 'MS_APP_URL'=>'http://localhost', 'MS_TRUST_PROXY'=>'false'] as $key => $value) putenv($key . '=' . $value);

function importer_assert(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function importer_snapshot(PDO $connection): array
{
    $snapshot = ['schema'=>$connection->query('SELECT type,name,sql FROM sqlite_master ORDER BY type,name')->fetchAll(PDO::FETCH_ASSOC), 'rows'=>[]];
    foreach ($connection->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $safeTable = str_replace('"', '""', $table);
        $snapshot['rows'][$table] = $connection->query('SELECT * FROM "' . $safeTable . '" ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
    }
    return $snapshot;
}

$source = json_decode((string) file_get_contents($repository . '/blog/database/public-content.json'), true, 512, JSON_THROW_ON_ERROR);
$expectedArticles = count(array_unique(array_column($source['posts'], 'translation_group')));
$expectedTranslations = count($source['posts']);
$before = null;
$fixturePdo = null;
if (in_array($case, ['existing', 'preserve', 'rollback'], true)) {
    $fixturePdo = new PDO('sqlite:' . $databasePath, null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $fixturePdo->exec((string) file_get_contents($repository . '/blog/database/sqlite_schema.sql'));
    $fixturePdo->exec("INSERT INTO settings (`key`,value) VALUES ('site_title','Owner-edited publication'),('newsletter_enabled','0')");
    $fixturePdo->exec("INSERT INTO users (username,password_hash,created_at) VALUES ('fixture-editor','fixture-only-not-a-real-hash','2026-01-01')");
    $author = $fixturePdo->prepare("INSERT INTO authors (name,slug,role,bio,status,created_at,updated_at) VALUES (?,'jovani','Owner-edited role','Owner-edited biography','active','2026-01-01','2026-01-01')");
    $author->execute([$source['profile']['name']]);
    $fixturePdo->exec("INSERT INTO newsletter_subscribers (email,status,created_at) VALUES ('subscriber@example.test','active','2026-01-01')");
    $fixturePdo->exec("INSERT INTO contact_messages (name,email,message,status,created_at) VALUES ('Fixture contact','contact@example.test','Private fixture message','new','2026-01-01')");
    if ($case === 'existing') $fixturePdo->exec("INSERT INTO posts (title,slug,excerpt,content_markdown,content_html,status,author_name,created_at,updated_at) VALUES ('Existing draft','existing-draft','Original excerpt','Original body','<p>Original body</p>','draft','Existing author','2026-01-01','2026-01-01')");
    if ($case === 'rollback') $fixturePdo->exec("CREATE TRIGGER fail_second_import BEFORE INSERT ON posts WHEN (SELECT COUNT(*) FROM posts) >= 1 BEGIN SELECT RAISE(ABORT,'Intentional rollback fixture'); END");
    $before = importer_snapshot($fixturePdo);
    $fixturePdo = null;
}
if ($case === 'repeat') {
    importer_assert(is_file($databasePath) && is_file($snapshotPath), 'The initial-import fixture is missing.');
    $before = json_decode((string) file_get_contents($snapshotPath), true, 512, JSON_THROW_ON_ERROR);
}

ob_start();
register_shutdown_function(function () use ($case, $databasePath, $snapshotPath, $before, $expectedArticles, $expectedTranslations): void {
    $output = (string) ob_get_clean();
    $result = json_decode($output, true);
    $error = error_get_last();
    try {
        importer_assert(!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true), 'PHP terminated with an error.');
        if ($case === 'dry') {
            importer_assert(($result['dry_run'] ?? false) && ($result['database_opened'] ?? null) === false, 'Dry-run did not report a read-only validation.');
            importer_assert(!is_file($databasePath), 'Dry-run created a database.');
        } else {
            $check = new PDO('sqlite:' . $databasePath, null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
            $after = importer_snapshot($check);
            if ($case === 'empty') {
                importer_assert(($result['imported_articles'] ?? 0) === $expectedArticles, 'The expected article count was not imported.');
                importer_assert(($result['imported_translations'] ?? 0) === $expectedTranslations, 'Translations were not imported together.');
                importer_assert(count($after['rows']['posts']) === $expectedArticles && count($after['rows']['post_translations']) === $expectedTranslations, 'Database counts disagree with the result.');
                foreach (['users','newsletter_subscribers','contact_messages','newsletter_campaigns'] as $table) importer_assert(count($after['rows'][$table]) === 0, 'Public recovery inserted private records.');
                importer_assert(($result['newsletter_sent'] ?? null) === false, 'Recovery did not confirm no newsletter send.');
                file_put_contents($snapshotPath, json_encode($after, JSON_THROW_ON_ERROR));
            } elseif (in_array($case, ['repeat', 'existing'], true)) {
                importer_assert(($result['skipped'] ?? false) === true, 'A database with articles was not skipped.');
                importer_assert($after === $before, 'An existing database changed, including its schema or sequences.');
            } elseif ($case === 'preserve') {
                importer_assert(($result['imported_articles'] ?? 0) === $expectedArticles, 'Recovery failed on an empty editorial database.');
                foreach (['users','newsletter_subscribers','contact_messages','newsletter_campaigns'] as $table) importer_assert($after['rows'][$table] === $before['rows'][$table], 'Existing account or community records changed.');
                $settings = array_column($after['rows']['settings'], 'value', 'key');
                importer_assert($settings['site_title'] === 'Owner-edited publication' && $settings['newsletter_enabled'] === '0', 'Owner settings were replaced.');
                $author = $after['rows']['authors'][0];
                importer_assert($author['role'] === 'Owner-edited role' && $author['bio'] === 'Owner-edited biography', 'Existing author content was replaced.');
            } elseif ($case === 'rollback') {
                importer_assert(!is_array($result) || !($result['ok'] ?? false), 'The intentional database failure reported success.');
                importer_assert($after === $before, 'A failed import left partial articles, translations or settings.');
            }
            $check = null;
        }
        echo "PASS public importer: {$case}\n";
        if (function_exists('close_db')) close_db();
        exit(0);
    } catch (Throwable $error) {
        fwrite(STDERR, "FAIL public importer: {$case}: " . $error->getMessage() . "\n");
        if (function_exists('close_db')) close_db();
        exit(1);
    }
});

$argv = [$repository . '/blog/tools/import-public-content.php'];
if ($case === 'dry') $argv[] = '--dry-run';
require $repository . '/blog/tools/import-public-content.php';
