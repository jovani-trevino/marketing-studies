<section class="lab-hero" style="--lab-cover: url('<?= e(media_url(CATEGORY_COVER_IMAGES['lab'])) ?>');">
    <div class="container">
        <div class="lab-hero-content">
            <span class="eyebrow">Growth Lab</span>
            <h1><?= e(t('lab.title')) ?></h1>
            <p><?= e(t('lab.desc')) ?></p>
            <a href="#lab-library" class="btn btn-light btn-lg rounded-pill px-4">Resource library <i class="bi bi-arrow-down-short"></i></a>
        </div>
    </div>
</section>

<section class="section-space" id="lab-library">
    <div class="container">
        <?php if ($featuredResource): ?>
            <div class="section-head"><div><span class="eyebrow">Featured material</span><h2><?= e($featuredResource['title']) ?></h2></div></div>
            <article class="lab-featured-card mb-5">
                <a href="<?= e(url_path('/lab/download/' . $featuredResource['id'], $locale)) ?>" class="lab-featured-media">
                    <?php if (!empty($featuredResource['cover_image'])): ?><img src="<?= e(media_url($featuredResource['cover_image'])) ?>" alt="<?= e($featuredResource['title']) ?>" loading="lazy" decoding="async"><?php endif; ?>
                    <span><?= e($featuredResource['resource_type']) ?></span>
                </a>
                <div class="lab-featured-copy">
                    <p><?= e($featuredResource['description']) ?></p>
                    <div class="lab-resource-credit">
                        <?php if (!empty($featuredResource['source_name'])): ?>
                            <i class="bi bi-box-arrow-up-right"></i><span><?= e(t('lab.reference')) ?>: <?= e($featuredResource['source_name']) ?></span>
                        <?php else: ?>
                            <img src="<?= e(author_avatar_url($featuredResource['author_name'] ?: ($site['author_name'] ?? 'Jovani'))) ?>" alt="<?= e($featuredResource['author_name'] ?: 'Jovani') ?>">
                            <span><?= e(t('lab.author')) ?>: <?= e($featuredResource['author_name'] ?: ($site['author_name'] ?? 'Jovani')) ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= e(url_path('/lab/download/' . $featuredResource['id'], $locale)) ?>" class="btn btn-dark rounded-pill px-4"><i class="bi bi-download"></i> <?= e(empty($featuredResource['source_name']) ? t('lab.download') : t('lab.external')) ?></a>
                </div>
            </article>
        <?php endif; ?>
        <div class="section-head"><div><span class="eyebrow">Library</span><h2>Resource library</h2><p><?= e(t('lab.desc')) ?></p></div></div>
        <div class="lab-resource-grid">
            <?php foreach ($resources as $resource): ?>
                <article class="lab-resource-card">
                    <a href="<?= e(url_path('/lab/download/' . $resource['id'], $locale)) ?>" class="lab-resource-media">
                        <?php if (!empty($resource['cover_image'])): ?><img src="<?= e(media_url($resource['cover_image'])) ?>" alt="<?= e($resource['title']) ?>" loading="lazy" decoding="async"><?php endif; ?>
                        <span><?= e($resource['resource_type']) ?></span>
                    </a>
                    <div class="lab-resource-body">
                        <h3><a href="<?= e(url_path('/lab/download/' . $resource['id'], $locale)) ?>"><?= e($resource['title']) ?></a></h3>
                        <p><?= e($resource['description']) ?></p>
                        <div class="lab-resource-footer">
                            <span><?= (int) ($resource['download_count'] ?? 0) ?> downloads</span>
                            <a href="<?= e(url_path('/lab/download/' . $resource['id'], $locale)) ?>" class="post-link"><?= e(empty($resource['source_name']) ? t('lab.download') : t('lab.external')) ?> <i class="bi bi-arrow-right-short"></i></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
