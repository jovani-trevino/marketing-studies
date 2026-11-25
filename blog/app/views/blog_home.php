<?php
$isEs = $locale === 'es';
$blogUrl = url_path('/blog', $locale);
$caseUrl = url_path('/case-studies', $locale);
$labUrl = url_path('/lab', $locale);
$contactEmail = trim((string) ($site['contact_email'] ?? '')) ?: 'info@marketing-studies.com';
$heroCover = media_url($site['hero_background_image'] ?? '') ?: CATEGORY_COVER_IMAGES['negocios'];
$heroWords = $isEs ? ['Investigacion', 'Estrategia', 'Growth'] : ['Research', 'Strategy', 'Growth'];
$popularSearches = $isEs ? ['SEO', 'CRO', 'Analitica'] : ['SEO', 'CRO', 'Analytics'];
$featuredLead = $featuredPosts[0] ?? null;
$featuredSidePosts = $featuredLead ? array_slice($featuredPosts, 1, 3) : [];
$caseReadLabel = $isEs ? 'Leer caso' : 'Read case';
$featuredEyebrow = $isEs ? 'Destacados' : 'Featured';
$featuredTitle = $isEs ? 'Posts destacados' : 'Featured stories';
$featuredLink = $isEs ? 'Ver archivo' : 'View archive';
$feedEyebrow = $isEs ? 'Recien publicados' : 'Recently published';
$contactTopicLabel = $isEs ? 'Tema' : 'Topic';
$contactMessageLabel = $isEs ? 'Mensaje' : 'Message';
$heroDescription = $isEs
    ? 'Un blog para documentar estrategia, analitica, contenido y growth con criterio practico.'
    : 'A publication about strategy, analytics, content and growth with practical criteria.';
?>

<section class="landing-cover" style="--landing-cover-image: url('<?= e($heroCover) ?>');">
    <div class="container">
        <div class="landing-cover-content">
            <span class="eyebrow"><?= e(t('home.eyebrow')) ?></span>
            <h1 class="cover-animated-title" aria-label="<?= e($heroDescription) ?>">
                <?php foreach ($heroWords as $word): ?>
                    <span><?= e($word) ?></span>
                <?php endforeach; ?>
            </h1>
            <p><?= e($heroDescription) ?></p>
            <form class="hero-search-panel" method="get" action="<?= e($blogUrl) ?>">
                <i class="bi bi-search"></i>
                <input type="search" name="q" placeholder="<?= e(t('home.search_placeholder')) ?>" autocomplete="off">
                <button type="submit"><?= e(t('home.search_button')) ?></button>
            </form>
            <div class="hero-popular-searches">
                <span><?= e($isEs ? 'Busquedas populares' : 'Popular searches') ?></span>
                <?php foreach ($popularSearches as $term): ?>
                    <a href="<?= e($blogUrl . '?q=' . rawurlencode($term)) ?>"><?= e($term) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="landing-cover-actions">
                <a href="<?= e($caseUrl) ?>" class="btn btn-light btn-lg px-4 shadow-sm">
                    <i class="bi bi-window-stack"></i>
                    <?= e(t('home.case_studies')) ?>
                </a>
                <a href="#feed" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-clock"></i>
                    <?= e(t('home.latest')) ?>
                </a>
            </div>
        </div>
    </div>
</section>

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

<?php if ($featuredLead): ?>
    <section class="business-section pt-0" id="featured">
        <div class="container">
            <div class="section-head insights-editorial-head">
                <div>
                    <span class="eyebrow"><?= e($featuredEyebrow) ?></span>
                    <h2><?= e($featuredTitle) ?></h2>
                </div>
                <a href="<?= e($blogUrl) ?>" class="section-link"><?= e($featuredLink) ?></a>
            </div>
            <div class="insights-editorial-grid<?= $featuredSidePosts ? '' : ' insights-editorial-grid-single' ?>">
                <?php
                $leadCover = media_url($featuredLead['cover_image'] ?? '') ?: media_url($site['hero_background_image'] ?? '');
                $leadAuthorName = $featuredLead['author_display_name'] ?? $featuredLead['author_name'] ?? ($site['author_name'] ?? 'Jovani');
                $leadAuthorSlug = ($featuredLead['author_slug'] ?? '') ?: slugify($leadAuthorName);
                $leadAuthorAvatar = author_avatar_url($leadAuthorName, $featuredLead['author_avatar_url'] ?? null);
                $leadExcerpt = post_display_excerpt($featuredLead, 200);
                ?>
                <article class="insight-lead-card">
                    <a href="<?= e(post_url($featuredLead, $locale)) ?>" class="insight-lead-media">
                        <img src="<?= e($leadCover) ?>" alt="<?= e($featuredLead['title']) ?>" loading="lazy" decoding="async">
                    </a>
                    <div class="insight-lead-copy">
                        <div class="post-meta">
                            <span><?= (int) $featuredLead['read_time'] ?> min</span>
                            <span><?= e(date_label($featuredLead['published_at'])) ?></span>
                            <span><i class="bi bi-chat-left-text"></i> <?= (int) $featuredLead['approved_comment_count'] ?></span>
                        </div>
                        <h3><a href="<?= e(post_url($featuredLead, $locale)) ?>"><?= e($featuredLead['title']) ?></a></h3>
                        <?php if ($leadExcerpt !== ''): ?>
                            <p><?= e($leadExcerpt) ?></p>
                        <?php endif; ?>
                        <div class="insight-lead-footer">
                            <a class="story-inline-author" href="<?= e(url_path('/author/' . $leadAuthorSlug, $locale)) ?>">
                                <img src="<?= e($leadAuthorAvatar) ?>" alt="<?= e($leadAuthorName) ?>" loading="lazy" decoding="async">
                                <span><?= e(t('post.by') . ' ' . $leadAuthorName) ?></span>
                            </a>
                            <a href="<?= e(post_url($featuredLead, $locale)) ?>" class="post-link"><?= e(t('post.read_article')) ?> <i class="bi bi-arrow-right-short"></i></a>
                        </div>
                    </div>
                </article>
                <?php if ($featuredSidePosts): ?>
                    <div class="insight-side-stack">
                        <?php foreach ($featuredSidePosts as $post): ?>
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
                    <span class="eyebrow"><?= e($feedEyebrow) ?></span>
                    <h2><?= e(t('home.feed_title')) ?></h2>
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
                <input type="email" name="email" placeholder="you@company.com" required>
                <button type="submit"><?= e(t('home.newsletter_button')) ?></button>
                <small><?= e($isEs ? 'Semanal, practico y facil de cancelar.' : 'Weekly, practical and easy to unsubscribe.') ?></small>
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
                    <div class="col-md-6"><label class="form-label"><?= e($isEs ? 'Nombre' : 'Name') ?></label><input class="form-control" type="text" name="name" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
                    <div class="col-12"><label class="form-label"><?= e($contactTopicLabel) ?></label><input class="form-control" type="text" name="topic"></div>
                    <div class="col-12"><label class="form-label"><?= e($contactMessageLabel) ?></label><textarea class="form-control" name="message" rows="5" required></textarea></div>
                    <div class="col-12"><button class="btn btn-dark rounded-pill px-4" type="submit"><?= e($isEs ? 'Enviar mensaje' : 'Send message') ?></button></div>
                </div>
            </form>
        </div>
    </div>
</section>
