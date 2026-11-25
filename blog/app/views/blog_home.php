<?php
$isEs = $locale === 'es';
$blogUrl = url_path('/blog', $locale);
$caseUrl = url_path('/case-studies', $locale);
$labUrl = url_path('/lab', $locale);
$contactEmail = trim((string) ($site['contact_email'] ?? '')) ?: 'info@marketing-studies.com';
$heroCover = media_url($site['hero_background_image'] ?? '') ?: CATEGORY_COVER_IMAGES['negocios'];
$heroWords = $isEs ? ['Investigacion', 'Estrategia', 'Growth'] : ['Research', 'Strategy', 'Growth'];
$popularSearches = $isEs ? ['SEO', 'CRO', 'Analitica'] : ['SEO', 'CRO', 'Analytics'];
$featuredLead = $featuredPosts[0] ?? ($latest[0] ?? null);
$featuredSidePosts = $featuredLead ? array_slice($featuredPosts, 1, 3) : [];
$caseReadLabel = $isEs ? 'Leer caso' : 'Read case';
$featuredEyebrow = $isEs ? 'Destacados' : 'Featured';
$featuredTitle = $isEs ? 'Posts destacados' : 'Featured stories';
$featuredLink = $isEs ? 'Ver archivo' : 'View archive';
$feedEyebrow = $isEs ? 'Recién publicados' : 'Recently published';
$contactTopicLabel = $isEs ? 'Tema' : 'Topic';
$contactMessageLabel = $isEs ? 'Mensaje' : 'Message';
$heroDescription = $isEs
    ? 'Un blog para documentar estrategia, analitica, contenido y growth con criterio practico.'
    : 'A publication about strategy, analytics, content and growth with practical criteria.';
?>

<section class="ms-hero container">
    <div class="ms-hero-copy">
        <span class="ms-eyebrow"><?= $isEs ? 'MARKETING, TECNOLOGÍA Y PENSAMIENTO CRÍTICO' : 'MARKETING, TECHNOLOGY & CRITICAL THINKING' ?></span>
        <h1><?= $isEs ? 'Menos suposiciones.<br>Más <em>criterio.</em>' : 'Fewer assumptions.<br>Better <em>decisions.</em>' ?></h1>
        <p><?= $isEs ? 'Ideas, análisis y herramientas para entender el marketing y llevarlo a la práctica. Una publicación para quienes no dejan de hacerse preguntas.' : 'Ideas, analysis and tools to understand marketing and put it into practice. A publication for people who never stop asking questions.' ?></p>
        <div class="ms-hero-actions"><a class="btn btn-dark" href="<?= e($blogUrl) ?>"><?= $isEs ? 'Explorar artículos' : 'Explore articles' ?></a><a class="ms-quiet-link" href="<?= e(base_path() . '/portfolio/') ?>"><?= $isEs ? 'Conoce al autor' : 'Meet the author' ?></a></div>
        <a class="ms-hero-signature" href="<?= e(url_path('/jovani', $locale)) ?>"><img src="<?= e(media_url('/static/brand/jovani-trevino.jpg')) ?>" alt="Jovani Treviño" width="44" height="44"><span><?= $isEs ? 'Una mirada de' : 'A perspective by' ?> <strong>Jovani Treviño</strong><small>Marketing Intelligence & Performance</small></span></a>
    </div>
    <?php if ($featuredLead): ?>
    <article class="ms-hero-feature">
        <a class="ms-feature-image" href="<?= e(post_url($featuredLead, $locale)) ?>"><img src="<?= e(media_url($featuredLead['cover_image'] ?? '') ?: $heroCover) ?>" alt="<?= e($featuredLead['title']) ?>" width="900" height="600" fetchpriority="high"><span class="ms-feature-flag"><i class="bi bi-stars" aria-hidden="true"></i><?= $isEs ? 'Selección editorial' : 'Editor’s pick' ?></span></a>
        <div class="ms-feature-copy"><div class="ms-feature-meta"><span><?= e($featuredLead['category_name'] ?? '') ?></span><span><?= (int) ($featuredLead['read_time'] ?? 5) ?> min</span></div><h2><a href="<?= e(post_url($featuredLead, $locale)) ?>"><?= e($featuredLead['title']) ?></a></h2><a class="ms-text-link" href="<?= e(post_url($featuredLead, $locale)) ?>"><?= $isEs ? 'Leer el análisis' : 'Read the analysis' ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
    </article>
    <?php else: ?>
    <div class="ms-hero-note"><i class="bi bi-book" aria-hidden="true"></i><span><?= $isEs ? 'EL CUADERNO' : 'THE JOURNAL' ?></span><h2><?= $isEs ? 'Las mejores respuestas<br>empiezan con mejores<br><em>preguntas.</em>' : 'Better answers<br>start with better<br><em>questions.</em>' ?></h2><p><?= $isEs ? 'Estrategia, datos y una mirada curiosa.' : 'Strategy, data and a curious perspective.' ?></p></div>
    <?php endif; ?>
</section>
<div class="ms-topic-strip"><div class="container"><span><?= $isEs ? 'EXPLORA LAS IDEAS' : 'EXPLORE THE IDEAS' ?></span><?php foreach (array_slice($navCategories, 0, 6) as $topic): ?><a href="<?= e(url_path('/' . $topic['slug'], $locale)) ?>"><?= e($topic['name']) ?></a><?php endforeach; ?></div></div>

<?php if ($caseStudies): ?>
    <section class="business-section pt-0 case-study-showcase">
        <div class="container">
            <div class="case-study-shell">
                <div class="case-study-intro">
                    <span class="eyebrow"><?= e($isEs ? 'Casos y breakdowns' : 'Cases and breakdowns') ?></span>
                    <h2><?= e(t('home.case_title')) ?></h2>
                    <p><?= e(t('home.case_desc')) ?></p>
                    <a href="<?= e($caseUrl) ?>" class="btn btn-dark rounded-pill px-4">
                        <?= e(t('home.view_all_cases')) ?> <i class="bi bi-arrow-up-right"></i>
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
                                <p><?= e(post_display_excerpt($study, 150)) ?></p>
                                <a href="<?= e(post_url($study, $locale)) ?>" class="post-link"><?= e($caseReadLabel) ?> <i class="bi bi-arrow-right-short"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($featuredSidePosts): ?>
<section class="business-section" id="featured"><div class="container"><div class="section-head"><div><span class="eyebrow"><?= e($featuredEyebrow) ?></span><h2><?= $isEs ? 'Ideas para profundizar.' : 'Ideas to explore further.' ?></h2></div><a class="ms-text-link" href="<?= e($blogUrl) ?>"><?= e($featuredLink) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div><div class="row g-4"><?php foreach ($featuredSidePosts as $post): ?><div class="col-md-6 col-xl-4"><?php require __DIR__ . '/partials/post_card.php'; ?></div><?php endforeach; ?></div></div></section>
<?php endif; ?>

<?php if ($feedPosts || !empty($feedHasRemaining)): ?>
    <section class="business-section pt-0" id="feed">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow"><?= e($feedEyebrow) ?></span>
                    <h2><?= $isEs ? 'Ideas que merecen una segunda lectura.' : 'Ideas worth a second read.' ?></h2>
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
                <h2><?= e(t('home.newsletter_title')) ?></h2>
                <p><?= e(t('home.newsletter_desc')) ?></p>
            </div>
            <form class="newsletter-form" method="post" action="<?= e(url_path('/newsletter', $locale)) ?>">
                <?= csrf_input() ?>
                <input type="hidden" name="source" value="home">
                <label class="visually-hidden" for="newsletter-email"><?= $isEs ? 'Tu correo electrónico' : 'Your email' ?></label><input id="newsletter-email" type="email" name="email" placeholder="<?= $isEs ? 'tu@correo.com' : 'you@example.com' ?>" required autocomplete="email" maxlength="254">
                <button type="submit"><?= e(t('home.newsletter_button')) ?></button>
                <small><?= e($isEs ? 'Novedades cuando tenemos algo útil que compartir. Puedes darte de baja cuando quieras.' : 'Updates when we have something useful to share. Unsubscribe whenever you wish.') ?></small>
            </form>
        </div>
    </div>
</section>

<section class="business-section pt-0 contact-section" id="contact">
    <div class="container">
        <div class="contact-shell">
            <div class="contact-copy">
                <span class="eyebrow"><?= e($isEs ? 'Contacto' : 'Contact') ?></span>
                <h2><?= e(t('home.contact_title')) ?></h2>
                <p><?= e(t('home.contact_desc')) ?></p>
                <div class="jovani-contact-meta">
                    <span><strong><?= e($isEs ? 'Correo' : 'Email') ?>:</strong> <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></span>
                    <span><strong><?= e($isEs ? 'Archivo completo' : 'Full archive') ?>:</strong> <a href="<?= e($blogUrl) ?>"><?= e($isEs ? 'Ver blog' : 'Open blog') ?></a></span>
                    <span><strong><?= e($isEs ? 'Recursos' : 'Resources') ?>:</strong> <a href="<?= e($labUrl) ?>">Marketing Studies Lab</a></span>
                </div>
            </div>
            <form class="contact-form" method="post" action="<?= e(url_path('/contact', $locale)) ?>">
                <?= csrf_input() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="contact-name"><?= e($isEs ? 'Nombre' : 'Name') ?></label><input id="contact-name" class="form-control" type="text" name="name" required maxlength="150" autocomplete="name"></div>
                    <div class="col-md-6"><label class="form-label" for="contact-email">Email</label><input id="contact-email" class="form-control" type="email" name="email" required maxlength="254" autocomplete="email"></div>
                    <div class="col-12"><label class="form-label" for="contact-topic"><?= e($contactTopicLabel) ?></label><input id="contact-topic" class="form-control" type="text" name="topic" maxlength="200"></div>
                    <div class="col-12"><label class="form-label" for="contact-message"><?= e($contactMessageLabel) ?></label><textarea id="contact-message" class="form-control" name="message" rows="5" required maxlength="10000"></textarea></div>
                    <div class="col-12"><button class="btn btn-dark rounded-pill px-4" type="submit"><?= e($isEs ? 'Enviar mensaje' : 'Send message') ?></button></div>
                </div>
            </form>
        </div>
    </div>
</section>
