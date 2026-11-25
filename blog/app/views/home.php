<?php
$isEs = $locale === 'es';
$contactEmail = trim((string) ($site['contact_email'] ?? '')) ?: 'jovani@marketing-studies.com';
$portrait = versioned_asset_url('static/brand/jovani-trevino.jpg');
$featuredStory = $featured ?? ($latest[0] ?? null);
$caseUrl = url_path('/case-studies', $locale);
$blogUrl = url_path('/blog', $locale);
$labUrl = url_path('/lab', $locale);

$copy = $isEs ? [
    'eyebrow' => 'Jovani Treviño / Marketing Studies',
    'name' => 'Jovani Treviño',
    'headline' => 'Marketing Intelligence, SEO, performance y ejecución digital con criterio de negocio.',
    'intro' => 'Estratega de marketing con 9 años de experiencia integrando analítica, contenido, adquisición y activos web para convertir datos en decisiones, posicionamiento en crecimiento y ejecución en resultados medibles.',
    'primary_cta' => 'Explorar blog',
    'secondary_cta' => 'Ver especialidades',
    'tertiary_cta' => 'Escribeme',
    'current_label' => 'Actual en La Treviñosa Banda Regia',
    'current_role' => 'Marketing Intelligence & Performance',
    'current_copy' => 'Construyendo presencia digital, estrategia de contenido, amplificacion de marca y crecimiento sostenido para un proyecto musical con identidad propia.',
    'featured_label' => 'Se actualiza con el blog',
    'featured_copy' => 'Cada nuevo articulo, caso o insight publicado aterriza aqui para que la pagina siga viva y alineada con Marketing Studies.',
    'story_eyebrow' => 'Perfil profesional',
    'story_title' => 'Una página personal con fondo editorial, pensamiento estratégico y ejecución real.',
    'story_body_one' => 'Mi enfoque cruza investigación, SEO, performance, analítica y experiencia digital para que una marca no solo se vea bien, sino que entienda mejor su mercado, opere con más claridad y convierta mejor.',
    'story_body_two' => 'He trabajado entre pauta, contenido, UX, front-end, CRM y crecimiento de marca, por eso esta home mezcla narrativa personal con blog, casos de estudio y recursos que pueden seguir creciendo desde el CMS.',
    'story_stats' => [
        ['icon' => 'bi bi-bar-chart-line', 'text' => 'Decision making con data y contexto'],
        ['icon' => 'bi bi-search', 'text' => 'SEO, semantica y visibilidad organica'],
        ['icon' => 'bi bi-bullseye', 'text' => 'Performance media con foco en negocio'],
    ],
    'services_eyebrow' => 'Especialidades',
    'services_title' => 'Areas donde mas valor puedo crear',
    'services_link' => 'Ir al archivo completo',
    'experience_eyebrow' => 'Experiencia aplicada',
    'experience_title' => 'Trabajo en marcas, contenidos, pauta y activos digitales',
    'credentials_eyebrow' => 'Credenciales y stack',
    'credentials_title' => 'Formacion, certificaciones y herramientas con las que opero',
    'credentials_copy' => 'Licenciado en Ciencias de la Comunicación por la Universidad Autónoma de Nuevo León, con experiencia combinando marketing, diseño, contenido, tecnología y analítica para resolver retos de crecimiento.',
    'stack_cta' => 'Ver recursos del Lab',
    'case_eyebrow' => 'Casos y breakdowns',
    'case_title' => 'Analisis donde la estrategia aterriza en decisiones',
    'case_desc' => 'Campañas, marcas, ejecución y aprendizajes publicados desde el mismo ecosistema de Marketing Studies.',
    'insights_eyebrow' => 'Blog vivo',
    'insights_title' => 'Ultimos insights, ideas y piezas editoriales',
    'insights_link' => 'Ver todo el blog',
    'feed_eyebrow' => 'Mas reciente',
    'feed_title' => 'Publicaciones nuevas que siguen alimentando la pagina',
    'newsletter_title' => 'Recibe ideas utiles antes que se pierdan en el feed',
    'newsletter_desc' => 'Un correo con estrategia, SEO, performance, contenido y marketing con criterio. Practico, claro y sin relleno.',
    'newsletter_button' => 'Suscribirme',
    'newsletter_note' => 'Semanal, practico y facil de cancelar.',
    'contact_eyebrow' => 'Contacto',
    'contact_title' => 'Si quieres estrategia, contenido, performance o una web mejor pensada, hablemos.',
    'contact_desc' => 'La pagina ya conecta con el CMS y el blog; ahora tambien puede funcionar como tu punto de contacto para proyectos, colaboraciones o nuevas oportunidades.',
    'contact_email_label' => 'Correo directo',
    'contact_role_label' => 'Enfoque actual',
    'contact_lab_label' => 'Tambien explora',
    'contact_button' => 'Enviar mensaje',
    'read_featured' => 'Leer articulo destacado',
    'view_all_cases' => 'Ver todos los casos',
    'read_case' => 'Leer caso',
    'read_article' => 'Leer articulo',
    'latest_articles' => 'articulos publicados',
    'case_count' => 'casos de estudio',
    'lab_count' => 'recursos y descargas',
    'signals' => [
        ['icon' => 'bi bi-briefcase', 'text' => '9+ años combinando estrategia, contenido y performance.'],
        ['icon' => 'bi bi-music-note-beamed', 'text' => 'Actual en La Treviñosa, impulsando marca y crecimiento digital.'],
        ['icon' => 'bi bi-journal-richtext', 'text' => $totalPosts . ' articulos publicados que siguen actualizando la home.'],
        ['icon' => 'bi bi-window-stack', 'text' => $totalCaseStudies . ' casos de estudio y ' . $totalLabResources . ' recursos activos en el ecosistema.'],
    ],
    'topics' => [
        ['icon' => 'bi bi-graph-up-arrow', 'title' => 'Marketing Intelligence', 'copy' => 'Data, decisiones y growth.', 'href' => '#services'],
        ['icon' => 'bi bi-search-heart', 'title' => 'SEO & Content Systems', 'copy' => 'Search intent y visibilidad.', 'href' => '#services'],
        ['icon' => 'bi bi-badge-ad', 'title' => 'Performance & Paid', 'copy' => 'Campañas con foco en resultados.', 'href' => '#services'],
        ['icon' => 'bi bi-code-slash', 'title' => 'Front-End & UX', 'copy' => 'Activos web y ejecución visual.', 'href' => '#stack'],
    ],
    'services' => [
        [
            'icon' => 'bi bi-graph-up-arrow',
            'title' => 'Marketing Intelligence & Performance',
            'copy' => 'Planeación, lectura de datos, optimización de inversión y enfoque en indicadores que realmente mueven negocio.',
        ],
        [
            'icon' => 'bi bi-search',
            'title' => 'SEO, semantica y contenido',
            'copy' => 'Arquitectura editorial, optimización on-page, intención de búsqueda y contenido pensado para ranking y conversión.',
        ],
        [
            'icon' => 'bi bi-bullseye',
            'title' => 'Paid media con sentido estratégico',
            'copy' => 'Campañas para Meta, Google y otras plataformas con análisis constante, eficiencia y mejora continua.',
        ],
        [
            'icon' => 'bi bi-window',
            'title' => 'Front-end, UX y activos digitales',
            'copy' => 'Landing pages, implementación visual, experiencia de usuario y ejecución web desde una base de marketing.',
        ],
    ],
    'experience' => [
        [
            'period' => '2016 - Actual',
            'company' => 'La Treviñosa Banda Regia',
            'role' => 'Estrategia de crecimiento y marketing musical',
            'points' => [
                'Desarrollo presencia digital, contenido, amplificacion y estrategia de crecimiento para fortalecer visibilidad y comunidad.',
                'Integro pauta, contenido, analítica y experimentación para impulsar alcance, interacción y consistencia de marca.',
                'Aterrizo ideas en activos, publicaciones, piezas y decisiones que mantienen el proyecto vivo y competitivo.',
            ],
        ],
        [
            'period' => 'Paid Media / Performance',
            'company' => 'Runauto Esquivel',
            'role' => 'Marketing Intelligence & Performance',
            'points' => [
                'Optimice campañas para Meta Ads y Google Ads con foco en eficiencia, conversión y aprendizaje accionable.',
                'Alinee pauta, contenido y analítica para elevar relevancia comercial y retorno sobre la inversión.',
                'Traduci datos de rendimiento en ajustes creativos, segmentacion y decisiones mas rentables.',
            ],
        ],
        [
            'period' => 'Marketing Ops / UX',
            'company' => 'Realty CRM Real Estate',
            'role' => 'Marketing, atencion al cliente y experiencia digital',
            'points' => [
                'Combine CRM, contenido, UX y ejecución digital para mejorar retención, experiencia y comunicación comercial.',
                'Participe en paginas, materiales visuales, automatizaciones y procesos orientados a conversion y servicio.',
                'Fortaleci la operacion conectando marketing, tecnologia y atencion con una logica mas clara para el usuario.',
            ],
        ],
    ],
    'stack_groups' => [
        [
            'title' => 'Formacion',
            'items' => [
                'Universidad Autonoma de Nuevo Leon',
                'Licenciado en Ciencias de la Comunicacion con enfoque en mercadotecnia',
                'Base en comunicacion, marca, investigacion y ecosistemas digitales',
            ],
        ],
        [
            'title' => 'Certificaciones y criterio tecnico',
            'items' => [
                'Google Ads y Google Analytics',
                'Microsoft Advertising',
                'Experiencia práctica en SEO, CRO, pauta, reporting y analítica',
            ],
        ],
        [
            'title' => 'Herramientas de trabajo',
            'items' => [
                'Google Marketing Platform, Meta Ads, GA4, Search Console, Hootsuite',
                'Adobe Photoshop, Illustrator, Premiere, After Effects y InDesign',
                'HTML, CSS, Bootstrap, Visual Studio Code, CMS y activos web',
            ],
        ],
    ],
] : [
    'eyebrow' => 'Jovani Treviño / Marketing Studies',
    'name' => 'Jovani Treviño',
    'headline' => 'Marketing intelligence, SEO, performance and digital execution with business focus.',
    'intro' => 'Marketing strategist with 9 years of experience connecting analytics, content, acquisition and web assets to turn data into decisions, visibility into growth and execution into measurable results.',
    'primary_cta' => 'Explore the blog',
    'secondary_cta' => 'See focus areas',
    'tertiary_cta' => 'Email me',
    'current_label' => 'Currently at La Treviñosa Banda Regia',
    'current_role' => 'Marketing Intelligence & Performance',
    'current_copy' => 'Building digital presence, content strategy, brand amplification and steady growth for a music project with a strong identity.',
    'featured_label' => 'Updated by the blog',
    'featured_copy' => 'Every new article, case study or insight published lands here so the page keeps evolving with Marketing Studies.',
    'story_eyebrow' => 'Professional profile',
    'story_title' => 'A personal website with editorial depth, strategic thinking and real execution.',
    'story_body_one' => 'My approach blends research, SEO, performance, analytics and digital experience so a brand does not only look better, but understands its market, operates more clearly and converts with more confidence.',
    'story_body_two' => 'I have worked across paid media, content, UX, front-end, CRM and brand growth, so this homepage combines personal narrative with a live blog, case studies and resources managed from the CMS.',
    'story_stats' => [
        ['icon' => 'bi bi-bar-chart-line', 'text' => 'Decision making powered by context and data'],
        ['icon' => 'bi bi-search', 'text' => 'SEO, semantics and organic visibility'],
        ['icon' => 'bi bi-bullseye', 'text' => 'Performance media tied to business outcomes'],
    ],
    'services_eyebrow' => 'Focus areas',
    'services_title' => 'Where I can create the most value',
    'services_link' => 'Browse the full archive',
    'experience_eyebrow' => 'Applied experience',
    'experience_title' => 'Work across brands, content, paid media and digital assets',
    'credentials_eyebrow' => 'Credentials and stack',
    'credentials_title' => 'Education, certifications and tools behind the work',
    'credentials_copy' => 'Communication graduate from Universidad Autonoma de Nuevo Leon with hands-on experience combining marketing, design, content, technology and analytics to solve growth challenges.',
    'stack_cta' => 'Open the Lab resources',
    'case_eyebrow' => 'Cases and breakdowns',
    'case_title' => 'Analysis where strategy becomes concrete decisions',
    'case_desc' => 'Campaigns, brands, execution and learnings published from the same Marketing Studies ecosystem.',
    'insights_eyebrow' => 'Live blog',
    'insights_title' => 'Latest insights, ideas and editorial pieces',
    'insights_link' => 'Open the full blog',
    'feed_eyebrow' => 'Most recent',
    'feed_title' => 'Fresh posts that keep this page growing',
    'newsletter_title' => 'Get practical ideas before they disappear in the feed',
    'newsletter_desc' => 'One useful email with strategy, SEO, performance, content and sharper marketing thinking.',
    'newsletter_button' => 'Subscribe',
    'newsletter_note' => 'Weekly, practical and easy to unsubscribe.',
    'contact_eyebrow' => 'Contact',
    'contact_title' => 'If you need strategy, content, performance or a better digital experience, let us talk.',
    'contact_desc' => 'This homepage already connects to the CMS and blog; it can also work as your contact point for projects, collaborations and new opportunities.',
    'contact_email_label' => 'Direct email',
    'contact_role_label' => 'Current focus',
    'contact_lab_label' => 'Also explore',
    'contact_button' => 'Send message',
    'read_featured' => 'Read featured article',
    'view_all_cases' => 'View all case studies',
    'read_case' => 'Read case',
    'read_article' => 'Read article',
    'latest_articles' => 'published articles',
    'case_count' => 'case studies',
    'lab_count' => 'resources and downloads',
    'signals' => [
        ['icon' => 'bi bi-briefcase', 'text' => '9+ years blending strategy, content and performance.'],
        ['icon' => 'bi bi-music-note-beamed', 'text' => 'Currently at La Treviñosa, building digital growth and brand presence.'],
        ['icon' => 'bi bi-journal-richtext', 'text' => $totalPosts . ' published articles continuing to refresh the homepage.'],
        ['icon' => 'bi bi-window-stack', 'text' => $totalCaseStudies . ' case studies and ' . $totalLabResources . ' live resources in the ecosystem.'],
    ],
    'topics' => [
        ['icon' => 'bi bi-graph-up-arrow', 'title' => 'Marketing Intelligence', 'copy' => 'Data, decisions and growth.', 'href' => '#services'],
        ['icon' => 'bi bi-search-heart', 'title' => 'SEO & Content Systems', 'copy' => 'Search intent and visibility.', 'href' => '#services'],
        ['icon' => 'bi bi-badge-ad', 'title' => 'Performance & Paid', 'copy' => 'Campaigns tied to outcomes.', 'href' => '#services'],
        ['icon' => 'bi bi-code-slash', 'title' => 'Front-End & UX', 'copy' => 'Web assets and visual execution.', 'href' => '#stack'],
    ],
    'services' => [
        [
            'icon' => 'bi bi-graph-up-arrow',
            'title' => 'Marketing Intelligence & Performance',
            'copy' => 'Planning, data interpretation, investment optimization and a focus on metrics that truly move the business.',
        ],
        [
            'icon' => 'bi bi-search',
            'title' => 'SEO, semantics and content',
            'copy' => 'Editorial architecture, on-page optimization, search intent and content built for ranking and conversion.',
        ],
        [
            'icon' => 'bi bi-bullseye',
            'title' => 'Paid media with strategic discipline',
            'copy' => 'Campaigns across Meta, Google and other platforms with constant analysis, efficiency and iteration.',
        ],
        [
            'icon' => 'bi bi-window',
            'title' => 'Front-end, UX and digital assets',
            'copy' => 'Landing pages, visual implementation, user experience and web execution from a marketing-first perspective.',
        ],
    ],
    'experience' => [
        [
            'period' => '2016 - Present',
            'company' => 'La Treviñosa Banda Regia',
            'role' => 'Growth strategy and music marketing',
            'points' => [
                'I manage digital presence, content, amplification and growth strategy to strengthen visibility and community.',
                'I connect paid media, content, analytics and experimentation to improve reach, engagement and brand consistency.',
                'I turn ideas into assets, posts, campaigns and decisions that keep the project active and competitive.',
            ],
        ],
        [
            'period' => 'Paid Media / Performance',
            'company' => 'Runauto Esquivel',
            'role' => 'Marketing Intelligence & Performance',
            'points' => [
                'I optimized Meta Ads and Google Ads campaigns around efficiency, conversion and practical learning.',
                'I aligned paid media, content and analytics to improve commercial relevance and return on investment.',
                'I translated performance data into creative adjustments, targeting changes and more profitable decisions.',
            ],
        ],
        [
            'period' => 'Marketing Ops / UX',
            'company' => 'Realty CRM Real Estate',
            'role' => 'Marketing, customer care and digital experience',
            'points' => [
                'I combined CRM, content, UX and digital execution to improve retention, user experience and commercial communication.',
                'I contributed to pages, visual materials, automations and processes tied to conversion and service.',
                'I strengthened operations by connecting marketing, technology and support with a clearer user logic.',
            ],
        ],
    ],
    'stack_groups' => [
        [
            'title' => 'Education',
            'items' => [
                'Universidad Autonoma de Nuevo Leon',
                'Communication degree with marketing focus',
                'Foundation in communication, branding, research and digital ecosystems',
            ],
        ],
        [
            'title' => 'Certifications and technical criteria',
            'items' => [
                'Google Ads and Google Analytics',
                'Microsoft Advertising',
                'Hands-on experience in SEO, CRO, paid media, reporting and analytics',
            ],
        ],
        [
            'title' => 'Working stack',
            'items' => [
                'Google Marketing Platform, Meta Ads, GA4, Search Console, Hootsuite',
                'Adobe Photoshop, Illustrator, Premiere, After Effects and InDesign',
                'HTML, CSS, Bootstrap, Visual Studio Code, CMS workflows and web assets',
            ],
        ],
    ],
];
?>

<div class="jovani-home">
    <section class="section-space jovani-hero-section">
        <div class="container">
            <div class="jovani-hero-shell">
                <div class="business-hero-grid">
                    <article class="page-hero-panel jovani-hero-panel">
                        <span class="eyebrow"><?= e($copy['eyebrow']) ?></span>
                        <p class="jovani-nameplate"><?= e($copy['name']) ?></p>
                        <h1><?= e($copy['headline']) ?></h1>
                        <p><?= e($copy['intro']) ?></p>

                        <div class="business-hero-actions">
                            <a href="<?= e($blogUrl) ?>" class="btn btn-dark px-4">
                                <i class="bi bi-journal-richtext"></i>
                                <?= e($copy['primary_cta']) ?>
                            </a>
                            <a href="#services" class="btn btn-outline-dark px-4">
                                <i class="bi bi-grid-1x2"></i>
                                <?= e($copy['secondary_cta']) ?>
                            </a>
                            <a href="mailto:<?= e($contactEmail) ?>" class="btn btn-outline-dark px-4">
                                <i class="bi bi-envelope"></i>
                                <?= e($copy['tertiary_cta']) ?>
                            </a>
                        </div>

                        <div class="signal-strip jovani-signal-strip">
                            <?php foreach ($copy['signals'] as $signal): ?>
                                <span><i class="<?= e($signal['icon']) ?>"></i><?= e($signal['text']) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="business-topic-strip jovani-anchor-grid">
                            <?php foreach ($copy['topics'] as $index => $topic): ?>
                                <a href="<?= e($topic['href']) ?>" style="--topic-index: <?= (int) $index ?>;">
                                    <i class="<?= e($topic['icon']) ?>"></i>
                                    <span class="jovani-anchor-copy">
                                        <strong><?= e($topic['title']) ?></strong>
                                        <small><?= e($topic['copy']) ?></small>
                                    </span>
                                    <i class="bi bi-arrow-right-short"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <aside class="hero-side-panel jovani-hero-side">
                        <article class="hero-side-card dark jovani-portrait-card">
                            <div class="jovani-portrait-stack">
                                <div class="jovani-portrait-frame">
                                    <img src="<?= e($portrait) ?>" alt="<?= e($copy['name']) ?>" loading="eager" decoding="async">
                                </div>
                                <div>
                                    <span class="eyebrow"><?= e($copy['current_label']) ?></span>
                                    <h2><?= e($copy['current_role']) ?></h2>
                                    <p><?= e($copy['current_copy']) ?></p>
                                </div>
                            </div>
                            <div class="jovani-chip-list">
                                <span>SEO</span>
                                <span>Performance</span>
                                <span>Analytics</span>
                                <span>Front-End</span>
                            </div>
                            <div class="jovani-contact-inline">
                                <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>
                                <span>Marketing Studies</span>
                            </div>
                        </article>

                        <?php if ($featuredStory): ?>
                            <article class="hero-side-card jovani-live-card">
                                <span class="eyebrow"><?= e($copy['featured_label']) ?></span>
                                <h2><?= e($featuredStory['title']) ?></h2>
                                <p><?= e($copy['featured_copy']) ?></p>
                                <div class="post-meta">
                                    <span><?= (int) ($featuredStory['read_time'] ?? 1) ?> min</span>
                                    <span><?= e(date_label($featuredStory['published_at'] ?? $featuredStory['updated_at'] ?? '')) ?></span>
                                </div>
                                <a href="<?= e(post_url($featuredStory, $locale)) ?>" class="post-link">
                                    <?= e($copy['read_featured']) ?> <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </article>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <section class="business-section pt-0" id="about">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-7">
                    <article class="featured-story jovani-story-card">
                        <span class="eyebrow"><?= e($copy['story_eyebrow']) ?></span>
                        <h3><?= e($copy['story_title']) ?></h3>
                        <p><?= e($copy['story_body_one']) ?></p>
                        <p><?= e($copy['story_body_two']) ?></p>
                        <div class="featured-focus-stats">
                            <?php foreach ($copy['story_stats'] as $stat): ?>
                                <span><i class="<?= e($stat['icon']) ?>"></i><?= e($stat['text']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </article>
                </div>
                <div class="col-lg-5">
                    <div class="row g-4">
                        <div class="col-12">
                            <article class="info-panel jovani-summary-panel">
                                <span class="eyebrow"><?= e($copy['insights_eyebrow']) ?></span>
                                <h3><?= e($totalPosts) ?> <?= e($copy['latest_articles']) ?></h3>
                                <p><?= e($totalCaseStudies) ?> <?= e($copy['case_count']) ?> y <?= e($totalLabResources) ?> <?= e($copy['lab_count']) ?> conectados desde un mismo CMS editorial.</p>
                            </article>
                        </div>
                        <div class="col-12">
                            <article class="info-panel jovani-summary-panel">
                                <span class="eyebrow">Marketing Studies</span>
                                <h3><?= e($isEs ? 'Pagina personal + blog + recursos' : 'Personal site + blog + resources') ?></h3>
                                <p><?= e($isEs ? 'Esta version deja atras la antigua web azul y evoluciona a una presencia mas editorial, estrategica y escalable.' : 'This version moves beyond the old blue portfolio into a more editorial, strategic and scalable presence.') ?></p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="business-section pt-0" id="services">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><?= e($copy['services_eyebrow']) ?></span>
                    <h2><?= e($copy['services_title']) ?></h2>
                </div>
                <a href="<?= e($blogUrl) ?>" class="section-link"><?= e($copy['services_link']) ?></a>
            </div>
            <div class="row g-4">
                <?php foreach ($copy['services'] as $service): ?>
                    <div class="col-md-6 col-xl-3">
                        <article class="business-panel jovani-service-card">
                            <div class="category-card-icon small"><i class="<?= e($service['icon']) ?>"></i></div>
                            <h3><?= e($service['title']) ?></h3>
                            <p><?= e($service['copy']) ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="business-section pt-0" id="stack">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-5">
                    <article class="info-panel jovani-credentials-card">
                        <span class="eyebrow"><?= e($copy['credentials_eyebrow']) ?></span>
                        <h3><?= e($copy['credentials_title']) ?></h3>
                        <p><?= e($copy['credentials_copy']) ?></p>
                        <a href="<?= e($labUrl) ?>" class="btn btn-outline-dark px-4 mt-2">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <?= e($copy['stack_cta']) ?>
                        </a>
                    </article>
                </div>
                <div class="col-lg-7">
                    <article class="page-hero-panel jovani-stack-panel">
                        <div class="row g-4">
                            <?php foreach ($copy['stack_groups'] as $group): ?>
                                <div class="col-md-4">
                                    <div class="jovani-stack-group">
                                        <h3><?= e($group['title']) ?></h3>
                                        <ul class="jovani-panel-list">
                                            <?php foreach ($group['items'] as $item): ?>
                                                <li><?= e($item) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <?php if ($caseStudies): ?>
        <section class="business-section pt-0 case-study-showcase">
            <div class="container">
                <div class="case-study-shell">
                    <div class="case-study-intro">
                        <span class="eyebrow"><?= e($copy['case_eyebrow']) ?></span>
                        <h2><?= e($copy['case_title']) ?></h2>
                        <p><?= e($copy['case_desc']) ?></p>
                        <a href="<?= e($caseUrl) ?>" class="btn btn-dark rounded-pill px-4">
                            <?= e($copy['view_all_cases']) ?> <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                    <div class="case-study-grid">
                        <?php foreach ($caseStudies as $study): ?>
                            <?php $studyCover = media_url($study['cover_image'] ?? '') ?: media_url($site['hero_background_image'] ?? ''); ?>
                            <article class="case-study-card" style="--category-accent: <?= e($study['category_color'] ?? '#c9853b') ?>">
                                <a href="<?= e(post_url($study, $locale)) ?>" class="case-study-card-media">
                                    <img src="<?= e($studyCover) ?>" alt="<?= e($study['title']) ?>" loading="lazy" decoding="async">
                                    <span><i class="<?= e($study['category_icon'] ?? 'bi bi-layout-text-window-reverse') ?>"></i> <?= e($study['category_name'] ?? 'Case') ?></span>
                                </a>
                                <div class="case-study-card-body">
                                    <div class="case-study-card-meta">
                                        <span><?= (int) $study['read_time'] ?> min</span>
                                        <span><?= e(date_label($study['published_at'])) ?></span>
                                    </div>
                                    <h3><a href="<?= e(post_url($study, $locale)) ?>"><?= e($study['title']) ?></a></h3>
                                    <p><?= e($study['excerpt']) ?></p>
                                    <a href="<?= e(post_url($study, $locale)) ?>" class="post-link"><?= e($copy['read_case']) ?> <i class="bi bi-arrow-right-short"></i></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php $featuredShowcase = $featuredPosts ?? []; ?>
    <?php if ($featuredShowcase): ?>
        <section class="business-section pt-0" id="insights">
            <div class="container">
                <div class="section-head insights-editorial-head">
                    <div>
                        <span class="eyebrow"><?= e($isEs ? 'Destacados del blog' : 'Featured from the blog') ?></span>
                        <h2><?= e($isEs ? 'Piezas destacadas' : 'Featured pieces') ?></h2>
                    </div>
                    <a href="<?= e($blogUrl) ?>" class="section-link"><?= e($copy['insights_link']) ?></a>
                </div>
                <?php $sideInsights = array_slice($featuredShowcase, 1, 3); ?>
                <div class="insights-editorial-grid<?= $sideInsights ? '' : ' insights-editorial-grid-single' ?>">
                    <?php
                    $lead = $featuredShowcase[0];
                    $leadCover = media_url($lead['cover_image'] ?? '') ?: media_url($site['hero_background_image'] ?? '');
                    $leadAuthorName = $lead['author_display_name'] ?? $lead['author_name'] ?? ($site['author_name'] ?? 'Jovani');
                    $leadAuthorSlug = ($lead['author_slug'] ?? '') ?: slugify($leadAuthorName);
                    $leadAuthorAvatar = author_avatar_url($leadAuthorName, $lead['author_avatar_url'] ?? null);
                    $leadExcerpt = post_display_excerpt($lead, 200);
                    ?>
                    <article class="insight-lead-card">
                        <a href="<?= e(post_url($lead, $locale)) ?>" class="insight-lead-media">
                            <img src="<?= e($leadCover) ?>" alt="<?= e($lead['title']) ?>" loading="lazy" decoding="async">
                        </a>
                        <div class="insight-lead-copy">
                            <div class="post-meta">
                                <span><?= (int) $lead['read_time'] ?> min</span>
                                <span><?= e(date_label($lead['published_at'])) ?></span>
                                <span><i class="bi bi-chat-left-text"></i> <?= (int) $lead['approved_comment_count'] ?></span>
                            </div>
                            <h3><a href="<?= e(post_url($lead, $locale)) ?>"><?= e($lead['title']) ?></a></h3>
                            <?php if ($leadExcerpt !== ''): ?>
                                <p><?= e($leadExcerpt) ?></p>
                            <?php endif; ?>
                            <div class="insight-lead-footer">
                                <a class="story-inline-author" href="<?= e(url_path('/author/' . $leadAuthorSlug, $locale)) ?>">
                                    <img src="<?= e($leadAuthorAvatar) ?>" alt="<?= e($leadAuthorName) ?>" loading="lazy" decoding="async">
                                    <span><?= e(t('post.by') . ' ' . $leadAuthorName) ?></span>
                                </a>
                                <a href="<?= e(post_url($lead, $locale)) ?>" class="post-link"><?= e($copy['read_article']) ?> <i class="bi bi-arrow-right-short"></i></a>
                            </div>
                        </div>
                    </article>
                    <?php if ($sideInsights): ?>
                        <div class="insight-side-stack">
                            <?php foreach ($sideInsights as $post): ?>
                                <?php $postCover = media_url($post['cover_image'] ?? '') ?: media_url($site['hero_background_image'] ?? ''); ?>
                                <?php $postExcerpt = post_display_excerpt($post, 130); ?>
                                <article class="insight-side-card">
                                    <a href="<?= e(post_url($post, $locale)) ?>" class="insight-side-media">
                                        <img src="<?= e($postCover) ?>" alt="<?= e($post['title']) ?>" loading="lazy" decoding="async">
                                    </a>
                                    <div class="insight-side-copy">
                                        <div class="post-meta"><span><?= e($post['category_name'] ?? '') ?></span><span><?= (int) $post['read_time'] ?> min</span></div>
                                        <h3><a href="<?= e(post_url($post, $locale)) ?>"><?= e($post['title']) ?></a></h3>
                                        <?php if ($postExcerpt !== ''): ?>
                                            <p><?= e($postExcerpt) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($feedPosts || !empty($feedHasRemaining)): ?>
        <section class="business-section pt-0" id="feed">
            <div class="container">
                <div class="section-head">
                    <div>
                        <span class="eyebrow"><?= e($copy['feed_eyebrow']) ?></span>
                        <h2><?= e($copy['feed_title']) ?></h2>
                    </div>
                </div>
                <div class="row g-4 infinite-post-grid"
                     data-infinite-grid
                     data-next-offset="<?= count($feedPosts) ?>"
                     data-limit="6"
                     data-exclude-id="<?= (int) (($feedExcludeIds[0] ?? 0)) ?>"
                     data-exclude-ids="<?= e(implode(',', $feedExcludeIds ?? [])) ?>"
                     data-exclude-category="casos-de-estudio"
                     data-exclude-featured="1"
                     data-layout="home"
                     data-endpoint="<?= e(url_path('/api/posts', $locale)) ?>">
                    <?php foreach ($feedPosts as $post): ?>
                        <div class="col-md-6 col-xl-4 infinite-post-item">
                            <?php require __DIR__ . '/partials/post_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="infinite-scroll-status" data-infinite-status data-loading-label="<?= e($isEs ? 'Cargando...' : 'Loading...') ?>" data-done-label="<?= e($isEs ? 'Ya no hay mas.' : 'You are at the end.') ?>">
                    <span class="spinner-border spinner-border-sm"></span>
                    <span><?= e($isEs ? 'Cargando...' : 'Loading...') ?></span>
                </div>
                <div class="infinite-scroll-sentinel" data-infinite-sentinel aria-hidden="true"></div>
            </div>
        </section>
    <?php endif; ?>

    <section class="business-section pt-0 newsletter-section" id="newsletter">
        <div class="container">
            <div class="newsletter-shell">
                <div class="newsletter-copy">
                    <span class="eyebrow">Newsletter</span>
                    <h2><?= e($copy['newsletter_title']) ?></h2>
                    <p><?= e($copy['newsletter_desc']) ?></p>
                </div>
                <form class="newsletter-form" method="post" action="<?= e(url_path('/newsletter', $locale)) ?>">
                    <?= csrf_input() ?>
                    <input type="hidden" name="source" value="home">
                    <input type="email" name="email" placeholder="you@company.com" required>
                    <button type="submit"><?= e($copy['newsletter_button']) ?></button>
                    <small><?= e($copy['newsletter_note']) ?></small>
                </form>
            </div>
        </div>
    </section>

    <section class="business-section pt-0 contact-section" id="contact">
        <div class="container">
            <div class="contact-shell">
                <div class="contact-copy">
                    <span class="eyebrow"><?= e($copy['contact_eyebrow']) ?></span>
                    <h2><?= e($copy['contact_title']) ?></h2>
                    <p><?= e($copy['contact_desc']) ?></p>
                    <div class="jovani-contact-meta">
                        <span><strong><?= e($copy['contact_email_label']) ?>:</strong> <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></span>
                        <span><strong><?= e($copy['contact_role_label']) ?>:</strong> <?= e($copy['current_role']) ?></span>
                        <span><strong><?= e($copy['contact_lab_label']) ?>:</strong> <a href="<?= e($labUrl) ?>">Marketing Studies Lab</a></span>
                    </div>
                </div>
                <form class="contact-form" method="post" action="<?= e(url_path('/contact', $locale)) ?>">
                    <?= csrf_input() ?>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label"><?= e($isEs ? 'Nombre' : 'Name') ?></label><input class="form-control" type="text" name="name" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
                        <div class="col-12"><label class="form-label"><?= e($isEs ? 'Tema' : 'Topic') ?></label><input class="form-control" type="text" name="topic"></div>
                        <div class="col-12"><label class="form-label"><?= e($isEs ? 'Mensaje' : 'Message') ?></label><textarea class="form-control" name="message" rows="5" required></textarea></div>
                        <div class="col-12"><button class="btn btn-dark rounded-pill px-4" type="submit"><?= e($copy['contact_button']) ?></button></div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
