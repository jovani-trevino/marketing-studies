-- Additive schema: existing tables and records are preserved.
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    slug TEXT NOT NULL UNIQUE,
    name_en TEXT NULL,
    name_es TEXT NULL,
    slug_en TEXT NULL,
    slug_es TEXT NULL,
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
);

CREATE TABLE IF NOT EXISTS authors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    role TEXT NULL,
    role_en TEXT NULL,
    role_es TEXT NULL,
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
    orcid_id TEXT NULL,
    orcid_name TEXT NULL,
    orcid_verified_at TEXT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    excerpt TEXT NOT NULL,
    content_markdown TEXT NOT NULL,
    content_html TEXT NOT NULL,
    cover_image TEXT,
    seo_title TEXT,
    seo_description TEXT,
    status TEXT NOT NULL DEFAULT 'draft',
    is_featured INTEGER NOT NULL DEFAULT 0,
    author_name TEXT NOT NULL,
    author_id INTEGER NULL,
    category_id INTEGER NULL,
    read_time INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    published_at TEXT NULL,
    newsletter_sent_at TEXT NULL,
    CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL,
    CONSTRAINT fk_posts_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

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
    UNIQUE (post_id, locale),
    UNIQUE (locale, slug),
    CONSTRAINT fk_post_translations_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    author_name TEXT NOT NULL,
    author_email TEXT NOT NULL,
    author_website TEXT,
    body TEXT NOT NULL,
    rating INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    ip_address TEXT,
    user_agent TEXT,
    is_recaptcha_verified INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    locale TEXT NOT NULL DEFAULT 'en',
    source TEXT DEFAULT 'home',
    status TEXT NOT NULL DEFAULT 'active',
    unsubscribe_token TEXT NULL,
    unsubscribed_at TEXT NULL,
    last_sent_at TEXT NULL,
    created_at TEXT NOT NULL
);

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
    CONSTRAINT fk_newsletter_campaigns_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    topic TEXT,
    message TEXT NOT NULL,
    locale TEXT NOT NULL DEFAULT 'en',
    status TEXT NOT NULL DEFAULT 'new',
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS lab_resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    resource_type TEXT NOT NULL DEFAULT 'template',
    download_url TEXT NOT NULL,
    cover_image TEXT,
    author_name TEXT,
    source_name TEXT,
    source_url TEXT,
    status TEXT NOT NULL DEFAULT 'draft',
    is_featured INTEGER NOT NULL DEFAULT 0,
    download_count INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    published_at TEXT NULL
);

CREATE TABLE IF NOT EXISTS settings (
    `key` TEXT NOT NULL,
    value TEXT NOT NULL,
    PRIMARY KEY (`key`)
);


CREATE INDEX IF NOT EXISTS idx_posts_status_published ON posts (status, published_at, updated_at);
CREATE INDEX IF NOT EXISTS idx_comments_post_status ON comments (post_id, status, created_at);
CREATE INDEX IF NOT EXISTS idx_newsletter_subscribers_status ON newsletter_subscribers (status);
CREATE INDEX IF NOT EXISTS idx_contacts_status_created ON contact_messages (status, created_at);
