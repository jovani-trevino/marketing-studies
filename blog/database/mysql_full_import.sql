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

SET FOREIGN_KEY_CHECKS = 0;

LOCK TABLES `users` WRITE;
INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`) VALUES (1, 'admin', 'scrypt:32768:8:1$DDCRqn6xWLFh2JEz$6b4d80d5130f2acf11822c17ca89e9ca41d1133dec3cdd86ee8c4c81d3538f880954766eaa82e1e25263ef4c04719c4154cf6687faefe037d17fb583864c1a7c', '2026-06-02T19:58:04');
UNLOCK TABLES;

LOCK TABLES `settings` WRITE;
INSERT INTO `settings` (`key`, `value`) VALUES ('hero_title', 'Marketing que convierte, no solo impresiona.');
INSERT INTO `settings` (`key`, `value`) VALUES ('hero_description', 'Un blog para documentar estrategias de SEO, paid media, embudos y experimentacion con enfoque en resultados.');
INSERT INTO `settings` (`key`, `value`) VALUES ('about_blurb', 'Comparte aprendizajes, estudios de caso y tacticas accionables para founders, marketers y equipos de growth.');
INSERT INTO `settings` (`key`, `value`) VALUES ('adsense_script', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('ad_home_top', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('ad_sidebar', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('ad_article_inline', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('ad_footer', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('comments_intro', 'Comparte tu punto de vista, deja tu rating y conversemos sobre growth, SEO y performance.');
INSERT INTO `settings` (`key`, `value`) VALUES ('social_section_title', 'Sigue el laboratorio de marketing');
INSERT INTO `settings` (`key`, `value`) VALUES ('social_section_description', 'Encuentra nuevos experimentos, breakdowns y aprendizajes en nuestras redes.');
INSERT INTO `settings` (`key`, `value`) VALUES ('social_x_url', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('social_linkedin_url', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('social_instagram_url', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('social_youtube_url', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('social_tiktok_url', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('contact_email', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('cookie_consent_enabled', '1');
INSERT INTO `settings` (`key`, `value`) VALUES ('cookie_consent_title', 'Usamos cookies para mejorar la experiencia');
INSERT INTO `settings` (`key`, `value`) VALUES ('cookie_consent_message', 'Utilizamos cookies funcionales y de medicion para mejorar el blog, mostrar publicidad y entender que contenido genera mas valor.');
INSERT INTO `settings` (`key`, `value`) VALUES ('cookie_policy_url', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('comments_moderation_enabled', '1');
INSERT INTO `settings` (`key`, `value`) VALUES ('recaptcha_enabled', '0');
INSERT INTO `settings` (`key`, `value`) VALUES ('recaptcha_site_key', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('recaptcha_secret_key', '');
INSERT INTO `settings` (`key`, `value`) VALUES ('hero_background_image', 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1800&q=82');
INSERT INTO `settings` (`key`, `value`) VALUES ('site_title', 'Marketing Studies');
INSERT INTO `settings` (`key`, `value`) VALUES ('logo_url', '/static/brand/marketing-studies-logo.png');
INSERT INTO `settings` (`key`, `value`) VALUES ('site_share_image', '/static/brand/marketing-studies-logo.png');
INSERT INTO `settings` (`key`, `value`) VALUES ('site_tagline', 'Research, strategy and growth for sharper marketing decisions.');
INSERT INTO `settings` (`key`, `value`) VALUES ('author_name', 'Jovani');
UNLOCK TABLES;

LOCK TABLES `categories` WRITE;
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (1, 'SEO & Contenido', 'seo-contenido', 'Clusters, topical authority y contenido con intencion comercial para capturar demanda organica.', '#C9853B', 'bi bi-search-heart-fill', 'Domina SERPs y conviertelas en pipeline, no solo en trafico.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (2, 'Paid Media', 'paid-media', 'Google Ads, Meta Ads y remarketing con obsesion por eficiencia, escala y margen.', '#0C3D69', 'bi bi-badge-ad-fill', 'Campanas que dejan aprendizaje y caja, no solo impresiones.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (5, 'Email & CRM', 'email-crm', 'Automatizaciones, nurture y retention loops para exprimir mejor cada lead y cliente.', '#B87835', 'bi bi-envelope-paper-heart-fill', 'La relacion con tu audiencia tambien puede convertirse en revenue predecible.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (6, 'Analytics & Attribution', 'analytics-attribution', 'Tracking, dashboards y lectura de señal para decidir con menos humo y mejor timing.', '#1D5A7D', 'bi bi-pie-chart-fill', 'Medir bien es lo que separa intuicion bonita de ventaja operativa.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (8, 'Growth & CRO', 'growth-cro', 'Experimentos, landings y optimizacion de conversion para destrabar crecimiento real.', '#E0A35A', 'bi bi-graph-up-arrow', 'Cada cambio necesita hipotesis, señal y una forma clara de ganar.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (9, 'Marketing', 'marketing', 'Estrategias de adquisicion, contenido, paid media y growth para construir demanda rentable.', '#C9853B', 'bi bi-bullseye', 'Ideas claras para convertir atencion en pipeline y clientes.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (10, 'Finanzas', 'finanzas', 'Lecturas sobre finanzas para founders, presupuestos, margen, inversion y toma de decisiones.', '#0C3D69', 'bi bi-bank', 'Numeros entendibles para crecer sin perder control.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (11, 'Diseño Gráfico', 'diseno-grafico', 'Identidad visual, creatividad, branding y diseno aplicado a comunicacion de negocios.', '#B87835', 'bi bi-vector-pen', 'Diseno que comunica estrategia, no solo estilo.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (12, 'Tecnología', 'tecnologia', 'Herramientas, automatizacion, software, inteligencia artificial y stacks para operar mejor.', '#123F63', 'bi bi-command', 'Tecnologia practica para hacer mas con menos friccion.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (13, 'Negocios', 'negocios', 'Modelos de negocio, estrategia, ventas, operaciones y crecimiento empresarial.', '#DCA157', 'bi bi-building-up', 'Estrategia para construir empresas mas claras, fuertes y rentables.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (14, 'Analítica', 'analitica', 'Dashboards, medicion, reporting, atribucion y lectura de datos para decidir mejor.', '#1D5A7D', 'bi bi-activity', 'Datos que ayudan a decidir, no reportes que solo decoran reuniones.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (15, 'Casos de Estudio', 'casos-de-estudio', 'Breakdowns de campañas, marcas, decisiones y resultados reales para aprender con contexto.', '#8B5B2F', 'bi bi-layout-text-window-reverse', 'Aprendizajes concretos a partir de ejemplos reales.');
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `accent_color`, `icon_class`, `spotlight`) VALUES (16, 'Lab', 'lab', 'Plantillas, checklists y recursos descargables para ejecutar mejor.', '#E0A35A', 'bi bi-flask', 'Material util para convertir ideas en ejecucion.');
UNLOCK TABLES;

LOCK TABLES `authors` WRITE;
INSERT INTO `authors` (`id`, `name`, `slug`, `role`, `bio`, `avatar_url`, `linkedin_url`, `x_url`, `website_url`, `status`, `created_at`, `updated_at`) VALUES (1, 'Jovani', 'jovani', 'Editor', 'Actionable learnings, case studies and tactics for founders, marketers and growth teams.', '/static/brand/jovani-trevino.jpg', '', '', '', 'active', '2026-06-05T13:44:09-06:00', '2026-06-05T13:44:09-06:00');
UNLOCK TABLES;

LOCK TABLES `posts` WRITE;
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (1, 'Como crear un funnel de contenido que atraiga leads de alta intencion', 'funnel-de-contenido-leads-alta-intencion', 'Una estructura simple para conectar SEO, lead magnets y nurtures sin depender solo de trafico frio.', '## El problema real

Muchos blogs publican bastante, pero no conectan el contenido con una oferta clara. Resultado: visitas sin conversion.

## La estructura que si funciona

1. **Contenido TOFU** para capturar preguntas y dolores.
2. **Lead magnet MOFU** con checklist, plantilla o benchmark.
3. **Email sequence BOFU** con prueba social, caso de uso y CTA.

## Que medir cada semana

- CTR organico por cluster
- Conversion a lead por landing
- Open rate por secuencia
- SQLs generados por tema

## Recomendacion final

No publiques mas por publicar. Diseña clusters con una intencion comercial clara desde el brief.
', '<h2>El problema real</h2>
<p>Muchos blogs publican bastante, pero no conectan el contenido con una oferta clara. Resultado: visitas sin conversion.</p>
<h2>La estructura que si funciona</h2>
<ol>
<li><strong>Contenido TOFU</strong> para capturar preguntas y dolores.</li>
<li><strong>Lead magnet MOFU</strong> con checklist, plantilla o benchmark.</li>
<li><strong>Email sequence BOFU</strong> con prueba social, caso de uso y CTA.</li>
</ol>
<h2>Que medir cada semana</h2>
<ul>
<li>CTR organico por cluster</li>
<li>Conversion a lead por landing</li>
<li>Open rate por secuencia</li>
<li>SQLs generados por tema</li>
</ul>
<h2>Recomendacion final</h2>
<p>No publiques mas por publicar. Diseña clusters con una intencion comercial clara desde el brief.</p>', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&q=80', 'Funnel de contenido para generar leads de alta intencion', 'Aprende a conectar SEO, lead magnets y email nurture para generar leads con mayor probabilidad de compra.', 'published', 0, 'Jovani', 9, 1, '2026-06-02T19:58:04', '2026-06-02T19:58:04', '2026-06-02T19:58:04', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (2, '7 ajustes rapidos para mejorar el ROAS de una cuenta de Google Ads', 'ajustes-rapidos-mejorar-roas-google-ads', 'Pequenos cambios en estructura, busquedas y creatividades que suelen liberar eficiencia en pocas semanas.', '## Empieza por el search term report

Antes de tocar budgets, revisa donde se fuga la intencion.

## Ajustes clave

1. Negativas cruzadas entre campañas.
2. Separar marca vs no marca.
3. Reforzar assets con social proof.
4. Ajustar landing por keyword cluster.
5. Excluir audiencias de baja calidad.
6. Revisar horarios y dispositivos.
7. Subir pujas solo donde existe margen real.

## Que no hacer

No aumentes presupuesto en una campaña con tracking roto o con mensajes genericos en la landing.
', '<h2>Empieza por el search term report</h2>
<p>Antes de tocar budgets, revisa donde se fuga la intencion.</p>
<h2>Ajustes clave</h2>
<ol>
<li>Negativas cruzadas entre campañas.</li>
<li>Separar marca vs no marca.</li>
<li>Reforzar assets con social proof.</li>
<li>Ajustar landing por keyword cluster.</li>
<li>Excluir audiencias de baja calidad.</li>
<li>Revisar horarios y dispositivos.</li>
<li>Subir pujas solo donde existe margen real.</li>
</ol>
<h2>Que no hacer</h2>
<p>No aumentes presupuesto en una campaña con tracking roto o con mensajes genericos en la landing.</p>', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1400&q=80', 'Mejora el ROAS en Google Ads con estos 7 ajustes', 'Checklist practica para optimizar Google Ads y detectar mejoras rapidas en ROAS y conversion rate.', 'published', 0, 'Jovani', 9, 1, '2026-06-02T19:58:04', '2026-06-02T19:58:04', '2026-06-02T19:58:04', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (3, 'Framework express para auditar una landing y subir conversiones', 'framework-auditar-landing-subir-conversiones', 'Un metodo rapido para revisar propuesta de valor, jerarquia visual, prueba social y friccion.', '## La auditoria en 20 minutos

Haz cuatro preguntas:

1. ¿Se entiende la oferta en 5 segundos?
2. ¿Existe una sola accion principal?
3. ¿Hay prueba social visible arriba del fold?
4. ¿La pagina reduce objeciones reales?

## Palancas de mejora

- Headline especifico
- CTA con beneficio
- Capturas o demo concreta
- FAQs enfocadas en objeciones
- Formulario mas corto

## Consejo de growth

Si no puedes explicar por que una variante deberia ganar, todavia no tienes una hipotesis, solo decoracion.
', '<h2>La auditoria en 20 minutos</h2>
<p>Haz cuatro preguntas:</p>
<ol>
<li>¿Se entiende la oferta en 5 segundos?</li>
<li>¿Existe una sola accion principal?</li>
<li>¿Hay prueba social visible arriba del fold?</li>
<li>¿La pagina reduce objeciones reales?</li>
</ol>
<h2>Palancas de mejora</h2>
<ul>
<li>Headline especifico</li>
<li>CTA con beneficio</li>
<li>Capturas o demo concreta</li>
<li>FAQs enfocadas en objeciones</li>
<li>Formulario mas corto</li>
</ul>
<h2>Consejo de growth</h2>
<p>Si no puedes explicar por que una variante deberia ganar, todavia no tienes una hipotesis, solo decoracion.</p>', 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1400&q=80', 'Como auditar una landing page para mejorar conversiones', 'Usa este framework para detectar fricciones y optimizar la tasa de conversion de tus landings.', 'published', 0, 'Jovani', NULL, 1, '2026-06-02T19:58:04', '2026-06-02T19:58:04', '2026-06-02T19:58:04', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (5, 'test', 'test', 'test', 'tesst', '<p>tesst</p>', '', 'sdd', 'sd', 'published', 1, 'Jovani', NULL, 1, '2026-06-02T20:05:32', '2026-06-02T20:05:32', '2026-06-02T20:05:32', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (6, 'Secuencias de email que activan leads sin sonar a automatizacion barata', 'secuencias-email-activan-leads', 'Un guion para nutrir MQLs con timing, contexto y llamados a la accion que si se sienten humanos.', '## El error clasico

Muchos flujos de email hablan demasiado pronto de demo y demasiado poco del problema que vive la audiencia.

## Una secuencia util

1. Email de contexto y pain framing.
2. Email con benchmark o framework descargable.
3. Email con caso real y aprendizaje.
4. Email con CTA contextual a llamada o demo.

## Que debes medir

- Tiempo a primera respuesta
- Clicks por tipo de CTA
- Conversion a reunion
- Unsubscribes por secuencia
', '<h2>El error clasico</h2>
<p>Muchos flujos de email hablan demasiado pronto de demo y demasiado poco del problema que vive la audiencia.</p>
<h2>Una secuencia util</h2>
<ol>
<li>Email de contexto y pain framing.</li>
<li>Email con benchmark o framework descargable.</li>
<li>Email con caso real y aprendizaje.</li>
<li>Email con CTA contextual a llamada o demo.</li>
</ol>
<h2>Que debes medir</h2>
<ul>
<li>Tiempo a primera respuesta</li>
<li>Clicks por tipo de CTA</li>
<li>Conversion a reunion</li>
<li>Unsubscribes por secuencia</li>
</ul>', 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1400&q=80', 'Secuencias de email para activar leads con mejor conversion', 'Aprende a nutrir leads con emails mas utiles y menos roboticos para aumentar respuestas y reuniones.', 'published', 0, 'Jovani', 12, 1, '2026-06-02T20:10:21', '2026-06-02T20:10:21', '2026-06-02T20:10:21', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (7, 'Como detectar cuando tu atribucion esta mintiendo y te esta costando presupuesto', 'atribucion-mintiendo-presupuesto', 'Senales para encontrar tracking roto, inflado de conversiones y dashboards que te empujan a malas decisiones.', '## Cuando los numeros se ven demasiado bonitos

Si todas las campañas aparecen como ganadoras, probablemente no estas midiendo bien el viaje completo.

## Donde revisar primero

1. Duplicados de eventos.
2. Inconsistencias entre CRM y plataforma.
3. Ventanas de atribucion muy agresivas.
4. Conversiones sin valor de negocio real.

## Lo importante

El mejor dashboard no es el mas vistoso: es el que te ayuda a cortar presupuesto mal asignado con rapidez.
', '<h2>Cuando los numeros se ven demasiado bonitos</h2>
<p>Si todas las campañas aparecen como ganadoras, probablemente no estas midiendo bien el viaje completo.</p>
<h2>Donde revisar primero</h2>
<ol>
<li>Duplicados de eventos.</li>
<li>Inconsistencias entre CRM y plataforma.</li>
<li>Ventanas de atribucion muy agresivas.</li>
<li>Conversiones sin valor de negocio real.</li>
</ol>
<h2>Lo importante</h2>
<p>El mejor dashboard no es el mas vistoso: es el que te ayuda a cortar presupuesto mal asignado con rapidez.</p>', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1400&q=80', 'Senales de que tu atribucion de marketing esta fallando', 'Detecta errores de tracking y dashboards engañosos antes de que destruyan tu presupuesto de marketing.', 'published', 0, 'Jovani', 14, 1, '2026-06-02T20:10:21', '2026-06-02T20:10:21', '2026-06-02T20:10:21', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (8, 'Checklist de SEO tecnico para acelerar indexacion sin tocar veinte plugins', 'checklist-seo-tecnico-indexacion', 'Una lista practica para revisar rastreo, enlazado interno, velocidad y señales de indexabilidad.', '## Antes de publicar mas

Revisa si Google esta entendiendo de verdad tu sitio.

## Checklist rapido

1. Sitemap limpio y actualizado.
2. Robots sin bloqueos accidentales.
3. Canonicals correctos.
4. Paginacion enlazada con sentido.
5. Core Web Vitals en plantillas clave.

## Que destraba mas rapido

El mejor combo suele ser arquitectura interna, enlazado contextual y limpieza de paginas debiles.
', '<h2>Antes de publicar mas</h2>
<p>Revisa si Google esta entendiendo de verdad tu sitio.</p>
<h2>Checklist rapido</h2>
<ol>
<li>Sitemap limpio y actualizado.</li>
<li>Robots sin bloqueos accidentales.</li>
<li>Canonicals correctos.</li>
<li>Paginacion enlazada con sentido.</li>
<li>Core Web Vitals en plantillas clave.</li>
</ol>
<h2>Que destraba mas rapido</h2>
<p>El mejor combo suele ser arquitectura interna, enlazado contextual y limpieza de paginas debiles.</p>', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1400&q=80', 'Checklist de SEO tecnico para mejorar indexacion', 'Audita rastreo, canonicals, enlazado interno y velocidad con este checklist de SEO tecnico.', 'published', 0, 'Jovani', 9, 1, '2026-06-02T20:41:16', '2026-06-02T20:41:16', '2026-06-02T20:41:16', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (9, 'Arquitectura de campañas para escalar Google Ads sin perder control', 'arquitectura-campanas-google-ads-control', 'Como separar intencion, creatividad y landings para subir presupuesto sin disparar el caos operativo.', '## El riesgo de escalar rapido

Cuando la estructura esta mal, mas presupuesto solo acelera el desperdicio.

## Una arquitectura util

1. Marca separada.
2. Intencion alta por cluster.
3. Remarketing por profundidad de visita.
4. Landings especificas para cada oferta.

## Lo que hay que vigilar

Consulta de busqueda, QS por cluster y tiempo de carga real por landing.
', '<h2>El riesgo de escalar rapido</h2>
<p>Cuando la estructura esta mal, mas presupuesto solo acelera el desperdicio.</p>
<h2>Una arquitectura util</h2>
<ol>
<li>Marca separada.</li>
<li>Intencion alta por cluster.</li>
<li>Remarketing por profundidad de visita.</li>
<li>Landings especificas para cada oferta.</li>
</ol>
<h2>Lo que hay que vigilar</h2>
<p>Consulta de busqueda, QS por cluster y tiempo de carga real por landing.</p>', 'https://images.unsplash.com/photo-1518186233392-c232efbf2373?auto=format&fit=crop&w=1400&q=80', 'Arquitectura de campañas Google Ads para escalar con control', 'Aprende a estructurar campañas de Google Ads para crecer sin perder visibilidad ni eficiencia.', 'published', 0, 'Jovani', 9, 1, '2026-06-02T20:41:16', '2026-06-02T20:41:16', '2026-06-02T20:41:16', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (10, 'Mapa de experimentos CRO para equipos que ya no quieren probar por intuicion', 'mapa-experimentos-cro-equipos', 'Un sistema para priorizar hipotesis de conversion con evidencia, impacto esperado y velocidad de aprendizaje.', '## El problema

Probar cosas sueltas se siente productivo, pero rara vez construye una ventaja repetible.

## Crea un backlog vivo

1. Hipotesis con insight.
2. Evidencia previa.
3. Esfuerzo de implementacion.
4. KPI primario.

## Resultado

Menos ocurrencias y mas aprendizaje acumulado en el tiempo.
', '<h2>El problema</h2>
<p>Probar cosas sueltas se siente productivo, pero rara vez construye una ventaja repetible.</p>
<h2>Crea un backlog vivo</h2>
<ol>
<li>Hipotesis con insight.</li>
<li>Evidencia previa.</li>
<li>Esfuerzo de implementacion.</li>
<li>KPI primario.</li>
</ol>
<h2>Resultado</h2>
<p>Menos ocurrencias y mas aprendizaje acumulado en el tiempo.</p>', 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1400&q=80', 'Como priorizar experimentos CRO con mejor criterio', 'Diseña un backlog de experimentos CRO con mejor señal, menos intuicion y mayor aprendizaje.', 'published', 0, 'Jovani', 13, 1, '2026-06-02T20:41:16', '2026-06-02T20:41:16', '2026-06-02T20:41:16', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (11, 'Nurture loops con CRM: de lead frio a oportunidad con menos friccion', 'nurture-loops-crm-oportunidad', 'Un marco para conectar scoring, automatizacion y contenido util sin empujar demasiado pronto la venta.', '## Nurture no es solo mandar correos

Un buen loop sincroniza comportamiento, contexto y timing comercial.

## Componentes clave

1. Eventos de comportamiento confiables.
2. Segmentos actualizados.
3. Automatizaciones segun intencion.
4. CTA segun etapa.

## El objetivo

Mover conversaciones con mas contexto y menos resistencia.
', '<h2>Nurture no es solo mandar correos</h2>
<p>Un buen loop sincroniza comportamiento, contexto y timing comercial.</p>
<h2>Componentes clave</h2>
<ol>
<li>Eventos de comportamiento confiables.</li>
<li>Segmentos actualizados.</li>
<li>Automatizaciones segun intencion.</li>
<li>CTA segun etapa.</li>
</ol>
<h2>El objetivo</h2>
<p>Mover conversaciones con mas contexto y menos resistencia.</p>', 'https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=1400&q=80', 'Nurture loops en CRM para mover leads a oportunidad', 'Conecta scoring, automatizacion y contenido para convertir leads frios en oportunidades reales.', 'published', 0, 'Jovani', 12, 1, '2026-06-02T20:41:16', '2026-06-02T20:41:16', '2026-06-02T20:41:16', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (12, 'Dashboard de atribucion para founders: que metricas si ver y cuales ignorar', 'dashboard-atribucion-founders-metricas', 'Un dashboard compacto para no perderte en vanity metrics cuando necesitas decidir presupuesto con rapidez.', '## Menos widgets, mejor lectura

Si tu dashboard parece cockpit de avion, probablemente nadie lo usa de verdad.

## Metrica que importa

1. CAC por canal.
2. Conversion por etapa.
3. Tiempo a revenue.
4. Calidad de pipeline por fuente.

## Conclusión

El mejor dashboard es el que ayuda a decir no mas rapido.
', '<h2>Menos widgets, mejor lectura</h2>
<p>Si tu dashboard parece cockpit de avion, probablemente nadie lo usa de verdad.</p>
<h2>Metrica que importa</h2>
<ol>
<li>CAC por canal.</li>
<li>Conversion por etapa.</li>
<li>Tiempo a revenue.</li>
<li>Calidad de pipeline por fuente.</li>
</ol>
<h2>Conclusión</h2>
<p>El mejor dashboard es el que ayuda a decir no mas rapido.</p>', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&q=80', 'Dashboard de atribucion para founders y equipos de growth', 'Que metricas revisar en un dashboard de atribucion para decidir mejor presupuesto y prioridades.', 'published', 0, 'Jovani', 14, 1, '2026-06-02T20:41:16', '2026-06-02T20:41:16', '2026-06-02T20:41:16', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (13, 'Caso de estudio: ecommerce B2B que redujo CAC con contenido comparativo', 'caso-ecommerce-b2b-cac-contenido-comparativo', 'Breakdown de como una estrategia de comparativas, landings por intencion y remarketing redujo CAC sin subir presupuesto.', '## Contexto

Un ecommerce B2B tenia buen trafico organico, pero la mayoria de visitantes llegaba a contenidos informativos con baja conversion.

## Reto

El equipo necesitaba capturar compradores en etapa de decision sin depender de mas gasto en pauta.

## Estrategia

1. Mapear keywords comparativas y consultas de decision.
2. Crear landings por categoria con tabla de criterios.
3. Conectar articulos top-of-funnel con CTAs mas especificos.
4. Activar remarketing solo para usuarios con señales de compra.

## Resultados

- CAC 28% menor en 60 dias.
- Conversion a lead 19% mas alta en paginas comparativas.
- Mejor lectura de intencion para el equipo comercial.

## Aprendizaje

No todo contenido debe buscar volumen. Algunas piezas ganan por estar cerca de una decision real.
', '<h2>Contexto</h2>
<p>Un ecommerce B2B tenia buen trafico organico, pero la mayoria de visitantes llegaba a contenidos informativos con baja conversion.</p>
<h2>Reto</h2>
<p>El equipo necesitaba capturar compradores en etapa de decision sin depender de mas gasto en pauta.</p>
<h2>Estrategia</h2>
<ol>
<li>Mapear keywords comparativas y consultas de decision.</li>
<li>Crear landings por categoria con tabla de criterios.</li>
<li>Conectar articulos top-of-funnel con CTAs mas especificos.</li>
<li>Activar remarketing solo para usuarios con señales de compra.</li>
</ol>
<h2>Resultados</h2>
<ul>
<li>CAC 28% menor en 60 dias.</li>
<li>Conversion a lead 19% mas alta en paginas comparativas.</li>
<li>Mejor lectura de intencion para el equipo comercial.</li>
</ul>
<h2>Aprendizaje</h2>
<p>No todo contenido debe buscar volumen. Algunas piezas ganan por estar cerca de una decision real.</p>', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1400&q=80', 'Caso ecommerce B2B: reducir CAC con contenido comparativo', 'Caso de estudio sobre contenido comparativo, landings por intencion y remarketing para reducir CAC en ecommerce B2B.', 'published', 0, 'Jovani', 15, 1, '2026-06-03T14:15:26', '2026-06-03T14:15:26', '2026-06-03T14:15:26', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (14, 'Caso de estudio: servicio profesional que triplico reuniones con una landing mejor enfocada', 'caso-servicio-profesional-triplico-reuniones-landing', 'Como un rediseño centrado en objeciones, prueba social y CTA contextual ayudo a convertir mas visitas en reuniones.', '## Contexto

Una firma de servicios profesionales recibia trafico calificado, pero el sitio explicaba demasiado y vendia poco.

## Reto

La landing principal no mostraba con claridad para quien era la oferta ni que problema resolvia primero.

## Estrategia

1. Reescribir el headline alrededor del dolor principal.
2. Subir prueba social arriba del fold.
3. Reducir opciones y dejar un CTA dominante.
4. Agregar bloque de preguntas frecuentes con objeciones reales.

## Resultados

- Reuniones comerciales 3x en seis semanas.
- Tiempo de lectura menor sin perder conversion.
- Mejor calidad percibida por el equipo de ventas.

## Aprendizaje

Una landing no necesita decirlo todo. Necesita decir lo correcto en el orden correcto.
', '<h2>Contexto</h2>
<p>Una firma de servicios profesionales recibia trafico calificado, pero el sitio explicaba demasiado y vendia poco.</p>
<h2>Reto</h2>
<p>La landing principal no mostraba con claridad para quien era la oferta ni que problema resolvia primero.</p>
<h2>Estrategia</h2>
<ol>
<li>Reescribir el headline alrededor del dolor principal.</li>
<li>Subir prueba social arriba del fold.</li>
<li>Reducir opciones y dejar un CTA dominante.</li>
<li>Agregar bloque de preguntas frecuentes con objeciones reales.</li>
</ol>
<h2>Resultados</h2>
<ul>
<li>Reuniones comerciales 3x en seis semanas.</li>
<li>Tiempo de lectura menor sin perder conversion.</li>
<li>Mejor calidad percibida por el equipo de ventas.</li>
</ul>
<h2>Aprendizaje</h2>
<p>Una landing no necesita decirlo todo. Necesita decir lo correcto en el orden correcto.</p>', 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=1400&q=80', 'Caso de estudio: triplicar reuniones con una landing enfocada', 'Breakdown de optimizacion de landing para servicios profesionales con foco en objeciones, prueba social y conversion.', 'published', 0, 'Jovani', 15, 1, '2026-06-03T14:15:26', '2026-06-03T14:15:26', '2026-06-03T14:15:26', NULL, 1);
INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `cover_image`, `seo_title`, `seo_description`, `status`, `is_featured`, `author_name`, `category_id`, `read_time`, `created_at`, `updated_at`, `published_at`, `newsletter_sent_at`, `author_id`) VALUES (15, 'Caso de estudio: SaaS que recupero pipeline apagado con nurture loops', 'caso-saas-recupero-pipeline-nurture-loops', 'Un caso sobre scoring, automatizacion y contenido util para reactivar leads dormidos sin saturar la bandeja.', '## Contexto

Un SaaS B2B tenia miles de leads historicos en CRM, pero muy pocas conversaciones nuevas salian de esa base.

## Reto

El equipo necesitaba reactivar pipeline sin parecer spam ni mandar el mismo correo a todos.

## Estrategia

1. Segmentar por problema, industria y comportamiento previo.
2. Crear loops de contenido segun etapa de decision.
3. Enviar CTAs suaves antes de pedir demo.
4. Pasar a ventas solo leads con señales acumuladas.

## Resultados

- 41% mas respuestas utiles en secuencias.
- 17 oportunidades reactivadas en el primer mes.
- Menos friccion entre marketing y ventas.

## Aprendizaje

El nurture funciona mejor cuando parece seguimiento con criterio, no automatizacion masiva.
', '<h2>Contexto</h2>
<p>Un SaaS B2B tenia miles de leads historicos en CRM, pero muy pocas conversaciones nuevas salian de esa base.</p>
<h2>Reto</h2>
<p>El equipo necesitaba reactivar pipeline sin parecer spam ni mandar el mismo correo a todos.</p>
<h2>Estrategia</h2>
<ol>
<li>Segmentar por problema, industria y comportamiento previo.</li>
<li>Crear loops de contenido segun etapa de decision.</li>
<li>Enviar CTAs suaves antes de pedir demo.</li>
<li>Pasar a ventas solo leads con señales acumuladas.</li>
</ol>
<h2>Resultados</h2>
<ul>
<li>41% mas respuestas utiles en secuencias.</li>
<li>17 oportunidades reactivadas en el primer mes.</li>
<li>Menos friccion entre marketing y ventas.</li>
</ul>
<h2>Aprendizaje</h2>
<p>El nurture funciona mejor cuando parece seguimiento con criterio, no automatizacion masiva.</p>', 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1400&q=80', 'Caso SaaS: recuperar pipeline con nurture loops', 'Caso de estudio sobre reactivacion de leads en SaaS B2B con scoring, automatizacion y contenido por etapa.', 'published', 0, 'Jovani', 15, 1, '2026-06-03T14:15:26', '2026-06-03T14:15:26', '2026-06-03T14:15:26', NULL, 1);
UNLOCK TABLES;

LOCK TABLES `post_translations` WRITE;
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (1, 1, 'es', 'Como crear un funnel de contenido que atraiga leads de alta intencion', 'funnel-de-contenido-leads-alta-intencion', 'Una estructura simple para conectar SEO, lead magnets y nurtures sin depender solo de trafico frio.', '## El problema real

Muchos blogs publican bastante, pero no conectan el contenido con una oferta clara. Resultado: visitas sin conversion.

## La estructura que si funciona

1. **Contenido TOFU** para capturar preguntas y dolores.
2. **Lead magnet MOFU** con checklist, plantilla o benchmark.
3. **Email sequence BOFU** con prueba social, caso de uso y CTA.

## Que medir cada semana

- CTR organico por cluster
- Conversion a lead por landing
- Open rate por secuencia
- SQLs generados por tema

## Recomendacion final

No publiques mas por publicar. Diseña clusters con una intencion comercial clara desde el brief.
', '<h2>El problema real</h2>
<p>Muchos blogs publican bastante, pero no conectan el contenido con una oferta clara. Resultado: visitas sin conversion.</p>
<h2>La estructura que si funciona</h2>
<ol>
<li><strong>Contenido TOFU</strong> para capturar preguntas y dolores.</li>
<li><strong>Lead magnet MOFU</strong> con checklist, plantilla o benchmark.</li>
<li><strong>Email sequence BOFU</strong> con prueba social, caso de uso y CTA.</li>
</ol>
<h2>Que medir cada semana</h2>
<ul>
<li>CTR organico por cluster</li>
<li>Conversion a lead por landing</li>
<li>Open rate por secuencia</li>
<li>SQLs generados por tema</li>
</ul>
<h2>Recomendacion final</h2>
<p>No publiques mas por publicar. Diseña clusters con una intencion comercial clara desde el brief.</p>', 'Funnel de contenido para generar leads de alta intencion', 'Aprende a conectar SEO, lead magnets y email nurture para generar leads con mayor probabilidad de compra.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (2, 2, 'es', '7 ajustes rapidos para mejorar el ROAS de una cuenta de Google Ads', 'ajustes-rapidos-mejorar-roas-google-ads', 'Pequenos cambios en estructura, busquedas y creatividades que suelen liberar eficiencia en pocas semanas.', '## Empieza por el search term report

Antes de tocar budgets, revisa donde se fuga la intencion.

## Ajustes clave

1. Negativas cruzadas entre campañas.
2. Separar marca vs no marca.
3. Reforzar assets con social proof.
4. Ajustar landing por keyword cluster.
5. Excluir audiencias de baja calidad.
6. Revisar horarios y dispositivos.
7. Subir pujas solo donde existe margen real.

## Que no hacer

No aumentes presupuesto en una campaña con tracking roto o con mensajes genericos en la landing.
', '<h2>Empieza por el search term report</h2>
<p>Antes de tocar budgets, revisa donde se fuga la intencion.</p>
<h2>Ajustes clave</h2>
<ol>
<li>Negativas cruzadas entre campañas.</li>
<li>Separar marca vs no marca.</li>
<li>Reforzar assets con social proof.</li>
<li>Ajustar landing por keyword cluster.</li>
<li>Excluir audiencias de baja calidad.</li>
<li>Revisar horarios y dispositivos.</li>
<li>Subir pujas solo donde existe margen real.</li>
</ol>
<h2>Que no hacer</h2>
<p>No aumentes presupuesto en una campaña con tracking roto o con mensajes genericos en la landing.</p>', 'Mejora el ROAS en Google Ads con estos 7 ajustes', 'Checklist practica para optimizar Google Ads y detectar mejoras rapidas en ROAS y conversion rate.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (3, 3, 'es', 'Framework express para auditar una landing y subir conversiones', 'framework-auditar-landing-subir-conversiones', 'Un metodo rapido para revisar propuesta de valor, jerarquia visual, prueba social y friccion.', '## La auditoria en 20 minutos

Haz cuatro preguntas:

1. ¿Se entiende la oferta en 5 segundos?
2. ¿Existe una sola accion principal?
3. ¿Hay prueba social visible arriba del fold?
4. ¿La pagina reduce objeciones reales?

## Palancas de mejora

- Headline especifico
- CTA con beneficio
- Capturas o demo concreta
- FAQs enfocadas en objeciones
- Formulario mas corto

## Consejo de growth

Si no puedes explicar por que una variante deberia ganar, todavia no tienes una hipotesis, solo decoracion.
', '<h2>La auditoria en 20 minutos</h2>
<p>Haz cuatro preguntas:</p>
<ol>
<li>¿Se entiende la oferta en 5 segundos?</li>
<li>¿Existe una sola accion principal?</li>
<li>¿Hay prueba social visible arriba del fold?</li>
<li>¿La pagina reduce objeciones reales?</li>
</ol>
<h2>Palancas de mejora</h2>
<ul>
<li>Headline especifico</li>
<li>CTA con beneficio</li>
<li>Capturas o demo concreta</li>
<li>FAQs enfocadas en objeciones</li>
<li>Formulario mas corto</li>
</ul>
<h2>Consejo de growth</h2>
<p>Si no puedes explicar por que una variante deberia ganar, todavia no tienes una hipotesis, solo decoracion.</p>', 'Como auditar una landing page para mejorar conversiones', 'Usa este framework para detectar fricciones y optimizar la tasa de conversion de tus landings.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (4, 5, 'es', 'test', 'test', 'test', 'tesst', '<p>tesst</p>', 'sdd', 'sd', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (5, 6, 'es', 'Secuencias de email que activan leads sin sonar a automatizacion barata', 'secuencias-email-activan-leads', 'Un guion para nutrir MQLs con timing, contexto y llamados a la accion que si se sienten humanos.', '## El error clasico

Muchos flujos de email hablan demasiado pronto de demo y demasiado poco del problema que vive la audiencia.

## Una secuencia util

1. Email de contexto y pain framing.
2. Email con benchmark o framework descargable.
3. Email con caso real y aprendizaje.
4. Email con CTA contextual a llamada o demo.

## Que debes medir

- Tiempo a primera respuesta
- Clicks por tipo de CTA
- Conversion a reunion
- Unsubscribes por secuencia
', '<h2>El error clasico</h2>
<p>Muchos flujos de email hablan demasiado pronto de demo y demasiado poco del problema que vive la audiencia.</p>
<h2>Una secuencia util</h2>
<ol>
<li>Email de contexto y pain framing.</li>
<li>Email con benchmark o framework descargable.</li>
<li>Email con caso real y aprendizaje.</li>
<li>Email con CTA contextual a llamada o demo.</li>
</ol>
<h2>Que debes medir</h2>
<ul>
<li>Tiempo a primera respuesta</li>
<li>Clicks por tipo de CTA</li>
<li>Conversion a reunion</li>
<li>Unsubscribes por secuencia</li>
</ul>', 'Secuencias de email para activar leads con mejor conversion', 'Aprende a nutrir leads con emails mas utiles y menos roboticos para aumentar respuestas y reuniones.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (6, 7, 'es', 'Como detectar cuando tu atribucion esta mintiendo y te esta costando presupuesto', 'atribucion-mintiendo-presupuesto', 'Senales para encontrar tracking roto, inflado de conversiones y dashboards que te empujan a malas decisiones.', '## Cuando los numeros se ven demasiado bonitos

Si todas las campañas aparecen como ganadoras, probablemente no estas midiendo bien el viaje completo.

## Donde revisar primero

1. Duplicados de eventos.
2. Inconsistencias entre CRM y plataforma.
3. Ventanas de atribucion muy agresivas.
4. Conversiones sin valor de negocio real.

## Lo importante

El mejor dashboard no es el mas vistoso: es el que te ayuda a cortar presupuesto mal asignado con rapidez.
', '<h2>Cuando los numeros se ven demasiado bonitos</h2>
<p>Si todas las campañas aparecen como ganadoras, probablemente no estas midiendo bien el viaje completo.</p>
<h2>Donde revisar primero</h2>
<ol>
<li>Duplicados de eventos.</li>
<li>Inconsistencias entre CRM y plataforma.</li>
<li>Ventanas de atribucion muy agresivas.</li>
<li>Conversiones sin valor de negocio real.</li>
</ol>
<h2>Lo importante</h2>
<p>El mejor dashboard no es el mas vistoso: es el que te ayuda a cortar presupuesto mal asignado con rapidez.</p>', 'Senales de que tu atribucion de marketing esta fallando', 'Detecta errores de tracking y dashboards engañosos antes de que destruyan tu presupuesto de marketing.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (7, 8, 'es', 'Checklist de SEO tecnico para acelerar indexacion sin tocar veinte plugins', 'checklist-seo-tecnico-indexacion', 'Una lista practica para revisar rastreo, enlazado interno, velocidad y señales de indexabilidad.', '## Antes de publicar mas

Revisa si Google esta entendiendo de verdad tu sitio.

## Checklist rapido

1. Sitemap limpio y actualizado.
2. Robots sin bloqueos accidentales.
3. Canonicals correctos.
4. Paginacion enlazada con sentido.
5. Core Web Vitals en plantillas clave.

## Que destraba mas rapido

El mejor combo suele ser arquitectura interna, enlazado contextual y limpieza de paginas debiles.
', '<h2>Antes de publicar mas</h2>
<p>Revisa si Google esta entendiendo de verdad tu sitio.</p>
<h2>Checklist rapido</h2>
<ol>
<li>Sitemap limpio y actualizado.</li>
<li>Robots sin bloqueos accidentales.</li>
<li>Canonicals correctos.</li>
<li>Paginacion enlazada con sentido.</li>
<li>Core Web Vitals en plantillas clave.</li>
</ol>
<h2>Que destraba mas rapido</h2>
<p>El mejor combo suele ser arquitectura interna, enlazado contextual y limpieza de paginas debiles.</p>', 'Checklist de SEO tecnico para mejorar indexacion', 'Audita rastreo, canonicals, enlazado interno y velocidad con este checklist de SEO tecnico.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (8, 9, 'es', 'Arquitectura de campañas para escalar Google Ads sin perder control', 'arquitectura-campanas-google-ads-control', 'Como separar intencion, creatividad y landings para subir presupuesto sin disparar el caos operativo.', '## El riesgo de escalar rapido

Cuando la estructura esta mal, mas presupuesto solo acelera el desperdicio.

## Una arquitectura util

1. Marca separada.
2. Intencion alta por cluster.
3. Remarketing por profundidad de visita.
4. Landings especificas para cada oferta.

## Lo que hay que vigilar

Consulta de busqueda, QS por cluster y tiempo de carga real por landing.
', '<h2>El riesgo de escalar rapido</h2>
<p>Cuando la estructura esta mal, mas presupuesto solo acelera el desperdicio.</p>
<h2>Una arquitectura util</h2>
<ol>
<li>Marca separada.</li>
<li>Intencion alta por cluster.</li>
<li>Remarketing por profundidad de visita.</li>
<li>Landings especificas para cada oferta.</li>
</ol>
<h2>Lo que hay que vigilar</h2>
<p>Consulta de busqueda, QS por cluster y tiempo de carga real por landing.</p>', 'Arquitectura de campañas Google Ads para escalar con control', 'Aprende a estructurar campañas de Google Ads para crecer sin perder visibilidad ni eficiencia.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (9, 10, 'es', 'Mapa de experimentos CRO para equipos que ya no quieren probar por intuicion', 'mapa-experimentos-cro-equipos', 'Un sistema para priorizar hipotesis de conversion con evidencia, impacto esperado y velocidad de aprendizaje.', '## El problema

Probar cosas sueltas se siente productivo, pero rara vez construye una ventaja repetible.

## Crea un backlog vivo

1. Hipotesis con insight.
2. Evidencia previa.
3. Esfuerzo de implementacion.
4. KPI primario.

## Resultado

Menos ocurrencias y mas aprendizaje acumulado en el tiempo.
', '<h2>El problema</h2>
<p>Probar cosas sueltas se siente productivo, pero rara vez construye una ventaja repetible.</p>
<h2>Crea un backlog vivo</h2>
<ol>
<li>Hipotesis con insight.</li>
<li>Evidencia previa.</li>
<li>Esfuerzo de implementacion.</li>
<li>KPI primario.</li>
</ol>
<h2>Resultado</h2>
<p>Menos ocurrencias y mas aprendizaje acumulado en el tiempo.</p>', 'Como priorizar experimentos CRO con mejor criterio', 'Diseña un backlog de experimentos CRO con mejor señal, menos intuicion y mayor aprendizaje.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (10, 11, 'es', 'Nurture loops con CRM: de lead frio a oportunidad con menos friccion', 'nurture-loops-crm-oportunidad', 'Un marco para conectar scoring, automatizacion y contenido util sin empujar demasiado pronto la venta.', '## Nurture no es solo mandar correos

Un buen loop sincroniza comportamiento, contexto y timing comercial.

## Componentes clave

1. Eventos de comportamiento confiables.
2. Segmentos actualizados.
3. Automatizaciones segun intencion.
4. CTA segun etapa.

## El objetivo

Mover conversaciones con mas contexto y menos resistencia.
', '<h2>Nurture no es solo mandar correos</h2>
<p>Un buen loop sincroniza comportamiento, contexto y timing comercial.</p>
<h2>Componentes clave</h2>
<ol>
<li>Eventos de comportamiento confiables.</li>
<li>Segmentos actualizados.</li>
<li>Automatizaciones segun intencion.</li>
<li>CTA segun etapa.</li>
</ol>
<h2>El objetivo</h2>
<p>Mover conversaciones con mas contexto y menos resistencia.</p>', 'Nurture loops en CRM para mover leads a oportunidad', 'Conecta scoring, automatizacion y contenido para convertir leads frios en oportunidades reales.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (11, 12, 'es', 'Dashboard de atribucion para founders: que metricas si ver y cuales ignorar', 'dashboard-atribucion-founders-metricas', 'Un dashboard compacto para no perderte en vanity metrics cuando necesitas decidir presupuesto con rapidez.', '## Menos widgets, mejor lectura

Si tu dashboard parece cockpit de avion, probablemente nadie lo usa de verdad.

## Metrica que importa

1. CAC por canal.
2. Conversion por etapa.
3. Tiempo a revenue.
4. Calidad de pipeline por fuente.

## Conclusión

El mejor dashboard es el que ayuda a decir no mas rapido.
', '<h2>Menos widgets, mejor lectura</h2>
<p>Si tu dashboard parece cockpit de avion, probablemente nadie lo usa de verdad.</p>
<h2>Metrica que importa</h2>
<ol>
<li>CAC por canal.</li>
<li>Conversion por etapa.</li>
<li>Tiempo a revenue.</li>
<li>Calidad de pipeline por fuente.</li>
</ol>
<h2>Conclusión</h2>
<p>El mejor dashboard es el que ayuda a decir no mas rapido.</p>', 'Dashboard de atribucion para founders y equipos de growth', 'Que metricas revisar en un dashboard de atribucion para decidir mejor presupuesto y prioridades.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (12, 13, 'es', 'Caso de estudio: ecommerce B2B que redujo CAC con contenido comparativo', 'caso-ecommerce-b2b-cac-contenido-comparativo', 'Breakdown de como una estrategia de comparativas, landings por intencion y remarketing redujo CAC sin subir presupuesto.', '## Contexto

Un ecommerce B2B tenia buen trafico organico, pero la mayoria de visitantes llegaba a contenidos informativos con baja conversion.

## Reto

El equipo necesitaba capturar compradores en etapa de decision sin depender de mas gasto en pauta.

## Estrategia

1. Mapear keywords comparativas y consultas de decision.
2. Crear landings por categoria con tabla de criterios.
3. Conectar articulos top-of-funnel con CTAs mas especificos.
4. Activar remarketing solo para usuarios con señales de compra.

## Resultados

- CAC 28% menor en 60 dias.
- Conversion a lead 19% mas alta en paginas comparativas.
- Mejor lectura de intencion para el equipo comercial.

## Aprendizaje

No todo contenido debe buscar volumen. Algunas piezas ganan por estar cerca de una decision real.
', '<h2>Contexto</h2>
<p>Un ecommerce B2B tenia buen trafico organico, pero la mayoria de visitantes llegaba a contenidos informativos con baja conversion.</p>
<h2>Reto</h2>
<p>El equipo necesitaba capturar compradores en etapa de decision sin depender de mas gasto en pauta.</p>
<h2>Estrategia</h2>
<ol>
<li>Mapear keywords comparativas y consultas de decision.</li>
<li>Crear landings por categoria con tabla de criterios.</li>
<li>Conectar articulos top-of-funnel con CTAs mas especificos.</li>
<li>Activar remarketing solo para usuarios con señales de compra.</li>
</ol>
<h2>Resultados</h2>
<ul>
<li>CAC 28% menor en 60 dias.</li>
<li>Conversion a lead 19% mas alta en paginas comparativas.</li>
<li>Mejor lectura de intencion para el equipo comercial.</li>
</ul>
<h2>Aprendizaje</h2>
<p>No todo contenido debe buscar volumen. Algunas piezas ganan por estar cerca de una decision real.</p>', 'Caso ecommerce B2B: reducir CAC con contenido comparativo', 'Caso de estudio sobre contenido comparativo, landings por intencion y remarketing para reducir CAC en ecommerce B2B.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (13, 14, 'es', 'Caso de estudio: servicio profesional que triplico reuniones con una landing mejor enfocada', 'caso-servicio-profesional-triplico-reuniones-landing', 'Como un rediseño centrado en objeciones, prueba social y CTA contextual ayudo a convertir mas visitas en reuniones.', '## Contexto

Una firma de servicios profesionales recibia trafico calificado, pero el sitio explicaba demasiado y vendia poco.

## Reto

La landing principal no mostraba con claridad para quien era la oferta ni que problema resolvia primero.

## Estrategia

1. Reescribir el headline alrededor del dolor principal.
2. Subir prueba social arriba del fold.
3. Reducir opciones y dejar un CTA dominante.
4. Agregar bloque de preguntas frecuentes con objeciones reales.

## Resultados

- Reuniones comerciales 3x en seis semanas.
- Tiempo de lectura menor sin perder conversion.
- Mejor calidad percibida por el equipo de ventas.

## Aprendizaje

Una landing no necesita decirlo todo. Necesita decir lo correcto en el orden correcto.
', '<h2>Contexto</h2>
<p>Una firma de servicios profesionales recibia trafico calificado, pero el sitio explicaba demasiado y vendia poco.</p>
<h2>Reto</h2>
<p>La landing principal no mostraba con claridad para quien era la oferta ni que problema resolvia primero.</p>
<h2>Estrategia</h2>
<ol>
<li>Reescribir el headline alrededor del dolor principal.</li>
<li>Subir prueba social arriba del fold.</li>
<li>Reducir opciones y dejar un CTA dominante.</li>
<li>Agregar bloque de preguntas frecuentes con objeciones reales.</li>
</ol>
<h2>Resultados</h2>
<ul>
<li>Reuniones comerciales 3x en seis semanas.</li>
<li>Tiempo de lectura menor sin perder conversion.</li>
<li>Mejor calidad percibida por el equipo de ventas.</li>
</ul>
<h2>Aprendizaje</h2>
<p>Una landing no necesita decirlo todo. Necesita decir lo correcto en el orden correcto.</p>', 'Caso de estudio: triplicar reuniones con una landing enfocada', 'Breakdown de optimizacion de landing para servicios profesionales con foco en objeciones, prueba social y conversion.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (14, 15, 'es', 'Caso de estudio: SaaS que recupero pipeline apagado con nurture loops', 'caso-saas-recupero-pipeline-nurture-loops', 'Un caso sobre scoring, automatizacion y contenido util para reactivar leads dormidos sin saturar la bandeja.', '## Contexto

Un SaaS B2B tenia miles de leads historicos en CRM, pero muy pocas conversaciones nuevas salian de esa base.

## Reto

El equipo necesitaba reactivar pipeline sin parecer spam ni mandar el mismo correo a todos.

## Estrategia

1. Segmentar por problema, industria y comportamiento previo.
2. Crear loops de contenido segun etapa de decision.
3. Enviar CTAs suaves antes de pedir demo.
4. Pasar a ventas solo leads con señales acumuladas.

## Resultados

- 41% mas respuestas utiles en secuencias.
- 17 oportunidades reactivadas en el primer mes.
- Menos friccion entre marketing y ventas.

## Aprendizaje

El nurture funciona mejor cuando parece seguimiento con criterio, no automatizacion masiva.
', '<h2>Contexto</h2>
<p>Un SaaS B2B tenia miles de leads historicos en CRM, pero muy pocas conversaciones nuevas salian de esa base.</p>
<h2>Reto</h2>
<p>El equipo necesitaba reactivar pipeline sin parecer spam ni mandar el mismo correo a todos.</p>
<h2>Estrategia</h2>
<ol>
<li>Segmentar por problema, industria y comportamiento previo.</li>
<li>Crear loops de contenido segun etapa de decision.</li>
<li>Enviar CTAs suaves antes de pedir demo.</li>
<li>Pasar a ventas solo leads con señales acumuladas.</li>
</ol>
<h2>Resultados</h2>
<ul>
<li>41% mas respuestas utiles en secuencias.</li>
<li>17 oportunidades reactivadas en el primer mes.</li>
<li>Menos friccion entre marketing y ventas.</li>
</ul>
<h2>Aprendizaje</h2>
<p>El nurture funciona mejor cuando parece seguimiento con criterio, no automatizacion masiva.</p>', 'Caso SaaS: recuperar pipeline con nurture loops', 'Caso de estudio sobre reactivacion de leads en SaaS B2B con scoring, automatizacion y contenido por etapa.', '2026-06-05T11:07:34-06:00', '2026-06-05T11:07:34-06:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (15, 1, 'en', 'How to build a content funnel that attracts high-intent leads', 'content-funnel-high-intent-leads', 'A simple structure to connect SEO, lead magnets and nurture flows without relying only on cold traffic.', '## The real problem

Many blogs publish a lot, but they do not connect content with a clear offer. The result is traffic without conversion.

## A funnel that works

1. Map the audience problem.
2. Publish a practical guide around the search intent.
3. Offer a useful download that continues the topic.
4. Send a nurture sequence that helps the reader decide.
5. Measure which topics become qualified conversations.

## What to improve first

Start with one cluster, one lead magnet and one simple email sequence. Once the path is working, scale the library.', '', 'How to build a content funnel that attracts high-intent leads | Marketing Studies', 'A simple structure to connect SEO, lead magnets and nurture flows without relying only on cold traffic.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (16, 2, 'en', '7 quick fixes to improve Google Ads ROAS', 'google-ads-roas-quick-fixes', 'Small changes in structure, search terms and creative that can unlock efficiency in just a few weeks.', '## Start with the search term report

Before changing budgets, review where intent is leaking.

## Key adjustments

1. Add cross-campaign negatives.
2. Separate brand and non-brand traffic.
3. Reinforce landing pages with social proof.
4. Match landing pages by keyword cluster.
5. Exclude low-quality audiences.
6. Review schedules and devices.
7. Raise bids only where margin exists.

## What not to do

Do not increase spend on campaigns with broken tracking or generic landing page messages.', '', '7 quick fixes to improve Google Ads ROAS | Marketing Studies', 'Small changes in structure, search terms and creative that can unlock efficiency in just a few weeks.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (17, 3, 'en', 'A fast landing page audit framework to lift conversions', 'landing-page-audit-framework-conversions', 'A quick method to review value proposition, visual hierarchy, social proof and friction.', '## The 20-minute audit

Ask four questions before touching design details.

1. Is the offer clear in five seconds?
2. Is there one primary action?
3. Is social proof visible above the fold?
4. Is the form asking only for what is needed?

## The highest-leverage changes

Improve the headline, reduce form friction and move proof closer to the call to action. Small clarity wins usually outperform decorative redesigns.', '', 'A fast landing page audit framework to lift conversions | Marketing Studies', 'A quick method to review value proposition, visual hierarchy, social proof and friction.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (18, 5, 'en', 'Test post', 'test-post', 'Temporary test content for validating the CMS.', '## Test

This is a temporary article used to validate publishing, comments and translations.', '', 'Test post | Marketing Studies', 'Temporary test content for validating the CMS.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (19, 6, 'en', 'Email sequences that activate leads without sounding automated', 'email-sequences-activate-leads', 'A practical sequence for nurturing MQLs with timing, context and calls to action that feel human.', '## The classic mistake

Many email flows talk about a demo too soon and not enough about the problem the audience is trying to solve.

## A useful sequence

1. Context and pain point.
2. A practical example.
3. A decision checklist.
4. A short case study.
5. A low-pressure next step.

## The goal

The best nurture flow makes the next conversation feel obvious, not forced.', '', 'Email sequences that activate leads without sounding automated | Marketing Studies', 'A practical sequence for nurturing MQLs with timing, context and calls to action that feel human.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (20, 7, 'en', 'How to know when attribution is lying and costing you budget', 'attribution-lies-costing-budget', 'Signals that reveal broken tracking, inflated conversions and dashboards that push poor decisions.', '## When numbers look too good

If every campaign looks like a winner, you may not be measuring the full journey.

## Where to check first

1. Duplicate conversion events.
2. Missing offline revenue.
3. Overweighted last-click reports.
4. Channels claiming the same lead.
5. Dashboards without quality signals.

## Better decisions

Use attribution as a decision aid, not as the only truth source.', '', 'How to know when attribution is lying and costing you budget | Marketing Studies', 'Signals that reveal broken tracking, inflated conversions and dashboards that push poor decisions.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (21, 8, 'en', 'Technical SEO checklist to speed up indexation without twenty plugins', 'technical-seo-indexation-checklist', 'A practical list to review crawlability, internal links, speed and indexability signals.', '## Before publishing more

Check whether Google can truly understand your site.

## Quick checklist

1. Clean and updated sitemap.
2. Robots rules without accidental blocks.
3. Correct canonicals.
4. Sensible internal pagination.
5. Core Web Vitals on key templates.

## What unlocks speed

The best combo is internal architecture, contextual links and clean pages without weak templates.', '', 'Technical SEO checklist to speed up indexation without twenty plugins | Marketing Studies', 'A practical list to review crawlability, internal links, speed and indexability signals.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (22, 9, 'en', 'Campaign architecture to scale Google Ads without losing control', 'google-ads-campaign-architecture-control', 'How to separate intent, creative and landing pages so budgets can grow without operational chaos.', '## The risk of scaling too fast

When the structure is weak, more budget only accelerates waste.

## A useful architecture

1. Separate brand.
2. Group high intent by cluster.
3. Keep creative testing contained.
4. Match landing pages to the promise.
5. Review budget by margin and signal quality.

## Scale with control

The account should make decisions easier as spend grows, not harder.', '', 'Campaign architecture to scale Google Ads without losing control | Marketing Studies', 'How to separate intent, creative and landing pages so budgets can grow without operational chaos.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (23, 10, 'en', 'A CRO experiment map for teams done testing by intuition', 'cro-experiment-map', 'A system to prioritize conversion hypotheses with evidence, expected impact and learning speed.', '## The problem

Testing random ideas can feel productive, but it rarely builds a repeatable advantage.

## Build a living backlog

1. Hypothesis with insight.
2. Previous evidence.
3. Expected impact.
4. Implementation effort.
5. Learning value.

## Make tests compound

A good CRO program gets smarter every week because every result updates the map.', '', 'A CRO experiment map for teams done testing by intuition | Marketing Studies', 'A system to prioritize conversion hypotheses with evidence, expected impact and learning speed.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (24, 11, 'en', 'CRM nurture loops: from cold lead to opportunity with less friction', 'crm-nurture-loops-opportunity', 'A framework to connect scoring, automation and useful content without pushing the sale too early.', '## Nurture is not just sending emails

A strong loop synchronizes behavior, context and sales timing.

## Key components

1. Reliable behavior events.
2. Segments that reflect intent.
3. Content mapped to the buying stage.
4. Alerts when the account is ready.
5. Clean handoff rules.

## Less friction

The buyer should feel guided, not chased.', '', 'CRM nurture loops: from cold lead to opportunity with less friction | Marketing Studies', 'A framework to connect scoring, automation and useful content without pushing the sale too early.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (25, 12, 'en', 'Attribution dashboard for founders: metrics to watch and ignore', 'attribution-dashboard-founders', 'A compact dashboard to avoid vanity metrics when you need to decide budget quickly.', '## Fewer widgets, better reading

If the dashboard looks like an airplane cockpit, no one will use it consistently.

## Metrics that matter

1. CAC by channel.
2. Conversion by stage.
3. Pipeline value.
4. Payback period.
5. Lead quality by source.

## What to ignore

Avoid reports that look impressive but do not change the next decision.', '', 'Attribution dashboard for founders: metrics to watch and ignore | Marketing Studies', 'A compact dashboard to avoid vanity metrics when you need to decide budget quickly.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (26, 13, 'en', 'Case study: B2B ecommerce reduced CAC with comparative content', 'b2b-ecommerce-cac-comparative-content-case-study', 'A breakdown of how comparisons, intent-based landings and remarketing reduced CAC without increasing budget.', '## Context

A B2B ecommerce company had solid organic traffic, but most visitors arrived through informational content with low conversion.

## Challenge

The team needed to capture higher intent without raising media spend.

## What changed

They built comparison pages, connected them to remarketing and used clearer product decision criteria.

## Result

The funnel produced better-qualified visitors and lowered CAC by focusing on decision-stage content.', '', 'Case study: B2B ecommerce reduced CAC with comparative content | Marketing Studies', 'A breakdown of how comparisons, intent-based landings and remarketing reduced CAC without increasing budget.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (27, 14, 'en', 'Case study: professional service tripled meetings with a sharper landing page', 'professional-services-landing-page-case-study', 'How an objection-led redesign, stronger proof and contextual CTAs converted more visits into meetings.', '## Context

A professional services firm had qualified traffic, but the site explained too much and persuaded too little.

## Challenge

The main landing page did not clearly show who the service was for or why prospects should book a call.

## What changed

The team rewrote the promise, moved proof higher and simplified the CTA path.

## Result

The page created clearer intent and tripled meeting requests.', '', 'Case study: professional service tripled meetings with a sharper landing page | Marketing Studies', 'How an objection-led redesign, stronger proof and contextual CTAs converted more visits into meetings.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
INSERT INTO `post_translations` (`id`, `post_id`, `locale`, `title`, `slug`, `excerpt`, `content_markdown`, `content_html`, `seo_title`, `seo_description`, `created_at`, `updated_at`) VALUES (28, 15, 'en', 'Case study: SaaS recovered dormant pipeline with nurture loops', 'saas-nurture-loops-pipeline-case-study', 'A case about scoring, automation and useful content to reactivate old leads without overwhelming inboxes.', '## Context

A B2B SaaS company had thousands of historic leads in the CRM, but very few new conversations came from that base.

## Challenge

The team needed to reactivate pipeline without sending generic blasts.

## What changed

They rebuilt scoring, created stage-based content and triggered sales alerts only when behavior showed intent.

## Result

Dormant leads became useful opportunities again with less noise and better timing.', '', 'Case study: SaaS recovered dormant pipeline with nurture loops | Marketing Studies', 'A case about scoring, automation and useful content to reactivate old leads without overwhelming inboxes.', '2026-06-05T17:23:06+00:00', '2026-06-05T17:23:06+00:00');
UNLOCK TABLES;

LOCK TABLES `comments` WRITE;
UNLOCK TABLES;

LOCK TABLES `newsletter_subscribers` WRITE;
INSERT INTO `newsletter_subscribers` (`id`, `email`, `locale`, `source`, `created_at`, `status`, `unsubscribe_token`, `unsubscribed_at`, `last_sent_at`) VALUES (2, 'jovani@publilum.com', 'es', 'footer', '2026-06-03T20:38:11', 'active', '949f00f83b88728ac8c30092eee805aef2e5efa6523c4caf', NULL, NULL);
UNLOCK TABLES;

LOCK TABLES `contact_messages` WRITE;
INSERT INTO `contact_messages` (`id`, `name`, `email`, `topic`, `message`, `locale`, `status`, `created_at`) VALUES (1, 'Test User', 'test-1780514964@example.com', 'Growth', 'Testing contact form message.', 'en', 'new', '2026-06-03T19:29:24');
UNLOCK TABLES;

LOCK TABLES `lab_resources` WRITE;
INSERT INTO `lab_resources` (`id`, `title`, `slug`, `description`, `resource_type`, `download_url`, `cover_image`, `author_name`, `source_name`, `source_url`, `status`, `is_featured`, `download_count`, `created_at`, `updated_at`, `published_at`) VALUES (1, 'Checklist de auditoria SEO express', 'checklist-auditoria-seo-express', 'Una lista practica para revisar indexacion, sitemap, titles, headings, contenido y Core Web Vitals antes de publicar.', 'Checklist', '/static/downloads/checklist-auditoria-seo-express.txt', 'https://images.unsplash.com/photo-1483058712412-4245e9b90334?auto=format&fit=crop&w=1200&q=80', 'Jovani', '', '', 'published', 1, 3, '2026-06-03T21:29:29', '2026-06-03T21:29:29', '2026-06-03T21:29:29');
INSERT INTO `lab_resources` (`id`, `title`, `slug`, `description`, `resource_type`, `download_url`, `cover_image`, `author_name`, `source_name`, `source_url`, `status`, `is_featured`, `download_count`, `created_at`, `updated_at`, `published_at`) VALUES (2, 'Brief para experimento de growth', 'brief-experimento-growth', 'Formato base para documentar hipotesis, audiencia, canales, metrica principal y criterios de decision.', 'Template', '/static/downloads/brief-experimento-growth.txt', 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80', 'Jovani', '', '', 'published', 0, 1, '2026-06-03T21:29:29', '2026-06-03T21:29:29', '2026-06-03T21:29:29');
INSERT INTO `lab_resources` (`id`, `title`, `slug`, `description`, `resource_type`, `download_url`, `cover_image`, `author_name`, `source_name`, `source_url`, `status`, `is_featured`, `download_count`, `created_at`, `updated_at`, `published_at`) VALUES (3, 'Guia de medicion para campañas', 'guia-medicion-campanas-google-analytics', 'Referencia externa para pensar eventos, conversiones y reportes antes de lanzar campanas.', 'Reference', 'https://support.google.com/analytics/', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80', '', 'Google Analytics Help', 'https://support.google.com/analytics/', 'published', 0, 0, '2026-06-03T21:29:29', '2026-06-03T21:29:29', '2026-06-03T21:29:29');
UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS = 1;
