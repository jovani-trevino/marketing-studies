<?php
$cover = media_url($post['cover_image'] ?? '') ?: media_url($site['hero_background_image'] ?? '');
$shareUrl = $canonicalUrl ?? current_url();
$authorName = $post['author_display_name'] ?? $post['author_name'] ?? ($site['author_name'] ?? 'Jovani');
$authorSlug = ($post['author_slug'] ?? '') ?: slugify($authorName);
$authorAvatar = author_avatar_url($authorName, $post['author_avatar_url'] ?? null);
$authorRole = trim((string) ($post['author_role'] ?? '')) ?: t('post.intent');
$articleContent = render_article_content((string) ($post['content_html'] ?? ''), (string) ($post['content_markdown'] ?? ''));
// Normalize document headings for one page title and a stable reading index.
$articleToc = [];
if (class_exists(DOMDocument::class)) {
    $previousErrors = libxml_use_internal_errors(true);
    $readerDocument = new DOMDocument('1.0', 'UTF-8');
    $readerDocument->loadHTML('<?xml encoding="utf-8"?><div id="ms-reading-root">' . $articleContent . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    $readerRoot = $readerDocument->getElementById('ms-reading-root');
    if ($readerRoot) {
        foreach (iterator_to_array($readerRoot->getElementsByTagName('h1')) as $heading) {
            $text = preg_replace('/\s+/u', ' ', trim($heading->textContent));
            $titleText = preg_replace('/\s+/u', ' ', trim((string) $post['title']));
            if ($text === $titleText) { $heading->parentNode?->removeChild($heading); continue; }
            $replacement = $readerDocument->createElement('h2');
            while ($heading->firstChild) $replacement->appendChild($heading->firstChild);
            $heading->parentNode?->replaceChild($replacement, $heading);
        }
        foreach ($readerRoot->getElementsByTagName('*') as $heading) {
            if (!in_array(strtolower($heading->tagName), ['h2','h3'], true) || trim($heading->textContent) === '') continue;
            $id = 'section-' . (count($articleToc) + 1);
            $heading->setAttribute('id', $id);
            $articleToc[] = ['id'=>$id,'label'=>trim($heading->textContent),'level'=>$heading->tagName];
        }
        $articleContent = '';
        foreach ($readerRoot->childNodes as $node) $articleContent .= $readerDocument->saveHTML($node);
    }
    libxml_clear_errors(); libxml_use_internal_errors($previousErrors);
}
$articleSections = article_content_sections($articleContent);
$sectionCount = count($articleSections['sections'] ?? []);
$heroExcerpt = post_display_excerpt($post, 240);
$articleHeroStyle = '--category-accent: ' . e($post['category_color'] ?? '#c9853b') . ';';
if ($cover) {
    $articleHeroStyle .= " --article-cover-image: url('" . e($cover) . "');";
}
$heroSnapshotLabels = $locale === 'es'
    ? array(
        'cover' => 'Articulo destacado',
        'headline' => 'Panorama rapido',
        'topic' => 'Tema',
        'read_time' => 'Lectura',
        'published' => 'Publicado',
        'format' => 'Formato',
    )
    : array(
        'cover' => 'Featured article',
        'headline' => 'Quick brief',
        'topic' => 'Topic',
        'read_time' => 'Read time',
        'published' => 'Published',
        'format' => 'Format',
    );
$articleFormat = $sectionCount > 0
    ? ($locale === 'es' ? $sectionCount . ' secciones' : $sectionCount . ' sections')
    : ($locale === 'es' ? 'Lectura larga' : 'Long read');
$heroSnapshot = array(
    array('label' => $heroSnapshotLabels['topic'], 'value' => (string) ($post['category_name'] ?? '')),
    array('label' => $heroSnapshotLabels['read_time'], 'value' => (int) ($post['read_time'] ?? 0) . ' min'),
    array('label' => $heroSnapshotLabels['published'], 'value' => date_label($post['published_at'] ?? '')),
    array('label' => $heroSnapshotLabels['format'], 'value' => $articleFormat),
);
$heroMetaItems = array_values(array_filter(array(
    array('icon' => $post['category_icon'] ?? '', 'label' => (string) ($post['category_name'] ?? '')),
    array('icon' => 'bi bi-clock', 'label' => (int) ($post['read_time'] ?? 0) . ' min'),
    array('icon' => 'bi bi-calendar3', 'label' => date_label($post['published_at'] ?? '')),
    array('icon' => 'bi bi-chat-left-dots', 'label' => (int) ($summary['total_comments'] ?? 0) . ' ' . t('post.comments')),
), static function (array $item): bool {
    return trim((string) ($item['label'] ?? '')) !== '';
}));
$primaryShareItems = array(
    array('type' => 'native', 'label' => t('post.share'), 'icon' => 'bi bi-share-fill'),
    array('type' => 'link', 'label' => 'LinkedIn', 'icon' => 'bi bi-linkedin', 'url' => $shareLinks['linkedin'] ?? ''),
    array('type' => 'link', 'label' => 'WhatsApp', 'icon' => 'bi bi-whatsapp', 'url' => $shareLinks['whatsapp'] ?? ''),
);
$secondaryShareItems = array(
    array('type' => 'link', 'label' => 'Facebook', 'icon' => 'bi bi-facebook', 'url' => $shareLinks['facebook'] ?? ''),
    array('type' => 'link', 'label' => 'X', 'icon' => 'bi bi-twitter-x', 'url' => $shareLinks['x'] ?? ''),
    array('type' => 'native', 'label' => ($locale === 'es' ? 'Compartir enlace' : 'Share link'), 'icon' => 'bi bi-link-45deg'),
);
$extraScripts = '';
if (setting_is_enabled($site['recaptcha_enabled'] ?? '0') && !empty($site['recaptcha_site_key'])) {
    $extraScripts = '<script>window.onRecaptchaLoaded=function(){if(window.GBRecaptcha){window.GBRecaptcha.render();}}</script><script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoaded&render=explicit" async defer></script>';
}
?>
<section class="article-hero">
    <div class="container article-page-container">
        <div class="article-hero-shell" style="<?= $articleHeroStyle ?>">
            <?php if ($cover): ?>
                <img class="article-hero-bg-image" src="<?= e($cover) ?>" alt="" loading="eager" decoding="async" fetchpriority="high" aria-hidden="true">
            <?php endif; ?>
            <div class="article-hero-content">
                <div class="article-hero-heading">
                    <div class="post-meta mb-3" aria-label="<?= e($heroSnapshotLabels['headline']) ?>">
                        <?php foreach ($heroMetaItems as $index => $item): ?>
                            <?php if ($index === 0): ?>
                                <span class="meta-chip" style="--category-accent: <?= e($post['category_color'] ?? '#c9853b') ?>"><i class="<?= e((string) ($item['icon'] ?? '')) ?>"></i> <?= e((string) ($item['label'] ?? '')) ?></span>
                            <?php else: ?>
                                <span><i class="<?= e((string) ($item['icon'] ?? '')) ?>"></i> <?= e((string) ($item['label'] ?? '')) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <h1 class="article-title"><?= e($post['title']) ?></h1>
                    <?php if ($heroExcerpt !== ''): ?>
                        <p class="article-excerpt"><?= e($heroExcerpt) ?></p>
                    <?php endif; ?>
                </div>
                <div class="article-hero-footer">
                    <a class="article-author-line" href="<?= e(url_path('/author/' . $authorSlug, $locale)) ?>">
                        <img src="<?= e($authorAvatar) ?>" alt="<?= e($authorName) ?>" loading="lazy" decoding="async">
                        <span><strong><?= e(t('post.by') . ' ' . $authorName) ?></strong><small><?= e($authorRole) ?></small></span>
                    </a>
                    <div class="article-meta-strip">
                        <div class="article-share-inline">
                            <?php foreach ($primaryShareItems as $item): ?>
                                <?php if (($item['type'] ?? '') === 'native'): ?>
                                    <button type="button" class="share-chip share-chip-button" data-share-native data-share-title="<?= e($post['title']) ?>" data-share-url="<?= e($shareUrl) ?>" aria-label="<?= e((string) ($item['label'] ?? t('post.share'))) ?>" title="<?= e((string) ($item['label'] ?? t('post.share'))) ?>"><i class="<?= e((string) ($item['icon'] ?? 'bi bi-share-fill')) ?>"></i><span class="visually-hidden"><?= e((string) ($item['label'] ?? t('post.share'))) ?></span></button>
                                <?php elseif (!empty($item['url'])): ?>
                                    <a href="<?= e((string) $item['url']) ?>" target="_blank" rel="noopener noreferrer" class="share-chip" aria-label="<?= e((string) ($item['label'] ?? 'Share')) ?>" title="<?= e((string) ($item['label'] ?? 'Share')) ?>"><i class="<?= e((string) ($item['icon'] ?? 'bi bi-link-45deg')) ?>"></i></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <details class="article-share-more">
                                <summary class="share-chip share-chip-button" aria-label="<?= e($locale === 'es' ? 'Más redes' : 'More networks') ?>" title="<?= e($locale === 'es' ? 'Más redes' : 'More networks') ?>">
                                    <i class="bi bi-three-dots" aria-hidden="true"></i>
                                </summary>
                                <div class="article-share-more-menu">
                                    <?php foreach ($secondaryShareItems as $item): ?>
                                        <?php if (($item['type'] ?? '') === 'native'): ?>
                                            <button type="button" class="share-chip share-chip-button" data-share-native data-share-title="<?= e($post['title']) ?>" data-share-url="<?= e($shareUrl) ?>" aria-label="<?= e((string) ($item['label'] ?? t('post.share'))) ?>" title="<?= e((string) ($item['label'] ?? t('post.share'))) ?>"><i class="<?= e((string) ($item['icon'] ?? 'bi bi-share-fill')) ?>"></i><span class="visually-hidden"><?= e((string) ($item['label'] ?? t('post.share'))) ?></span></button>
                                        <?php elseif (!empty($item['url'])): ?>
                                            <a href="<?= e((string) $item['url']) ?>" target="_blank" rel="noopener noreferrer" class="share-chip" aria-label="<?= e((string) ($item['label'] ?? 'Share')) ?>" title="<?= e((string) ($item['label'] ?? 'Share')) ?>"><i class="<?= e((string) ($item['icon'] ?? 'bi bi-link-45deg')) ?>"></i></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0 article-reading-section">
    <div class="container article-page-container">
        <div class="article-shell article-shell-wide">
            <div class="ms-reading-grid<?= !$articleToc ? ' ms-reading-no-toc' : '' ?>">
                <aside class="ms-article-sidebar">
                    <?php if ($articleToc): ?><details open><summary><?= $locale === 'es' ? 'En este artículo' : 'In this article' ?></summary><nav aria-label="<?= $locale === 'es' ? 'Índice del artículo' : 'Article contents' ?>"><?php foreach ($articleToc as $item): ?><a class="<?= $item['level'] === 'h3' ? 'ms-toc-sub' : '' ?>" href="#<?= e($item['id']) ?>"><?= e($item['label']) ?></a><?php endforeach; ?></nav></details><?php endif; ?>
                    <p><i class="bi bi-book" aria-hidden="true"></i><?= $locale === 'es' ? 'Un espacio para leer con calma y pensar con profundidad.' : 'A space to read carefully and think deeply.' ?></p>
                </aside>
                <div><article class="article-body" id="article-body"><?= $articleContent ?></article>
                <div class="ms-citation"><p><?= $locale === 'es' ? '¿Te resultó útil? Comparte la lectura y conserva la referencia.' : 'Found this useful? Share the reading and keep the reference.' ?></p><button class="btn btn-outline-dark" type="button" data-copy-citation data-author="<?= e($authorName) ?>" data-year="<?= e(substr((string) ($post['published_at'] ?? ''),0,4)) ?>" data-title="<?= e($post['title']) ?>"><i class="bi bi-copy" aria-hidden="true"></i><?= $locale === 'es' ? 'Copiar referencia' : 'Copy citation' ?></button></div></div>
            </div>
            <section class="comment-zone" id="comentarios">
                <div class="section-head">
                    <div>
                        <span class="eyebrow"><?= $locale === 'es' ? 'COMUNIDAD Y CONVERSACIÓN' : 'COMMUNITY & CONVERSATION' ?></span>
                        <h2><?= e(t('post.comments_title')) ?></h2>
                    </div>
                    <?php if ((int) ($summary['total_comments'] ?? 0) > 0): ?><div class="comment-summary-pill">
                        <?= rating_stars((float) ($summary['average_rating'] ?? 0), ((int) ($summary['total_comments'] ?? 0)) === 0) ?>
                        <span><?= number_format((float) ($summary['average_rating'] ?? 0), 1) ?> / 5</span>
                    </div><?php endif; ?>
                </div>
                <div class="comment-layout comment-layout-clean">
                    <div class="comment-form-card comment-form-card-clean">
                        <span class="eyebrow"><?= e(t('post.leave')) ?></span>
                        <h3><?= e(t('post.rate_article')) ?></h3>
                        <p><?= e($site['comments_intro'] ?? '') ?></p>
                        <form method="post" action="<?= e(post_comment_url($post, $locale)) ?>" class="comment-form">
                            <?= csrf_input() ?>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label" for="comment-name"><?= e(t('post.name')) ?></label><input id="comment-name" autocomplete="name" maxlength="150" type="text" name="author_name" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label" for="comment-email"><?= e(t('post.email')) ?></label><input id="comment-email" autocomplete="email" maxlength="254" type="email" name="author_email" class="form-control" required></div>
                                <div class="col-12">
                                    <label class="form-label"><?= e(t('post.your_rating')) ?></label>
                                    <div class="rating-input-group">
                                        <?php for ($value = 5; $value >= 1; $value--): ?>
                                            <input type="radio" class="rating-radio" name="rating" id="rating-<?= $value ?>" value="<?= $value ?>" <?= $value === 5 ? 'checked' : '' ?>>
                                            <label class="rating-label" for="rating-<?= $value ?>"><i class="bi bi-star-fill" aria-hidden="true"></i><span class="visually-hidden"><?= $value ?> <?= $locale === 'es' ? ($value === 1 ? 'estrella' : 'estrellas') : ($value === 1 ? 'star' : 'stars') ?></span></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="col-12"><label class="form-label" for="comment-body"><?= e(t('post.comment')) ?></label><textarea id="comment-body" maxlength="10000" name="body" rows="5" class="form-control" required placeholder="<?= e(t('post.comment_placeholder')) ?>"></textarea></div>
                                <?php if (setting_is_enabled($site['recaptcha_enabled'] ?? '0') && !empty($site['recaptcha_site_key'])): ?><div class="col-12">
                                    <div class="recaptcha-panel">
                                        <div class="recaptcha-panel-copy">
                                            <span><i class="bi bi-shield-check"></i></span>
                                            <div><strong><?= e(t('post.recaptcha_title')) ?></strong><small><?= e(setting_is_enabled($site['recaptcha_enabled'] ?? '0') ? t('post.recaptcha_active') : t('post.recaptcha_ready')) ?></small></div>
                                        </div>
                                        <?php if (setting_is_enabled($site['recaptcha_enabled'] ?? '0') && !empty($site['recaptcha_site_key'])): ?>
                                            <div id="recaptcha-root" class="recaptcha-box" data-sitekey="<?= e($site['recaptcha_site_key']) ?>"></div>
                                        <?php endif; ?>
                                    </div>
                                </div><?php endif; ?>
                            </div>
                            <div class="comment-form-footer">
                                <small class="text-body-secondary"><?= e(t('post.moderation')) ?></small>
                                <button type="submit" class="btn btn-dark rounded-pill px-4"><?= e(t('post.submit_comment')) ?></button>
                            </div>
                        </form>
                    </div>
                    <div class="comment-list-card comment-list-card-clean">
                        <div class="comment-list-head"><span class="eyebrow"><?= e(t('post.community')) ?></span><h3><?= (int) ($summary['total_comments'] ?? 0) ?> <?= e(t('post.comments')) ?></h3></div>
                        <?php if ($comments): ?>
                            <div class="comment-stream">
                                <?php foreach ($comments as $comment): ?>
                                    <article class="comment-card">
                                        <div class="comment-card-head"><div><strong><?= e($comment['author_name']) ?></strong><small><?= e(date_label($comment['created_at'])) ?></small></div><?= rating_stars((int) $comment['rating']) ?></div>
                                        <p><?= e($comment['body']) ?></p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state compact-empty"><h3><?= e(t('post.no_comments_title')) ?></h3><p><?= e(t('post.no_comments_desc')) ?></p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>

<?php if ($related): ?>
<section class="section-space pt-0">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow"><?= $locale === 'es' ? 'SIGUE EXPLORANDO' : 'KEEP EXPLORING' ?></span><h2><?= e(t('post.related')) ?></h2></div></div>
        <div class="row g-4">
            <?php foreach ($related as $post): ?>
                <div class="col-md-4"><?php require __DIR__ . '/partials/post_card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
