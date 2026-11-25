<?php $cover = media_url($category['cover_image'] ?? '') ?: media_url($site['hero_background_image'] ?? ''); ?>
<section class="category-cover-hero" style="--category-accent: <?= e($category['accent_color'] ?? '#c9853b') ?>; --category-cover: url('<?= e($cover) ?>');">
    <div class="container">
        <div class="category-cover-content">
            <span class="category-cover-icon"><i class="<?= e($category['icon_class'] ?? 'bi bi-megaphone-fill') ?>"></i></span>
            <span class="eyebrow"><?= e($locale === 'es' ? 'ÁREA TEMÁTICA' : 'Strategic area') ?></span>
            <h1><?= e($category['name']) ?></h1>
            <p><?= e($category['description']) ?></p>
            <div class="category-cover-meta">
                <span><?= (int) ($category['total_posts'] ?? 0) ?> <?= e($locale === 'es' ? 'artículos publicados' : 'articles published') ?></span>
                <span><?= e($category['spotlight'] ?? '') ?></span>
            </div>
            <div class="category-cover-actions">
                <a href="#category-feed" class="btn btn-light rounded-pill px-4"><?= e(t('home.latest')) ?></a>
                <a href="<?= e(url_path('/blog?category=' . $category['slug'], $locale)) ?>" class="btn btn-outline-light rounded-pill px-4"><?= e(t('home.search_button')) ?></a>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0" id="category-feed">
    <div class="container">
        <?php if ($leadPost): ?>
            <article class="archive-lead-story mb-4">
                <div class="archive-lead-copy">
                    <div class="post-meta mb-3">
                        <span class="meta-chip" style="--category-accent: <?= e($leadPost['category_color'] ?? '#c9853b') ?>">
                            <i class="<?= e($leadPost['category_icon'] ?? '') ?>"></i> <?= e($leadPost['category_name'] ?? '') ?>
                        </span>
                        <span><?= (int) $leadPost['read_time'] ?> min</span>
                        <span><?= e(date_label($leadPost['published_at'])) ?></span>
                    </div>
                    <h3><a href="<?= e(post_url($leadPost, $locale)) ?>"><?= e($leadPost['title']) ?></a></h3>
                    <p><?= e($leadPost['excerpt']) ?></p>
                    <a href="<?= e(post_url($leadPost, $locale)) ?>" class="post-link"><?= e(t('post.read_article')) ?> <i class="bi bi-arrow-right-short"></i></a>
                </div>
                <?php $leadCover = media_url($leadPost['cover_image'] ?? '') ?: $cover; ?>
                <a class="archive-lead-media" href="<?= e(post_url($leadPost, $locale)) ?>">
                    <img src="<?= e($leadCover) ?>" alt="<?= e($leadPost['title']) ?>" loading="eager" decoding="async">
                </a>
            </article>
        <?php endif; ?>
        <?php if ($posts): ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-xl-4">
                        <?php require __DIR__ . '/partials/post_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3><?= e($locale === 'es' ? 'Esta categoría todavía no tiene más artículos.' : 'This category has no more articles yet.') ?></h3>
            </div>
        <?php endif; ?>
    </div>
</section>
