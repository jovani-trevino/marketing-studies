SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS post_translations;
DROP TABLE IF EXISTS newsletter_campaigns;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS authors;
DROP TABLE IF EXISTS lab_resources;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at VARCHAR(32) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE authors (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE posts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    excerpt TEXT NOT NULL,
    content_markdown MEDIUMTEXT NOT NULL,
    content_html MEDIUMTEXT NOT NULL,
    cover_image TEXT,
    seo_title VARCHAR(255),
    seo_description TEXT,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    author_name VARCHAR(191) NOT NULL,
    author_id INT UNSIGNED NULL,
    category_id INT UNSIGNED NULL,
    read_time INT NOT NULL DEFAULT 1,
    created_at VARCHAR(32) NOT NULL,
    updated_at VARCHAR(32) NOT NULL,
    published_at VARCHAR(32) NULL,
    newsletter_sent_at VARCHAR(32) NULL,
    PRIMARY KEY (id),
    KEY idx_posts_status_published (status, published_at, updated_at),
    KEY idx_posts_author_id (author_id),
    KEY idx_posts_category_id (category_id),
    CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL,
    CONSTRAINT fk_posts_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE post_translations (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id INT UNSIGNED NOT NULL,
    author_name VARCHAR(191) NOT NULL,
    author_email VARCHAR(191) NOT NULL,
    author_website TEXT,
    body TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'spam') NOT NULL DEFAULT 'pending',
    ip_address VARCHAR(64),
    user_agent TEXT,
    is_recaptcha_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at VARCHAR(32) NOT NULL,
    updated_at VARCHAR(32) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_comments_post_status (post_id, status, created_at),
    KEY idx_comments_status_created (status, created_at),
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE newsletter_subscribers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(191) NOT NULL UNIQUE,
    locale VARCHAR(8) NOT NULL DEFAULT 'en',
    source VARCHAR(64) DEFAULT 'home',
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    unsubscribe_token VARCHAR(64) NULL,
    unsubscribed_at VARCHAR(32) NULL,
    last_sent_at VARCHAR(32) NULL,
    created_at VARCHAR(32) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_newsletter_subscribers_status (status),
    KEY idx_newsletter_subscribers_token (unsubscribe_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE newsletter_campaigns (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL,
    topic VARCHAR(255),
    message TEXT NOT NULL,
    locale VARCHAR(8) NOT NULL DEFAULT 'en',
    status VARCHAR(32) NOT NULL DEFAULT 'new',
    created_at VARCHAR(32) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_resources (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    resource_type VARCHAR(80) NOT NULL DEFAULT 'template',
    download_url TEXT NOT NULL,
    cover_image TEXT,
    author_name VARCHAR(191),
    source_name VARCHAR(191),
    source_url TEXT,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    download_count INT NOT NULL DEFAULT 0,
    created_at VARCHAR(32) NOT NULL,
    updated_at VARCHAR(32) NOT NULL,
    published_at VARCHAR(32) NULL,
    PRIMARY KEY (id),
    KEY idx_lab_resources_status (status, published_at, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    `key` VARCHAR(191) NOT NULL,
    value MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
