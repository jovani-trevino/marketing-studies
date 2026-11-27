<?php

function db(): PDO
{
    $pdo = $GLOBALS['marketing_studies_db'] ?? null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $driver = app_config('db_driver', 'sqlite');
    if ($driver === 'mysql') {
        $mysql = app_config('mysql');
        $dsn = 'mysql:host=' . $mysql['host'] . ';port=' . (int) ($mysql['port'] ?? 3306) . ';dbname=' . $mysql['database'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $mysql['username'], $mysql['password']);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } elseif ($driver === 'sqlite') {
        $path = (string) app_config('sqlite_path');
        if ($path !== ':memory:' && !is_dir(dirname($path))) mkdir(dirname($path), 0700, true);
        $pdo = new PDO('sqlite:' . $path);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        if ($path !== ':memory:') { $pdo->exec('PRAGMA journal_mode = WAL'); chmod($path, 0600); }
    } else {
        throw new RuntimeException('El controlador de base de datos no es compatible.');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $GLOBALS['marketing_studies_db'] = $pdo;
    return $pdo;
}

function close_db(): void
{
    unset($GLOBALS['marketing_studies_db']);
}

function db_statement(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    foreach (array_values($params) as $index => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : (is_bool($value) ? PDO::PARAM_BOOL : ($value === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
        $statement->bindValue($index + 1, $value, $type);
    }
    $statement->execute();
    return $statement;
}

function create_initial_admin(string $username, string $password): int
{
    $username = trim($username);
    if ($username === '' || mb_strlen($username) > 191 || preg_match('/[\x00-\x20\x7f]/', $username)) throw new RuntimeException('Elige un usuario de hasta 191 caracteres, sin espacios.');
    if (strlen($password) < 12 || strlen($password) > 72) throw new RuntimeException('La contraseña debe contener al menos 12 caracteres y no superar 72 bytes.');
    // An initial setup is never a password reset for an existing installation.
    if (db_one('SELECT id FROM users LIMIT 1') || db_one("SELECT value FROM settings WHERE `key` = 'cms_password_hash' AND value != '' LIMIT 1")
        || (string) app_config('admin_password_hash', '') !== '' || (string) app_config('admin_password', '') !== '') {
        throw new RuntimeException('Ya existe un acceso configurado. No se modificó ninguna cuenta; utiliza el CMS para cambiar sus credenciales.');
    }
    db_run('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)', [$username, password_hash($password, PASSWORD_DEFAULT), date('c')]);
    return (int) db()->lastInsertId();
}

function db_all(string $sql, array $params = []): array
{
    $stmt = db_statement($sql, $params);
    return $stmt->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $stmt = db_statement($sql, $params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function db_run(string $sql, array $params = []): void
{
    db_statement($sql, $params);
}

function db_columns(string $table): array
{
    $driver = app_config('db_driver', 'sqlite');
    if ($driver === 'mysql') {
        $columns = [];
        foreach (db_all("SHOW COLUMNS FROM `$table`") as $row) {
            $columns[] = $row['Field'];
        }
        return $columns;
    }
    $columns = [];
    foreach (db_all("PRAGMA table_info($table)") as $row) {
        $columns[] = $row['name'];
    }
    return $columns;
}

function ensure_db_column(string $table, string $column, string $definition): void
{
    if (in_array($column, db_columns($table), true)) {
        return;
    }
    db_run("ALTER TABLE $table ADD COLUMN $column $definition");
}

function ensure_runtime_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $driver = app_config('db_driver', 'sqlite');
    // Fresh installations work without importing any private database snapshot.
    // Both schemas are additive; existing account and editorial rows stay intact.
    $schemaPath = dirname(__DIR__) . '/database/' . ($driver === 'mysql' ? 'mysql' : 'sqlite') . '_schema.sql';
    $schema = (string) file_get_contents($schemaPath);
    foreach (preg_split('/;\s*(?:\r?\n|$)/', $schema) ?: [] as $statement) {
        if (trim($statement) !== '') db()->exec($statement);
    }
    if ($driver === 'mysql') {
        db_run("
            CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL UNIQUE,
                slug VARCHAR(191) NOT NULL UNIQUE,
                name_en VARCHAR(191) NULL,
                name_es VARCHAR(191) NULL,
                slug_en VARCHAR(191) NULL,
                slug_es VARCHAR(191) NULL,
                description TEXT,
                description_en TEXT,
                description_es TEXT,
                accent_color VARCHAR(24) NOT NULL DEFAULT '#C9853B',
                icon_class VARCHAR(191) NOT NULL DEFAULT 'bi bi-megaphone-fill',
                spotlight TEXT,
                spotlight_en TEXT,
                spotlight_es TEXT,
                parent_id INT UNSIGNED NULL,
                show_in_nav TINYINT(1) NOT NULL DEFAULT 1,
                nav_order INT NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        db_run("
            CREATE TABLE IF NOT EXISTS authors (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL,
                slug VARCHAR(191) NOT NULL UNIQUE,
                role VARCHAR(191) NULL,
                role_en VARCHAR(191) NULL,
                role_es VARCHAR(191) NULL,
                headline TEXT,
                headline_en TEXT,
                headline_es TEXT,
                bio TEXT,
                bio_en TEXT,
                bio_es TEXT,
                expertise_areas TEXT,
                expertise_areas_en TEXT,
                expertise_areas_es TEXT,
                profile_labels TEXT,
                profile_labels_en TEXT,
                profile_labels_es TEXT,
                highlights TEXT,
                highlights_en TEXT,
                highlights_es TEXT,
                avatar_url TEXT,
                professional_links TEXT,
                linkedin_url TEXT,
                github_url TEXT,
                x_url TEXT,
                website_url TEXT,
                orcid_id VARCHAR(32) NULL,
                orcid_name VARCHAR(191) NULL,
                orcid_verified_at VARCHAR(32) NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'active',
                created_at VARCHAR(32) NOT NULL,
                updated_at VARCHAR(32) NOT NULL,
                PRIMARY KEY (id),
                KEY idx_authors_status (status),
                KEY idx_authors_orcid_id (orcid_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        ensure_db_column('categories', 'name_en', 'VARCHAR(191) NULL');
        ensure_db_column('categories', 'name_es', 'VARCHAR(191) NULL');
        ensure_db_column('categories', 'slug_en', 'VARCHAR(191) NULL');
        ensure_db_column('categories', 'slug_es', 'VARCHAR(191) NULL');
        ensure_db_column('categories', 'description_en', 'TEXT NULL');
        ensure_db_column('categories', 'description_es', 'TEXT NULL');
        ensure_db_column('categories', 'parent_id', 'INT UNSIGNED NULL');
        ensure_db_column('categories', 'show_in_nav', "TINYINT(1) NOT NULL DEFAULT 1");
        ensure_db_column('categories', 'nav_order', 'INT NOT NULL DEFAULT 0');
        ensure_db_column('categories', 'spotlight_en', 'TEXT NULL');
        ensure_db_column('categories', 'spotlight_es', 'TEXT NULL');
        ensure_db_column('authors', 'role_en', 'VARCHAR(191) NULL');
        ensure_db_column('authors', 'role_es', 'VARCHAR(191) NULL');
        ensure_db_column('authors', 'headline', 'TEXT NULL');
        ensure_db_column('authors', 'headline_en', 'TEXT NULL');
        ensure_db_column('authors', 'headline_es', 'TEXT NULL');
        ensure_db_column('authors', 'bio_en', 'TEXT NULL');
        ensure_db_column('authors', 'bio_es', 'TEXT NULL');
        ensure_db_column('authors', 'expertise_areas', 'TEXT NULL');
        ensure_db_column('authors', 'expertise_areas_en', 'TEXT NULL');
        ensure_db_column('authors', 'expertise_areas_es', 'TEXT NULL');
        ensure_db_column('authors', 'profile_labels', 'TEXT NULL');
        ensure_db_column('authors', 'profile_labels_en', 'TEXT NULL');
        ensure_db_column('authors', 'profile_labels_es', 'TEXT NULL');
        ensure_db_column('authors', 'highlights', 'TEXT NULL');
        ensure_db_column('authors', 'highlights_en', 'TEXT NULL');
        ensure_db_column('authors', 'highlights_es', 'TEXT NULL');
        ensure_db_column('authors', 'professional_links', 'TEXT NULL');
        ensure_db_column('authors', 'github_url', 'TEXT NULL');
        ensure_db_column('authors', 'orcid_id', 'VARCHAR(32) NULL');
        ensure_db_column('authors', 'orcid_name', 'VARCHAR(191) NULL');
        ensure_db_column('authors', 'orcid_verified_at', 'VARCHAR(32) NULL');
        ensure_db_column('posts', 'author_id', 'INT UNSIGNED NULL');
        ensure_db_column('posts', 'newsletter_sent_at', 'VARCHAR(32) NULL');
        ensure_db_column('newsletter_subscribers', 'status', "VARCHAR(32) NOT NULL DEFAULT 'active'");
        ensure_db_column('newsletter_subscribers', 'unsubscribe_token', 'VARCHAR(64) NULL');
        ensure_db_column('newsletter_subscribers', 'unsubscribed_at', 'VARCHAR(32) NULL');
        ensure_db_column('newsletter_subscribers', 'last_sent_at', 'VARCHAR(32) NULL');
        db_run("
            CREATE TABLE IF NOT EXISTS post_translations (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id INT UNSIGNED NOT NULL,
                locale VARCHAR(8) NOT NULL,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(191) NOT NULL,
                excerpt TEXT NOT NULL,
                content_markdown MEDIUMTEXT NOT NULL,
                content_html MEDIUMTEXT NOT NULL,
                seo_title VARCHAR(255),
                seo_description TEXT,
                created_at VARCHAR(32) NOT NULL,
                updated_at VARCHAR(32) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_post_translations_post_locale (post_id, locale),
                UNIQUE KEY uq_post_translations_locale_slug (locale, slug),
                KEY idx_post_translations_slug_locale (slug, locale),
                CONSTRAINT fk_post_translations_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        db_run("
            CREATE TABLE IF NOT EXISTS newsletter_campaigns (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id INT UNSIGNED NULL,
                subject VARCHAR(255) NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'completed',
                recipient_count INT NOT NULL DEFAULT 0,
                sent_count INT NOT NULL DEFAULT 0,
                failed_count INT NOT NULL DEFAULT 0,
                created_at VARCHAR(32) NOT NULL,
                sent_at VARCHAR(32) NULL,
                PRIMARY KEY (id),
                KEY idx_newsletter_campaigns_post_id (post_id),
                KEY idx_newsletter_campaigns_created (created_at),
                CONSTRAINT fk_newsletter_campaigns_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } else {
        db_run("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                slug TEXT NOT NULL UNIQUE,
                name_en TEXT,
                name_es TEXT,
                slug_en TEXT,
                slug_es TEXT,
                description TEXT,
                description_en TEXT,
                description_es TEXT,
                accent_color TEXT NOT NULL DEFAULT '#C9853B',
                icon_class TEXT NOT NULL DEFAULT 'bi bi-megaphone-fill',
                spotlight TEXT,
                spotlight_en TEXT,
                spotlight_es TEXT,
                parent_id INTEGER NULL,
                show_in_nav INTEGER NOT NULL DEFAULT 1,
                nav_order INTEGER NOT NULL DEFAULT 0
            )
        ");
        db_run("
            CREATE TABLE IF NOT EXISTS authors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                role TEXT,
                role_en TEXT,
                role_es TEXT,
                headline TEXT,
                headline_en TEXT,
                headline_es TEXT,
                bio TEXT,
                bio_en TEXT,
                bio_es TEXT,
                expertise_areas TEXT,
                expertise_areas_en TEXT,
                expertise_areas_es TEXT,
                profile_labels TEXT,
                profile_labels_en TEXT,
                profile_labels_es TEXT,
                highlights TEXT,
                highlights_en TEXT,
                highlights_es TEXT,
                avatar_url TEXT,
                professional_links TEXT,
                linkedin_url TEXT,
                github_url TEXT,
                x_url TEXT,
                website_url TEXT,
                orcid_id TEXT,
                orcid_name TEXT,
                orcid_verified_at TEXT,
                status TEXT NOT NULL DEFAULT 'active',
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )
        ");
        db_run("CREATE INDEX IF NOT EXISTS idx_authors_status ON authors (status)");
        ensure_db_column('categories', 'name_en', 'TEXT NULL');
        ensure_db_column('categories', 'name_es', 'TEXT NULL');
        ensure_db_column('categories', 'slug_en', 'TEXT NULL');
        ensure_db_column('categories', 'slug_es', 'TEXT NULL');
        ensure_db_column('categories', 'description_en', 'TEXT NULL');
        ensure_db_column('categories', 'description_es', 'TEXT NULL');
        ensure_db_column('categories', 'parent_id', 'INTEGER NULL');
        ensure_db_column('categories', 'show_in_nav', 'INTEGER NOT NULL DEFAULT 1');
        ensure_db_column('categories', 'nav_order', 'INTEGER NOT NULL DEFAULT 0');
        ensure_db_column('categories', 'spotlight_en', 'TEXT NULL');
        ensure_db_column('categories', 'spotlight_es', 'TEXT NULL');
        ensure_db_column('authors', 'role_en', 'TEXT NULL');
        ensure_db_column('authors', 'role_es', 'TEXT NULL');
        ensure_db_column('authors', 'headline', 'TEXT NULL');
        ensure_db_column('authors', 'headline_en', 'TEXT NULL');
        ensure_db_column('authors', 'headline_es', 'TEXT NULL');
        ensure_db_column('authors', 'bio_en', 'TEXT NULL');
        ensure_db_column('authors', 'bio_es', 'TEXT NULL');
        ensure_db_column('authors', 'expertise_areas', 'TEXT NULL');
        ensure_db_column('authors', 'expertise_areas_en', 'TEXT NULL');
        ensure_db_column('authors', 'expertise_areas_es', 'TEXT NULL');
        ensure_db_column('authors', 'profile_labels', 'TEXT NULL');
        ensure_db_column('authors', 'profile_labels_en', 'TEXT NULL');
        ensure_db_column('authors', 'profile_labels_es', 'TEXT NULL');
        ensure_db_column('authors', 'highlights', 'TEXT NULL');
        ensure_db_column('authors', 'highlights_en', 'TEXT NULL');
        ensure_db_column('authors', 'highlights_es', 'TEXT NULL');
        ensure_db_column('authors', 'professional_links', 'TEXT NULL');
        ensure_db_column('authors', 'github_url', 'TEXT NULL');
        ensure_db_column('authors', 'orcid_id', 'TEXT NULL');
        ensure_db_column('authors', 'orcid_name', 'TEXT NULL');
        ensure_db_column('authors', 'orcid_verified_at', 'TEXT NULL');
        ensure_db_column('posts', 'author_id', 'INTEGER NULL');
        ensure_db_column('posts', 'newsletter_sent_at', 'TEXT NULL');
        ensure_db_column('newsletter_subscribers', 'status', "TEXT NOT NULL DEFAULT 'active'");
        ensure_db_column('newsletter_subscribers', 'unsubscribe_token', 'TEXT NULL');
        ensure_db_column('newsletter_subscribers', 'unsubscribed_at', 'TEXT NULL');
        ensure_db_column('newsletter_subscribers', 'last_sent_at', 'TEXT NULL');
        db_run("
            CREATE TABLE IF NOT EXISTS post_translations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NOT NULL,
                locale TEXT NOT NULL,
                title TEXT NOT NULL,
                slug TEXT NOT NULL,
                excerpt TEXT NOT NULL,
                content_markdown TEXT NOT NULL,
                content_html TEXT NOT NULL,
                seo_title TEXT,
                seo_description TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                UNIQUE (post_id, locale),
                UNIQUE (locale, slug)
            )
        ");
        db_run("CREATE INDEX IF NOT EXISTS idx_post_translations_slug_locale ON post_translations (slug, locale)");
        db_run("
            CREATE TABLE IF NOT EXISTS newsletter_campaigns (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NULL,
                subject TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'completed',
                recipient_count INTEGER NOT NULL DEFAULT 0,
                sent_count INTEGER NOT NULL DEFAULT 0,
                failed_count INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                sent_at TEXT NULL,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
            )
        ");
        db_run("CREATE INDEX IF NOT EXISTS idx_newsletter_campaigns_post_id ON newsletter_campaigns (post_id)");
    }
    ensure_system_categories();
    migrate_author_links_to_professional_profiles();
    seed_default_authors();
    seed_default_post_translations();
    ensure_newsletter_tokens();
}

function migrate_author_links_to_professional_profiles(): void
{
    foreach (db_all("SELECT id, github_url, x_url FROM authors") as $author) {
        $githubUrl = trim((string) ($author['github_url'] ?? ''));
        $legacyUrl = trim((string) ($author['x_url'] ?? ''));
        if ($githubUrl !== '' || $legacyUrl === '') {
            continue;
        }
        if (stripos($legacyUrl, 'github.com') === false) {
            continue;
        }
        db_run("UPDATE authors SET github_url = ? WHERE id = ?", [$legacyUrl, $author['id']]);
    }
}

function system_category_definitions(): array
{
    return [
        'casos-de-estudio' => [
            'slug' => 'casos-de-estudio',
            'name' => 'Casos de Estudio',
            'accent_color' => '#8B5B2F',
            'icon_class' => 'bi bi-layout-text-window-reverse',
            'nav_order' => 60,
            'admin_path' => '/admin/case-studies',
            'admin_label' => 'Casos',
            'publish_copy' => 'Publica sus posts desde el modulo Casos del CMS.',
        ],
        'lab' => [
            'slug' => 'lab',
            'name' => 'Lab',
            'accent_color' => '#E0A35A',
            'icon_class' => 'bi bi-flask',
            'nav_order' => 70,
            'admin_path' => '/admin/lab-resources',
            'admin_label' => 'Lab',
            'publish_copy' => 'Publica sus recursos desde el modulo Lab del CMS.',
        ],
    ];
}

function system_category_definition(string $slug): ?array
{
    $canonical = canonical_category_slug($slug);
    $definitions = system_category_definitions();
    return $definitions[$canonical] ?? null;
}

function is_system_category_slug(string $slug): bool
{
    return system_category_definition($slug) !== null;
}

function ensure_system_categories(): void
{
    foreach (system_category_definitions() as $canonical => $definition) {
        $copyEn = CATEGORY_COPY[$canonical]['en'] ?? [];
        $copyEs = CATEGORY_COPY[$canonical]['es'] ?? [];
        $aliases = array_values(array_unique(array_filter([
            $canonical,
            (string) ($copyEn['slug'] ?? ''),
            (string) ($copyEs['slug'] ?? ''),
        ])));
        $placeholders = implode(',', array_fill(0, count($aliases), '?'));
        $row = db_one("
            SELECT *
            FROM categories
            WHERE slug IN ($placeholders) OR slug_en IN ($placeholders) OR slug_es IN ($placeholders)
            ORDER BY CASE WHEN slug = ? THEN 0 ELSE 1 END, id ASC
            LIMIT 1
        ", array_merge($aliases, $aliases, $aliases, [$canonical]));

        $defaults = [
            'name' => $definition['name'],
            'slug' => $canonical,
            'name_en' => (string) ($copyEn['name'] ?? $definition['name']),
            'name_es' => (string) ($copyEs['name'] ?? $definition['name']),
            'slug_en' => (string) ($copyEn['slug'] ?? $canonical),
            'slug_es' => (string) ($copyEs['slug'] ?? $canonical),
            'description' => (string) ($copyEs['description'] ?? ($copyEn['description'] ?? '')),
            'description_en' => (string) ($copyEn['description'] ?? ''),
            'description_es' => (string) ($copyEs['description'] ?? ''),
            'accent_color' => $definition['accent_color'],
            'icon_class' => $definition['icon_class'],
            'spotlight' => (string) ($copyEs['spotlight'] ?? ($copyEn['spotlight'] ?? '')),
            'spotlight_en' => (string) ($copyEn['spotlight'] ?? ''),
            'spotlight_es' => (string) ($copyEs['spotlight'] ?? ''),
            'parent_id' => null,
            'show_in_nav' => 1,
            'nav_order' => $definition['nav_order'],
        ];

        if ($row) {
            $payload = [];
            foreach ($defaults as $field => $defaultValue) {
                if ($field === 'slug') {
                    $payload[$field] = $defaultValue;
                    continue;
                }

                if (in_array($field, ['parent_id', 'show_in_nav', 'nav_order'], true)) {
                    $payload[$field] = array_key_exists($field, $row) ? $row[$field] : $defaultValue;
                    if ($field === 'parent_id' && ($payload[$field] === '' || $payload[$field] === 0 || $payload[$field] === '0')) {
                        $payload[$field] = null;
                    }
                    continue;
                }

                $currentValue = trim((string) ($row[$field] ?? ''));
                $payload[$field] = $currentValue !== '' ? $row[$field] : $defaultValue;
            }

            db_run("UPDATE categories SET name=?, slug=?, name_en=?, name_es=?, slug_en=?, slug_es=?, description=?, description_en=?, description_es=?, accent_color=?, icon_class=?, spotlight=?, spotlight_en=?, spotlight_es=?, parent_id=?, show_in_nav=?, nav_order=? WHERE id=?", [
                $payload['name'],
                $payload['slug'],
                $payload['name_en'],
                $payload['name_es'],
                $payload['slug_en'],
                $payload['slug_es'],
                $payload['description'],
                $payload['description_en'],
                $payload['description_es'],
                $payload['accent_color'],
                $payload['icon_class'],
                $payload['spotlight'],
                $payload['spotlight_en'],
                $payload['spotlight_es'],
                $payload['parent_id'],
                $payload['show_in_nav'],
                $payload['nav_order'],
                $row['id'],
            ]);
            continue;
        }

        db_run("INSERT INTO categories (name, slug, name_en, name_es, slug_en, slug_es, description, description_en, description_es, accent_color, icon_class, spotlight, spotlight_en, spotlight_es, parent_id, show_in_nav, nav_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $defaults['name'],
            $defaults['slug'],
            $defaults['name_en'],
            $defaults['name_es'],
            $defaults['slug_en'],
            $defaults['slug_es'],
            $defaults['description'],
            $defaults['description_en'],
            $defaults['description_es'],
            $defaults['accent_color'],
            $defaults['icon_class'],
            $defaults['spotlight'],
            $defaults['spotlight_en'],
            $defaults['spotlight_es'],
            $defaults['parent_id'],
            $defaults['show_in_nav'],
            $defaults['nav_order'],
        ]);
    }
}

function seed_default_authors(): void
{
    $seedVersion = 'authors-v2';
    $seededVersion = '';
    try {
        $seededVersion = (string) ((db_one("SELECT value FROM settings WHERE `key` = ? LIMIT 1", ['authors_seed_version'])['value'] ?? ''));
    } catch (Throwable) {
        $seededVersion = '';
    }

    if ($seededVersion === $seedVersion) {
        sync_post_author_links();
        return;
    }

    $now = date('c');
    $names = [];
    foreach (db_all("SELECT DISTINCT author_name FROM posts WHERE author_name IS NOT NULL AND author_name != ''") as $row) {
        $names[] = trim((string) $row['author_name']);
    }
    $siteAuthor = trim((string) (settings()['author_name'] ?? default_settings()['author_name'] ?? 'Jovani'));
    $authorCount = db_one("SELECT COUNT(*) AS total FROM authors");
    if (!$names && (int) ($authorCount['total'] ?? 0) === 0 && $siteAuthor !== '') {
        $names[] = $siteAuthor;
    }
    $names = array_values(array_unique(array_filter($names)));
    foreach ($names as $name) {
        $slug = unique_author_slug(slugify($name));
        $existing = db_one("SELECT * FROM authors WHERE slug = ? OR name = ? LIMIT 1", [$slug, $name]);
        if (!$existing) {
            $avatar = slugify($name) === 'jovani' || str_contains(slugify($name), 'jovani') ? '/static/brand/jovani-trevino.jpg' : '';
            db_run("INSERT INTO authors (name, slug, role, bio, avatar_url, professional_links, linkedin_url, github_url, x_url, website_url, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, '', '', '', '', '', 'active', ?, ?)", [
                $name,
                $slug,
                'Editor',
                'Actionable learnings, case studies and tactics for founders, marketers and growth teams.',
                $avatar,
                $now,
                $now,
            ]);
        }
        $author = db_one("SELECT id FROM authors WHERE slug = ? OR name = ? LIMIT 1", [$slug, $name]);
        if ($author) {
            db_run("UPDATE posts SET author_id = ? WHERE (author_id IS NULL OR author_id = 0) AND author_name = ?", [$author['id'], $name]);
        }
    }
    try {
        save_setting('authors_seed_version', $seedVersion);
    } catch (Throwable) {
        // If settings persistence is not available, the seed still succeeds for this request.
    }
    sync_post_author_links();
}

function sync_post_author_links(): void
{
    foreach (db_all("SELECT id, name FROM authors WHERE name IS NOT NULL AND name != ''") as $author) {
        db_run("UPDATE posts SET author_id = ? WHERE (author_id IS NULL OR author_id = 0) AND author_name = ?", [(int) $author['id'], (string) $author['name']]);
    }
}

function seed_default_post_translations(): void
{
    $count = db_one("SELECT COUNT(*) AS total FROM post_translations");
    if ((int) ($count['total'] ?? 0) > 0) {
        return;
    }
    $now = date('c');
    $driver = app_config('db_driver', 'sqlite');
    $insertSql = $driver === 'mysql'
        ? "INSERT IGNORE INTO post_translations (post_id, locale, title, slug, excerpt, content_markdown, content_html, seo_title, seo_description, created_at, updated_at) VALUES (?, 'es', ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        : "INSERT OR IGNORE INTO post_translations (post_id, locale, title, slug, excerpt, content_markdown, content_html, seo_title, seo_description, created_at, updated_at) VALUES (?, 'es', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    foreach (db_all("SELECT * FROM posts") as $post) {
        db_run($insertSql, [
            $post['id'],
            $post['title'] ?? '',
            $post['slug'] ?? '',
            $post['excerpt'] ?? '',
            $post['content_markdown'] ?? '',
            $post['content_html'] ?? '',
            $post['seo_title'] ?? '',
            $post['seo_description'] ?? '',
            $now,
            $now,
        ]);
    }
}

function newsletter_token(): string
{
    return bin2hex(random_bytes(24));
}

function ensure_newsletter_tokens(): void
{
    foreach (db_all("SELECT id FROM newsletter_subscribers WHERE unsubscribe_token IS NULL OR unsubscribe_token = ''") as $subscriber) {
        db_run("UPDATE newsletter_subscribers SET unsubscribe_token = ? WHERE id = ?", [newsletter_token(), $subscriber['id']]);
    }
}

function default_settings(): array
{
    return [
        'site_title' => 'Marketing Studies',
        'site_tagline' => 'Research, strategy and growth for sharper marketing decisions.',
        'logo_url' => '/static/brand/marketing-studies-logo.png',
        'hero_title' => 'Research-backed marketing for smarter growth.',
        'hero_description' => 'Marketing Studies turns research, strategy and growth into practical guides, case studies and resources for business teams.',
        'hero_background_image' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1800&q=82',
        'about_blurb' => 'Research-led learnings, case studies and tactics for founders, marketers and growth teams.',
        'author_name' => 'Jovani',
        'site_share_image' => '/static/brand/marketing-studies-logo.png',
        'comments_intro' => 'Share your point of view, leave a rating and join the conversation about growth, SEO and performance.',
        'newsletter_enabled' => '1',
        'newsletter_from_name' => 'Marketing Studies',
        'newsletter_from_email' => '',
        'newsletter_subject_prefix' => 'New from Marketing Studies',
        'newsletter_smtp_host' => '',
        'newsletter_smtp_port' => '465',
        'newsletter_smtp_encryption' => 'ssl',
        'newsletter_smtp_username' => '',
        'newsletter_smtp_password' => '',
        'cookie_consent_enabled' => '1',
        'cookie_consent_title' => 'We use cookies to improve your experience',
        'cookie_consent_message' => 'We use functional and measurement cookies to improve the blog, show ads and understand what content creates more value.',
        'comments_moderation_enabled' => '1',
        'recaptcha_enabled' => '0',
        'recaptcha_site_key' => '',
        'recaptcha_secret_key' => '',
        'adsense_script' => '',
        'ad_home_top' => '',
        'ad_sidebar' => '',
        'ad_article_inline' => '',
        'ad_footer' => '',
        'social_linkedin_url' => '',
        'social_x_url' => '',
        'social_instagram_url' => '',
        'social_youtube_url' => '',
        'social_tiktok_url' => '',
        'contact_email' => 'jovani@marketing-studies.com',
    ];
}

function settings(): array
{
    $locale = $GLOBALS['locale'] ?? DEFAULT_LOCALE;
    if (isset($GLOBALS['marketing_settings_cache'][$locale])) return $GLOBALS['marketing_settings_cache'][$locale];
    $settings = default_settings();
    try {
        foreach (db_all('SELECT `key`, value FROM settings') as $row) {
            $settings[$row['key']] = $row['value'];
        }
    } catch (Throwable) {
        // The SQL import creates the table; this keeps first-run errors readable.
    }
    if ($locale === 'es') {
        $settings['site_tagline'] = 'Research, estrategia y growth para tomar mejores decisiones de marketing.';
        $settings['hero_title'] = 'Marketing con investigacion para crecer mejor.';
        $settings['hero_description'] = 'Marketing Studies convierte investigacion, estrategia y growth en guias practicas, casos de estudio y recursos para equipos de negocio.';
        $settings['about_blurb'] = 'Aprendizajes con criterio, estudios de caso y tacticas accionables para founders, marketers y equipos de growth.';
        $settings['comments_intro'] = 'Comparte tu punto de vista, deja tu rating y conversemos sobre growth, SEO y performance.';
        $settings['cookie_consent_title'] = 'Usamos cookies para mejorar la experiencia';
        $settings['cookie_consent_message'] = 'Utilizamos cookies funcionales y de medicion para mejorar el blog, mostrar publicidad y entender que contenido genera mas valor.';
    }
    $GLOBALS['marketing_settings_cache'][$locale] = $settings;
    return $settings;
}

function save_setting(string $key, string $value): void
{
    $driver = app_config('db_driver', 'sqlite');
    if ($driver === 'mysql') {
        db_run('INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)', [$key, $value]);
    } else {
        db_run('INSERT INTO settings (`key`, value) VALUES (?, ?) ON CONFLICT(`key`) DO UPDATE SET value = excluded.value', [$key, $value]);
    }
    unset($GLOBALS['marketing_settings_cache']);
}

function post_select(): string
{
    return "
        posts.*,
        categories.name AS category_name,
        categories.slug AS category_slug,
        categories.description AS category_description,
        categories.accent_color AS category_color,
        categories.icon_class AS category_icon,
        categories.spotlight AS category_spotlight,
        COALESCE((SELECT authors.name FROM authors WHERE authors.id = posts.author_id), posts.author_name) AS author_display_name,
        (SELECT authors.slug FROM authors WHERE authors.id = posts.author_id) AS author_slug,
        (SELECT authors.avatar_url FROM authors WHERE authors.id = posts.author_id) AS author_avatar_url,
        (SELECT authors.bio FROM authors WHERE authors.id = posts.author_id) AS author_bio,
        (SELECT authors.bio_en FROM authors WHERE authors.id = posts.author_id) AS author_bio_en,
        (SELECT authors.bio_es FROM authors WHERE authors.id = posts.author_id) AS author_bio_es,
        (SELECT authors.role FROM authors WHERE authors.id = posts.author_id) AS author_role,
        (SELECT authors.role_en FROM authors WHERE authors.id = posts.author_id) AS author_role_en,
        (SELECT authors.role_es FROM authors WHERE authors.id = posts.author_id) AS author_role_es,
        COALESCE((SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.status = 'approved'), 0) AS approved_comment_count,
        COALESCE((SELECT ROUND(AVG(comments.rating), 1) FROM comments WHERE comments.post_id = posts.id AND comments.status = 'approved'), 0) AS average_rating
    ";
}

function unique_author_slug(string $slug, ?int $ignoreId = null): string
{
    $slug = slugify($slug);
    $base = $slug;
    $suffix = 2;
    while (true) {
        $sql = "SELECT id FROM authors WHERE slug = ?";
        $params = [$slug];
        if ($ignoreId) {
            $sql .= " AND id != ?";
            $params[] = $ignoreId;
        }
        if (!db_one($sql, $params)) {
            return $slug;
        }
        $slug = $base . '-' . $suffix++;
    }
}

function authors(bool $activeOnly = false): array
{
    $where = $activeOnly ? "WHERE status = 'active'" : '';
    return db_all("SELECT authors.*, (SELECT COUNT(*) FROM posts WHERE posts.author_id = authors.id OR posts.author_name = authors.name) AS total_posts FROM authors $where ORDER BY name ASC");
}

function author_by_id(int $id): ?array
{
    return db_one("SELECT * FROM authors WHERE id = ? LIMIT 1", [$id]);
}

function author_by_slug(string $slug): ?array
{
    return db_one("SELECT * FROM authors WHERE slug = ? LIMIT 1", [$slug]);
}

function author_post_filter(array $author): array
{
    return [
        'where' => '(posts.author_id = ? OR posts.author_name = ?)',
        'params' => [(int) $author['id'], $author['name']],
    ];
}

function get_post_translation(int $postId, string $locale): ?array
{
    if (!in_array($locale, SUPPORTED_LOCALES, true)) {
        return null;
    }
    return db_one("SELECT * FROM post_translations WHERE post_id = ? AND locale = ? LIMIT 1", [$postId, $locale]);
}

function apply_post_translation(array $post, ?string $locale = null): array
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    $post['base_slug'] = $post['base_slug'] ?? ($post['slug'] ?? '');
    $post['base_title'] = $post['base_title'] ?? ($post['title'] ?? '');
    $post['base_excerpt'] = $post['base_excerpt'] ?? ($post['excerpt'] ?? '');
    $post['base_content_markdown'] = $post['base_content_markdown'] ?? ($post['content_markdown'] ?? '');
    $post['base_content_html'] = $post['base_content_html'] ?? ($post['content_html'] ?? '');
    $post['base_seo_title'] = $post['base_seo_title'] ?? ($post['seo_title'] ?? '');
    $post['base_seo_description'] = $post['base_seo_description'] ?? ($post['seo_description'] ?? '');
    foreach (['slug', 'title', 'excerpt', 'content_markdown', 'content_html', 'seo_title', 'seo_description'] as $field) {
        $baseField = 'base_' . $field;
        if (array_key_exists($baseField, $post)) {
            $post[$field] = $post[$baseField];
        }
    }
    $post['translation_locale'] = $locale;
    $post['translated'] = false;
    if (empty($post['id']) || !in_array($locale, SUPPORTED_LOCALES, true)) {
        return $post;
    }
    $translation = get_post_translation((int) $post['id'], $locale);
    if (!$translation) {
        return $post;
    }
    foreach (['title', 'slug', 'excerpt', 'content_markdown', 'content_html', 'seo_title', 'seo_description'] as $field) {
        if (trim((string) ($translation[$field] ?? '')) !== '') {
            $post[$field] = $translation[$field];
        }
    }
    $post['translated'] = true;
    return $post;
}

function normalize_category_slugs(string|array $categorySlug): array
{
    if (is_array($categorySlug)) {
        $slugs = $categorySlug;
    } else {
        $slugs = trim($categorySlug) !== '' ? [$categorySlug] : [];
    }

    $normalized = [];
    foreach ($slugs as $slug) {
        $resolved = canonical_category_slug((string) $slug);
        if ($resolved !== '') {
            $normalized[] = $resolved;
        }
    }

    return array_values(array_unique($normalized));
}

function append_post_category_scope(array &$where, array &$params, string|array $categorySlug = '', string|array $excludeCategorySlug = ''): void
{
    $categorySlugs = normalize_category_slugs($categorySlug);
    if ($categorySlugs) {
        $placeholders = implode(',', array_fill(0, count($categorySlugs), '?'));
        $where[] = "categories.slug IN ($placeholders)";
        array_push($params, ...$categorySlugs);
    }

    $excludedCategorySlugs = normalize_category_slugs($excludeCategorySlug);
    if ($excludedCategorySlugs) {
        $placeholders = implode(',', array_fill(0, count($excludedCategorySlugs), '?'));
        $where[] = "(categories.slug IS NULL OR categories.slug NOT IN ($placeholders))";
        array_push($params, ...$excludedCategorySlugs);
    }
}

function normalize_post_exclude_ids(int|array $excludeIds): array
{
    $items = is_array($excludeIds) ? $excludeIds : [$excludeIds];
    $normalized = [];

    foreach ($items as $item) {
        $id = (int) $item;
        if ($id > 0) {
            $normalized[] = $id;
        }
    }

    return array_values(array_unique($normalized));
}

function published_posts(int $limit = 6, int $offset = 0, int|array $excludeIds = 0, string $search = '', string|array $categorySlug = '', string|array $excludeCategorySlug = '', bool $excludeFeatured = false): array
{
    $where = ["posts.status = 'published'"];
    $params = [];

    $excluded = normalize_post_exclude_ids($excludeIds);
    if ($excluded) {
        $placeholders = implode(',', array_fill(0, count($excluded), '?'));
        $where[] = "posts.id NOT IN ($placeholders)";
        array_push($params, ...$excluded);
    }
    append_post_search($where, $params, $search);
    if ($excludeFeatured) {
        $where[] = '(posts.is_featured IS NULL OR posts.is_featured = 0)';
    }
    append_post_category_scope($where, $params, $categorySlug, $excludeCategorySlug);
    $sql = 'SELECT ' . post_select() . ' FROM posts LEFT JOIN categories ON categories.id = posts.category_id WHERE ' . implode(' AND ', $where) . ' ORDER BY posts.published_at DESC, posts.updated_at DESC, posts.id DESC LIMIT ? OFFSET ?';
    $params[] = max(1, min(5000, $limit));
    $params[] = max(0, $offset);
    return array_map('localize_post', db_all($sql, $params));
}

function count_published_posts(string $search = '', string|array $categorySlug = '', string|array $excludeCategorySlug = '', bool $excludeFeatured = false): int
{
    $where = ["posts.status = 'published'"];
    $params = [];
    append_post_search($where, $params, $search);
    if ($excludeFeatured) {
        $where[] = '(posts.is_featured IS NULL OR posts.is_featured = 0)';
    }
    append_post_category_scope($where, $params, $categorySlug, $excludeCategorySlug);
    $row = db_one('SELECT COUNT(*) AS total FROM posts LEFT JOIN categories ON categories.id = posts.category_id WHERE ' . implode(' AND ', $where), $params);
    return (int) ($row['total'] ?? 0);
}

function search_like_pattern(string $search): string
{
    return '%' . strtr(mb_substr($search, 0, 200), ['!'=>'!!', '%'=>'!%', '_'=>'!_']) . '%';
}

function append_post_search(array &$where, array &$params, string $search): void
{
    if ($search === '') return;
    $where[] = "(posts.title LIKE ? ESCAPE '!' OR posts.excerpt LIKE ? ESCAPE '!' OR posts.content_markdown LIKE ? ESCAPE '!' OR posts.content_html LIKE ? ESCAPE '!' OR EXISTS (SELECT 1 FROM post_translations pt WHERE pt.post_id = posts.id AND pt.locale = ? AND (pt.title LIKE ? ESCAPE '!' OR pt.excerpt LIKE ? ESCAPE '!' OR pt.content_markdown LIKE ? ESCAPE '!' OR pt.content_html LIKE ? ESCAPE '!')))";
    $like = search_like_pattern($search);
    array_push($params, $like, $like, $like, $like, $GLOBALS['locale'] ?? DEFAULT_LOCALE, $like, $like, $like, $like);
}

function featured_posts(int $limit = 4, string|array $excludeCategorySlug = ''): array
{
    $where = ["posts.status = 'published'", 'posts.is_featured = 1'];
    $params = [];
    append_post_category_scope($where, $params, '', $excludeCategorySlug);
    $sql = 'SELECT ' . post_select() . ' FROM posts LEFT JOIN categories ON categories.id = posts.category_id WHERE ' . implode(' AND ', $where) . ' ORDER BY posts.published_at DESC, posts.updated_at DESC, posts.id DESC LIMIT ?';
    $params[] = max(1, $limit);
    return array_map('localize_post', db_all($sql, $params));
}

function featured_post(): ?array
{
    return featured_posts(1)[0] ?? null;
}

function find_post(string $slug, ?string $locale = null): ?array
{
    $locale = $locale ?: ($GLOBALS['locale'] ?? DEFAULT_LOCALE);
    $slug = trim(rawurldecode($slug), '/');
    if ($slug === '') {
        return null;
    }
    $row = db_one(
        'SELECT ' . post_select() . ",
            CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM post_translations locale_match
                    WHERE locale_match.post_id = posts.id
                      AND locale_match.locale = ?
                      AND locale_match.slug = ?
                ) THEN 0
                WHEN posts.slug = ? THEN 1
                WHEN EXISTS (
                    SELECT 1
                    FROM post_translations any_match
                    WHERE any_match.post_id = posts.id
                      AND any_match.slug = ?
                ) THEN 2
                ELSE 3
            END AS slug_match_priority
        FROM posts
        LEFT JOIN categories ON categories.id = posts.category_id
        WHERE posts.status = 'published'
          AND (
              posts.slug = ?
              OR EXISTS (
                    SELECT 1
                    FROM post_translations locale_match
                    WHERE locale_match.post_id = posts.id
                      AND locale_match.locale = ?
                      AND locale_match.slug = ?
              )
              OR EXISTS (
                    SELECT 1
                    FROM post_translations any_match
                    WHERE any_match.post_id = posts.id
                      AND any_match.slug = ?
              )
          )
        ORDER BY slug_match_priority ASC, posts.published_at DESC, posts.updated_at DESC, posts.id DESC
        LIMIT 1",
        [$locale, $slug, $slug, $slug, $slug, $locale, $slug, $slug]
    );
    return $row ? localize_post($row, $locale) : null;
}

function sort_categories(array $categories): array
{
    usort($categories, static function (array $left, array $right): int {
        $leftOrder = (int) ($left['nav_order'] ?? 0);
        $rightOrder = (int) ($right['nav_order'] ?? 0);
        if ($leftOrder !== $rightOrder) {
            return $leftOrder <=> $rightOrder;
        }
        return strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
    });

    return $categories;
}

function build_category_tree(array $categories, ?int $parentId = null): array
{
    $branch = [];

    foreach ($categories as $category) {
        $categoryParentId = isset($category['parent_id']) && $category['parent_id'] !== '' ? (int) $category['parent_id'] : null;
        if ($categoryParentId !== $parentId) {
            continue;
        }

        $category['children'] = build_category_tree($categories, (int) $category['id']);
        $category['has_children'] = $category['children'] !== [];
        $branch[] = $category;
    }

    return $branch;
}

function flatten_category_tree(array $categories, int $depth = 0): array
{
    $items = [];

    foreach ($categories as $category) {
        $category['depth'] = $depth;
        $category['tree_label'] = str_repeat('-- ', $depth) . ($category['name'] ?? '');
        $items[] = $category;
        if (!empty($category['children'])) {
            array_push($items, ...flatten_category_tree($category['children'], $depth + 1));
        }
    }

    return $items;
}

function public_categories(bool $navOnly = false): array
{
    $where = $navOnly ? 'WHERE COALESCE(categories.show_in_nav, 1) = 1' : '';
    $rows = db_all("
        SELECT
            categories.*,
            parent.name AS parent_name,
            parent.slug AS parent_slug,
            COUNT(CASE WHEN posts.status = 'published' THEN posts.id END) AS total_posts
        FROM categories
        LEFT JOIN categories AS parent ON parent.id = categories.parent_id
        LEFT JOIN posts ON posts.category_id = categories.id
        $where
        GROUP BY categories.id
    ");

    $localized = array_map('localize_category', $rows);
    return sort_categories($localized);
}

function public_category_tree(bool $navOnly = false): array
{
    $categories = public_categories($navOnly);
    $knownIds = array_map(static fn(array $category): int => (int) $category['id'], $categories);

    foreach ($categories as &$category) {
        $parentId = isset($category['parent_id']) && $category['parent_id'] !== '' ? (int) $category['parent_id'] : 0;
        if ($parentId > 0 && !in_array($parentId, $knownIds, true)) {
            $category['parent_id'] = null;
            $category['parent_name'] = null;
            $category['parent_slug'] = null;
        }
    }
    unset($category);

    return build_category_tree($categories);
}

function public_category_options(bool $navOnly = false): array
{
    return flatten_category_tree(public_category_tree($navOnly));
}

function category_slug_aliases(string $slug): array
{
    $slug = trim($slug, '/');
    if ($slug === '') {
        return [];
    }

    $aliases = [$slug];
    $row = db_one("SELECT slug, slug_en, slug_es FROM categories WHERE slug = ? OR slug_en = ? OR slug_es = ? LIMIT 1", [$slug, $slug, $slug]);
    if ($row) {
        foreach (['slug', 'slug_en', 'slug_es'] as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '') {
                $aliases[] = $value;
            }
        }
    }

    foreach (CATEGORY_COPY as $canonical => $copy) {
        $matched = $canonical === $slug;
        foreach ($copy as $localeCopy) {
            if (($localeCopy['slug'] ?? '') === $slug) {
                $matched = true;
            }
        }
        if ($matched) {
            $aliases[] = $canonical;
            foreach ($copy as $localeCopy) {
                if (!empty($localeCopy['slug'])) {
                    $aliases[] = (string) $localeCopy['slug'];
                }
            }
        }
    }

    return array_values(array_unique($aliases));
}

function category_slug_from_alias(string $slug): string
{
    $slug = trim($slug, '/');
    $aliases = category_slug_aliases($slug);
    if (!$aliases) {
        return $slug;
    }

    $placeholders = implode(',', array_fill(0, count($aliases), '?'));
    $params = array_merge($aliases, $aliases, $aliases, [$slug, $slug, $slug]);
    $row = db_one("
        SELECT slug
        FROM categories
        WHERE slug IN ($placeholders) OR slug_en IN ($placeholders) OR slug_es IN ($placeholders)
        ORDER BY CASE WHEN slug = ? OR slug_en = ? OR slug_es = ? THEN 0 ELSE 1 END, id ASC
        LIMIT 1
    ", $params);

    if ($row) {
        return (string) ($row['slug'] ?? $slug);
    }

    foreach (CATEGORY_COPY as $canonical => $copy) {
        if ($canonical === $slug) {
            return $canonical;
        }
        foreach ($copy as $localeCopy) {
            if (($localeCopy['slug'] ?? '') === $slug) {
                return $canonical;
            }
        }
    }

    return $slug;
}

function nav_categories(): array
{
    return public_category_tree(true);
}

function find_category(string $slug): ?array
{
    $aliases = category_slug_aliases($slug);
    if (!$aliases) {
        return null;
    }

    $placeholders = implode(',', array_fill(0, count($aliases), '?'));
    $requestedSlug = trim($slug, '/');
    $params = array_merge($aliases, $aliases, $aliases, [$requestedSlug, $requestedSlug, $requestedSlug]);
    $row = db_one("
        SELECT
            categories.*,
            parent.name AS parent_name,
            parent.slug AS parent_slug,
            COUNT(CASE WHEN posts.status = 'published' THEN posts.id END) AS total_posts
        FROM categories
        LEFT JOIN categories AS parent ON parent.id = categories.parent_id
        LEFT JOIN posts ON posts.category_id = categories.id
        WHERE categories.slug IN ($placeholders) OR categories.slug_en IN ($placeholders) OR categories.slug_es IN ($placeholders)
        GROUP BY categories.id
        ORDER BY CASE WHEN categories.slug = ? OR categories.slug_en = ? OR categories.slug_es = ? THEN 0 ELSE 1 END, categories.id ASC
        LIMIT 1
    ", $params);

    return $row ? localize_category($row) : null;
}

function unique_category_slug(string $slug, ?int $ignoreId = null): string
{
    $slug = slugify($slug);
    $base = $slug;
    $suffix = 2;

    while (true) {
        $sql = "SELECT id FROM categories WHERE (slug = ? OR slug_en = ? OR slug_es = ?)";
        $params = [$slug, $slug, $slug];
        if ($ignoreId) {
            $sql .= " AND id != ?";
            $params[] = $ignoreId;
        }
        if (!db_one($sql, $params)) {
            return $slug;
        }
        $slug = $base . '-' . $suffix++;
    }
}

function category_descendant_ids(int $categoryId): array
{
    $rows = db_all("SELECT id, parent_id FROM categories");
    $byParent = [];

    foreach ($rows as $row) {
        $parentId = isset($row['parent_id']) && $row['parent_id'] !== '' ? (int) $row['parent_id'] : 0;
        $byParent[$parentId][] = (int) $row['id'];
    }

    $ids = [];
    $stack = [$categoryId];

    while ($stack) {
        $currentId = array_pop($stack);
        if (in_array($currentId, $ids, true)) {
            continue;
        }
        $ids[] = $currentId;
        foreach ($byParent[$currentId] ?? [] as $childId) {
            $stack[] = $childId;
        }
    }

    return $ids;
}

function category_descendant_slugs(int $categoryId): array
{
    $ids = category_descendant_ids($categoryId);
    if (!$ids) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $rows = db_all("SELECT slug FROM categories WHERE id IN ($placeholders)", $ids);
    return array_values(array_unique(array_map(static fn(array $row): string => (string) $row['slug'], $rows)));
}

function comments_for_post(int $postId): array
{
    return db_all("SELECT * FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC", [$postId]);
}

function comment_summary(int $postId): array
{
    return db_one("SELECT COUNT(*) AS total_comments, COALESCE(ROUND(AVG(rating), 1), 0) AS average_rating FROM comments WHERE post_id = ? AND status = 'approved'", [$postId]) ?: ['total_comments' => 0, 'average_rating' => 0];
}

function related_posts(array $post): array
{
    $canonical = $post['category_canonical_slug'] ?? canonical_category_slug($post['category_slug'] ?? '');
    return published_posts(3, 0, (int) $post['id'], '', $canonical);
}

function unique_post_translation_slug(string $slug, string $locale, int $postId): string
{
    $slug = slugify($slug);
    $base = $slug;
    $suffix = 2;
    while (true) {
        $row = db_one("SELECT post_id FROM post_translations WHERE locale = ? AND slug = ? AND post_id != ? LIMIT 1", [$locale, $slug, $postId]);
        $baseRow = db_one("SELECT id FROM posts WHERE slug = ? AND id != ? LIMIT 1", [$slug, $postId]);
        if (!$row && !$baseRow) {
            return $slug;
        }
        $slug = $base . '-' . $suffix++;
    }
}

function save_post_translation(int $postId, string $locale, array $data): void
{
    if (!in_array($locale, SUPPORTED_LOCALES, true)) {
        return;
    }
    $title = trim((string) ($data['title'] ?? ''));
    $excerpt = trim((string) ($data['excerpt'] ?? ''));
    $content = trim((string) ($data['content_markdown'] ?? ''));
    $contentHtml = trim((string) ($data['content_html'] ?? ''));
    $seoTitle = trim((string) ($data['seo_title'] ?? ''));
    $seoDescription = trim((string) ($data['seo_description'] ?? ''));
    $slugInput = trim((string) ($data['slug'] ?? ''));
    if ($title === '' && $excerpt === '' && $content === '' && $contentHtml === '' && $seoTitle === '' && $seoDescription === '' && $slugInput === '') {
        db_run("DELETE FROM post_translations WHERE post_id = ? AND locale = ?", [$postId, $locale]);
        return;
    }
    $basePost = db_one("SELECT * FROM posts WHERE id = ? LIMIT 1", [$postId]) ?: [];
    $title = $title ?: (string) ($basePost['title'] ?? '');
    $excerpt = $excerpt ?: seo_text($contentHtml !== '' ? $contentHtml : markdown_to_html($content), 240) ?: (string) ($basePost['excerpt'] ?? '');
    $content = $content ?: (string) ($basePost['content_markdown'] ?? '');
    $contentHtml = sanitize_article_html($contentHtml ?: ($content !== '' ? markdown_to_html($content) : trim((string) ($basePost['content_html'] ?? ''))));
    if (mb_strlen($title) > 255 || strlen($contentHtml) > 2097152 || strlen($content) > 2097152 || mb_strlen($excerpt) > 10000) throw new RuntimeException('La traducción supera el tamaño permitido.');
    $existing = get_post_translation($postId, $locale);
    $slug = unique_post_translation_slug($slugInput ?: ($existing['slug'] ?? $title), $locale, $postId);
    $payload = [
        $postId,
        $locale,
        $title,
        $slug,
        $excerpt,
        $content,
        $contentHtml,
        seo_text($seoTitle ?: $title, 255),
        seo_description(['seo_description' => $seoDescription, 'excerpt' => $excerpt, 'content_html' => $contentHtml]),
        date('c'),
        date('c'),
    ];
    if (app_config('db_driver', 'sqlite') === 'mysql') {
        db_run(
            "INSERT INTO post_translations (post_id, locale, title, slug, excerpt, content_markdown, content_html, seo_title, seo_description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), slug = VALUES(slug), excerpt = VALUES(excerpt), content_markdown = VALUES(content_markdown), content_html = VALUES(content_html), seo_title = VALUES(seo_title), seo_description = VALUES(seo_description), updated_at = VALUES(updated_at)",
            $payload
        );
    } else {
        db_run(
            "INSERT INTO post_translations (post_id, locale, title, slug, excerpt, content_markdown, content_html, seo_title, seo_description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(post_id, locale) DO UPDATE SET title = excluded.title, slug = excluded.slug, excerpt = excluded.excerpt, content_markdown = excluded.content_markdown, content_html = excluded.content_html, seo_title = excluded.seo_title, seo_description = excluded.seo_description, updated_at = excluded.updated_at",
            $payload
        );
    }
}

function lab_resources(): array
{
    return db_all("SELECT * FROM lab_resources WHERE status = 'published' ORDER BY is_featured DESC, published_at DESC, updated_at DESC");
}

function social_links(array $site): array
{
    $map = [
        ['key' => 'social_linkedin_url', 'label' => 'LinkedIn', 'icon' => 'bi bi-linkedin'],
        ['key' => 'social_x_url', 'label' => 'X', 'icon' => 'bi bi-twitter-x'],
        ['key' => 'social_instagram_url', 'label' => 'Instagram', 'icon' => 'bi bi-instagram'],
        ['key' => 'social_youtube_url', 'label' => 'YouTube', 'icon' => 'bi bi-youtube'],
        ['key' => 'social_tiktok_url', 'label' => 'TikTok', 'icon' => 'bi bi-tiktok'],
    ];
    $links = [];
    foreach ($map as $item) {
        $url = safe_content_url((string) ($site[$item['key']] ?? ''));
        if ($url !== '') {
            $links[] = ['url' => $url, 'label' => $item['label'], 'icon' => $item['icon']];
        }
    }
    return $links;
}

function active_newsletter_subscribers(): array
{
    return db_all("SELECT * FROM newsletter_subscribers WHERE status = 'active' ORDER BY created_at DESC");
}

function newsletter_subscribers(int $limit = 200): array
{
    return db_all("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT ?", [$limit]);
}

function newsletter_campaigns(int $limit = 50): array
{
    return db_all("
        SELECT newsletter_campaigns.*, posts.title AS post_title, posts.slug AS post_slug, categories.slug AS category_slug
        FROM newsletter_campaigns
        LEFT JOIN posts ON posts.id = newsletter_campaigns.post_id
        LEFT JOIN categories ON categories.id = posts.category_id
        ORDER BY newsletter_campaigns.created_at DESC
        LIMIT ?
    ", [$limit]);
}

function newsletter_stats(): array
{
    return db_one("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) AS unsubscribed
        FROM newsletter_subscribers
    ") ?: ['total' => 0, 'active' => 0, 'unsubscribed' => 0];
}

function newsletter_subscriber_by_email(string $email): ?array
{
    $subscriber = db_one("SELECT * FROM newsletter_subscribers WHERE LOWER(email) = LOWER(?) LIMIT 1", [strtolower(trim($email))]);
    return $subscriber ?: null;
}

function save_admin_newsletter_subscriber(string $email, string $locale, string $source, string $status = 'active'): bool
{
    $email = strtolower(trim($email));
    $locale = in_array($locale, SUPPORTED_LOCALES, true) ? $locale : DEFAULT_LOCALE;
    $source = substr(trim($source), 0, 64) ?: 'cms';
    $status = in_array($status, ['active', 'unsubscribed'], true) ? $status : 'active';
    $existing = newsletter_subscriber_by_email($email);
    $token = newsletter_token();
    $now = date('c');
    $unsubscribedAt = $status === 'unsubscribed' ? $now : null;

    if ($existing) {
        db_run(
            "UPDATE newsletter_subscribers SET email = ?, locale = ?, source = ?, status = ?, unsubscribed_at = ?, unsubscribe_token = CASE WHEN unsubscribe_token IS NULL OR unsubscribe_token = '' THEN ? ELSE unsubscribe_token END WHERE id = ?",
            [$email, $locale, $source, $status, $unsubscribedAt, $token, $existing['id']]
        );
        return false;
    }

    db_run(
        "INSERT INTO newsletter_subscribers (email, locale, source, status, unsubscribe_token, unsubscribed_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$email, $locale, $source, $status, $token, $unsubscribedAt, $now]
    );

    return true;
}
function subscribe_or_reactivate_newsletter(string $email, string $locale, string $source): void
{
    save_admin_newsletter_subscriber($email, $locale, $source, 'active');
}

function unsubscribe_newsletter_token(string $token): bool
{
    $subscriber = db_one("SELECT * FROM newsletter_subscribers WHERE unsubscribe_token = ? LIMIT 1", [$token]);
    if (!$subscriber) {
        return false;
    }
    db_run("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = ? WHERE id = ?", [date('c'), $subscriber['id']]);
    return true;
}

function newsletter_mailer_config(array $site): array
{
    $config = app_config('newsletter_smtp', []);
    // Empty CMS defaults must not mask a configured private SMTP transport.
    $source = trim((string) ($site['newsletter_smtp_host'] ?? '')) !== ''
        ? ['host'=>$site['newsletter_smtp_host'], 'port'=>$site['newsletter_smtp_port'] ?? 465, 'encryption'=>$site['newsletter_smtp_encryption'] ?? 'ssl', 'username'=>$site['newsletter_smtp_username'] ?? '', 'password'=>$site['newsletter_smtp_password'] ?? '']
        : $config;
    $encryption = strtolower(trim((string) ($source['encryption'] ?? 'ssl')));
    if (!in_array($encryption, ['ssl', 'tls', 'none'], true)) {
        $encryption = 'ssl';
    }

    return [
        'host' => trim((string) ($source['host'] ?? '')),
        'port' => max(1, min(65535, (int) ($source['port'] ?? 465))),
        'encryption' => $encryption,
        'username' => trim((string) ($source['username'] ?? '')),
        'password' => (string) ($source['password'] ?? ''),
        'timeout' => max(8, (int) ($config['timeout'] ?? 20)),
    ];
}

function newsletter_mailer_status(?array $site = null): array
{
    $site = $site ?: settings();
    $smtp = newsletter_mailer_config($site);
    $smtpReady = $smtp['host'] !== '' && $smtp['port'] > 0;

    if ($smtpReady) {
        $authLabel = ($smtp['username'] !== '' || $smtp['password'] !== '') ? 'SMTP autenticado' : 'SMTP sin autenticacion';
        return [
            'transport' => 'smtp',
            'configured' => true,
            'label' => $authLabel,
            'summary' => $smtp['host'] . ':' . $smtp['port'] . ' · ' . strtoupper($smtp['encryption']),
        ];
    }

    return [
        'transport' => 'mail',
        'configured' => false,
        'label' => 'mail() del servidor',
        'summary' => 'Fallback local. Para Gmail/Outlook conviene SMTP + SPF/DKIM.',
    ];
}

function newsletter_sender_email(array $site): string
{
    $configured = trim((string) ($site['newsletter_from_email'] ?? ''));
    if (filter_var($configured, FILTER_VALIDATE_EMAIL)) {
        return $configured;
    }
    $smtp = newsletter_mailer_config($site);
    if (filter_var($smtp['username'] ?? '', FILTER_VALIDATE_EMAIL)) {
        return $smtp['username'];
    }
    $contact = trim((string) ($site['contact_email'] ?? ''));
    if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        return $contact;
    }
    $host = preg_replace('/^www\./', '', strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'marketing-studies.com')));
    $host = preg_replace('/:\d+$/', '', $host);
    if (!str_contains($host, '.') || filter_var($host, FILTER_VALIDATE_IP)) {
        $host = 'marketing-studies.com';
    }
    return 'newsletter@' . $host;
}

function newsletter_subject(array $post, array $site): string
{
    $prefix = trim((string) ($site['newsletter_subject_prefix'] ?? ''));
    return ($prefix !== '' ? $prefix . ': ' : '') . $post['title'];
}

function newsletter_header_encode(string $value): string
{
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function newsletter_header_address(string $email, string $name = ''): string
{
    $email = trim($email);
    $name = trim($name);
    if ($name === '') {
        return $email;
    }
    return newsletter_header_encode($name) . ' <' . $email . '>';
}

function newsletter_host_domain(array $site): string
{
    $fromEmail = newsletter_sender_email($site);
    $parts = explode('@', $fromEmail);
    $domain = strtolower(trim((string) end($parts)));
    if ($domain !== '' && str_contains($domain, '.')) {
        return $domain;
    }
    $host = preg_replace('/:\d+$/', '', strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'marketing-studies.com')));
    if ($host === '' || !str_contains($host, '.')) {
        return 'marketing-studies.com';
    }
    return preg_replace('/^www\./', '', $host);
}

function newsletter_build_message(string $to, string $subject, string $html, string $plain, array $site, string $unsubscribeUrl = ''): array
{
    $fromEmail = newsletter_sender_email($site);
    $fromName = trim((string) ($site['newsletter_from_name'] ?? 'Marketing Studies')) ?: 'Marketing Studies';
    $boundary = 'ms_' . bin2hex(random_bytes(12));
    $encodedSubject = newsletter_header_encode($subject);
    $toHeader = newsletter_header_address($to);
    $fromHeader = newsletter_header_address($fromEmail, $fromName);
    $listId = trim(preg_replace('/[^a-z0-9.-]+/i', '-', strtolower($fromName)), '-');
    $listId = ($listId !== '' ? $listId : 'marketing-studies') . '.' . newsletter_host_domain($site);
    $baseHeaders = [
        'Date: ' . date(DATE_RFC2822),
        'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . newsletter_host_domain($site) . '>',
        'MIME-Version: 1.0',
        'From: ' . $fromHeader,
        'Reply-To: ' . $fromHeader,
        'List-ID: ' . newsletter_header_encode($fromName) . ' <' . $listId . '>',
        'Precedence: bulk',
        'Auto-Submitted: auto-generated',
        'X-Auto-Response-Suppress: All',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if ($unsubscribeUrl !== '') {
        $baseHeaders[] = 'List-Unsubscribe: <mailto:' . $fromEmail . '?subject=unsubscribe>, <' . $unsubscribeUrl . '>';
        $baseHeaders[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';
    }

    $plainBody = function_exists('quoted_printable_encode') ? quoted_printable_encode(str_replace(["\r\n", "\r"], "\n", $plain)) : $plain;
    $htmlBody = function_exists('quoted_printable_encode') ? quoted_printable_encode(str_replace(["\r\n", "\r"], "\n", $html)) : $html;
    $body = "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
        . $plainBody . "\r\n"
        . "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
        . $htmlBody . "\r\n"
        . "--{$boundary}--\r\n";

    return [
        'from_email' => $fromEmail,
        'encoded_subject' => $encodedSubject,
        'mail_headers' => $baseHeaders,
        'smtp_headers' => array_merge(['To: ' . $toHeader, 'Subject: ' . $encodedSubject], $baseHeaders),
        'body' => $body,
    ];
}

function newsletter_smtp_write($socket, string $command): void
{
    fwrite($socket, $command . "\r\n");
}

function newsletter_smtp_read($socket): array
{
    $lines = [];
    while (($line = fgets($socket, 515)) !== false) {
        $lines[] = rtrim($line, "\r\n");
        if (preg_match('/^(\d{3})\s/', $line, $matches)) {
            return [(int) $matches[1], implode("\n", $lines)];
        }
        if (!preg_match('/^\d{3}-/', $line)) {
            break;
        }
    }
    throw new RuntimeException('SMTP sin respuesta valida.');
}

function newsletter_smtp_expect($socket, array $expectedCodes): string
{
    [$code, $message] = newsletter_smtp_read($socket);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('SMTP respondio ' . $code . ': ' . $message);
    }
    return $message;
}

function newsletter_smtp_send(string $to, array $payload, array $site): bool
{
    $smtp = newsletter_mailer_config($site);
    $transport = $smtp['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ]);
    $socket = @stream_socket_client(
        $transport . $smtp['host'] . ':' . $smtp['port'],
        $errno,
        $errstr,
        $smtp['timeout'],
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        throw new RuntimeException('No se pudo conectar al SMTP: ' . $errstr . ' (' . $errno . ')');
    }

    stream_set_timeout($socket, $smtp['timeout']);

    try {
        newsletter_smtp_expect($socket, [220]);
        $helo = newsletter_host_domain($site);
        newsletter_smtp_write($socket, 'EHLO ' . $helo);
        newsletter_smtp_expect($socket, [250]);

        if ($smtp['encryption'] === 'tls') {
            newsletter_smtp_write($socket, 'STARTTLS');
            newsletter_smtp_expect($socket, [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('No se pudo activar STARTTLS.');
            }
            newsletter_smtp_write($socket, 'EHLO ' . $helo);
            newsletter_smtp_expect($socket, [250]);
        }

        if ($smtp['username'] !== '' || $smtp['password'] !== '') {
            newsletter_smtp_write($socket, 'AUTH LOGIN');
            newsletter_smtp_expect($socket, [334]);
            newsletter_smtp_write($socket, base64_encode($smtp['username']));
            newsletter_smtp_expect($socket, [334]);
            newsletter_smtp_write($socket, base64_encode($smtp['password']));
            newsletter_smtp_expect($socket, [235]);
        }

        newsletter_smtp_write($socket, 'MAIL FROM:<' . $payload['from_email'] . '>');
        newsletter_smtp_expect($socket, [250]);
        newsletter_smtp_write($socket, 'RCPT TO:<' . $to . '>');
        newsletter_smtp_expect($socket, [250, 251]);
        newsletter_smtp_write($socket, 'DATA');
        newsletter_smtp_expect($socket, [354]);

        $rawMessage = implode("\r\n", $payload['smtp_headers']) . "\r\n\r\n" . $payload['body'];
        $rawMessage = preg_replace('/(?m)^\./', '..', $rawMessage) ?? $rawMessage;
        fwrite($socket, $rawMessage . "\r\n.\r\n");
        newsletter_smtp_expect($socket, [250]);
        newsletter_smtp_write($socket, 'QUIT');

        return true;
    } finally {
        fclose($socket);
    }
}

function newsletter_post_row(int $postId): ?array
{
    return db_one('SELECT ' . post_select() . " FROM posts LEFT JOIN categories ON categories.id = posts.category_id WHERE posts.id = ? AND posts.status = 'published' LIMIT 1", [$postId]);
}

function newsletter_excerpt(string $text, int $limit = 180): string
{
    $plain = strip_tags($text);
    $plain = preg_replace('/!\[[^\]]*\]\([^)]+\)/', '', $plain);
    $plain = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', (string) $plain);
    $plain = preg_replace('/(^|\s)(#{1,6}|[-*]|\d+\.)\s+/', ' ', (string) $plain);
    $plain = str_replace(['**', '*', '`', '>'], '', (string) $plain);
    $plain = trim(preg_replace('/\s+/', ' ', $plain));
    $length = function_exists('mb_strlen') ? mb_strlen($plain) : strlen($plain);
    if ($length <= $limit) {
        return $plain;
    }
    return (function_exists('mb_substr') ? mb_substr($plain, 0, $limit - 1) : substr($plain, 0, $limit - 1)) . '...';
}

function build_newsletter_email(array $post, array $subscriber, array $site): array
{
    $locale = in_array($subscriber['locale'] ?? '', SUPPORTED_LOCALES, true) ? $subscriber['locale'] : DEFAULT_LOCALE;
    $localizedPost = localize_post($post, $locale);
    $postUrl = absolute_url(post_url($localizedPost, $locale));
    $unsubscribeUrl = absolute_url(url_path('/newsletter/unsubscribe/' . ($subscriber['unsubscribe_token'] ?? ''), $locale));
    $logoUrl = absolute_url(media_url($site['logo_url'] ?? '/static/brand/marketing-studies-logo.png'));
    $siteTitle = $site['site_title'] ?? 'Marketing Studies';
    $preheader = newsletter_excerpt($localizedPost['excerpt'] ?: strip_tags($localizedPost['content_html'] ?? ''), 150);
    $cover = media_url($localizedPost['cover_image'] ?? '');
    $coverHtml = $cover !== '' ? '<img src="' . e(absolute_url($cover)) . '" alt="" style="display:block;width:100%;max-height:300px;object-fit:cover;border-radius:18px;margin:24px 0;">' : '';
    $buttonText = $locale === 'es' ? 'Leer el post completo' : 'Read the full post';
    $introTitle = $locale === 'es' ? 'Nuevo post publicado' : 'New post published';
    $unsubscribeText = $locale === 'es' ? 'Darte de baja' : 'Unsubscribe';
    $subject = newsletter_subject($localizedPost, $site);
    $bodyText = newsletter_excerpt($localizedPost['content_markdown'] ?? $localizedPost['excerpt'] ?? '', 420);
    $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($subject) . '</title></head>'
        . '<body style="margin:0;background:#f7f4ee;color:#102033;font-family:Arial,Helvetica,sans-serif;">'
        . '<div style="display:none;max-height:0;overflow:hidden;color:transparent;">' . e($preheader) . '</div>'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f4ee;padding:28px 14px;"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#fffdf8;border:1px solid #e6ddd1;border-radius:26px;overflow:hidden;">'
        . '<tr><td style="padding:28px 30px 18px;border-bottom:1px solid #e6ddd1;"><img src="' . e($logoUrl) . '" alt="' . e($siteTitle) . '" style="display:block;max-width:220px;height:auto;"></td></tr>'
        . '<tr><td style="padding:30px;">'
        . '<p style="margin:0 0 12px;color:#c9853b;font-size:12px;font-weight:800;letter-spacing:2px;text-transform:uppercase;">' . e($introTitle) . '</p>'
        . '<h1 style="margin:0 0 16px;color:#052446;font-size:34px;line-height:1.08;letter-spacing:-1.2px;">' . e($localizedPost['title']) . '</h1>'
        . '<p style="margin:0 0 18px;color:#5f6f86;font-size:17px;line-height:1.65;">' . e($localizedPost['excerpt']) . '</p>'
        . $coverHtml
        . '<div style="margin:20px 0 26px;padding:20px;border-left:4px solid #c9853b;background:#f8f2ea;border-radius:16px;color:#24364d;font-size:16px;line-height:1.65;">' . nl2br(e($bodyText)) . '</div>'
        . '<a href="' . e($postUrl) . '" style="display:inline-block;background:#052446;color:#fffdf8;text-decoration:none;border-radius:999px;padding:14px 22px;font-weight:800;">' . e($buttonText) . '</a>'
        . '</td></tr>'
        . '<tr><td style="padding:22px 30px;border-top:1px solid #e6ddd1;color:#6a7890;font-size:13px;line-height:1.55;">'
        . '<p style="margin:0 0 8px;">' . e($siteTitle) . ' · Research · Strategy · Growth</p>'
        . '<p style="margin:0;"><a href="' . e($unsubscribeUrl) . '" style="color:#6a7890;">' . e($unsubscribeText) . '</a></p>'
        . '</td></tr></table></td></tr></table></body></html>';
    $plain = $subject . "\n\n" . $localizedPost['excerpt'] . "\n\n" . $bodyText . "\n\n" . $postUrl . "\n\n" . $unsubscribeText . ': ' . $unsubscribeUrl;
    return ['subject' => $subject, 'html' => $html, 'plain' => $plain, 'url' => $postUrl];
}

function send_html_mail(string $to, string $subject, string $html, string $plain, array $site, string $unsubscribeUrl = ''): bool
{
    $payload = newsletter_build_message($to, $subject, $html, $plain, $site, $unsubscribeUrl);
    $mailer = newsletter_mailer_status($site);

    try {
        if ($mailer['transport'] === 'smtp') {
            return newsletter_smtp_send($to, $payload, $site);
        }

        return mail(
            $to,
            $payload['encoded_subject'],
            $payload['body'],
            implode("\r\n", $payload['mail_headers']),
            '-f' . $payload['from_email']
        );
    } catch (Throwable $error) {
        error_log('Newsletter send failed for ' . $to . ': ' . $error->getMessage());
        return false;
    }
}

function send_post_newsletter(int $postId, bool $force = false): array
{
    $site = settings();
    $post = newsletter_post_row($postId);
    if (!$post) {
        return ['sent' => 0, 'failed' => 0, 'recipients' => 0, 'message' => 'Post no encontrado o no publicado.'];
    }
    if (!$force && !empty($post['newsletter_sent_at'])) {
        return ['sent' => 0, 'failed' => 0, 'recipients' => 0, 'message' => 'Newsletter ya enviado previamente.'];
    }
    if (!setting_is_enabled($site['newsletter_enabled'] ?? '1')) {
        return ['sent' => 0, 'failed' => 0, 'recipients' => 0, 'message' => 'Newsletter desactivado en configuracion.'];
    }
    $subscribers = active_newsletter_subscribers();
    $sent = 0;
    $failed = 0;
    $subject = newsletter_subject(localize_post($post, DEFAULT_LOCALE), $site);
    $createdAt = date('c');
    db_run("INSERT INTO newsletter_campaigns (post_id, subject, status, recipient_count, sent_count, failed_count, created_at, sent_at) VALUES (?, ?, 'sending', ?, 0, 0, ?, NULL)", [$postId, $subject, count($subscribers), $createdAt]);
    $campaignId = (int) db()->lastInsertId();
    foreach ($subscribers as $subscriber) {
        $email = build_newsletter_email($post, $subscriber, $site);
        $unsubscribeUrl = absolute_url(url_path('/newsletter/unsubscribe/' . ($subscriber['unsubscribe_token'] ?? ''), $subscriber['locale'] ?? DEFAULT_LOCALE));
        if (send_html_mail($subscriber['email'], $email['subject'], $email['html'], $email['plain'], $site, $unsubscribeUrl)) {
            $sent++;
            db_run("UPDATE newsletter_subscribers SET last_sent_at = ? WHERE id = ?", [date('c'), $subscriber['id']]);
        } else {
            $failed++;
        }
    }
    $status = $failed > 0 ? ($sent > 0 ? 'partial' : 'failed') : 'completed';
    $sentAt = date('c');
    db_run("UPDATE newsletter_campaigns SET status = ?, sent_count = ?, failed_count = ?, sent_at = ? WHERE id = ?", [$status, $sent, $failed, $sentAt, $campaignId]);
    if ($sent > 0) {
        db_run("UPDATE posts SET newsletter_sent_at = ? WHERE id = ?", [$sentAt, $postId]);
    }
    return [
        'sent' => $sent,
        'failed' => $failed,
        'recipients' => count($subscribers),
        'message' => $sent . ' enviados, ' . $failed . ' fallidos.',
    ];
}
