<?php
$cover = media_url($post['cover_image'] ?? '') ?: media_url($GLOBALS['site']['hero_background_image'] ?? '');
$cardAuthorName = $post['author_display_name'] ?? $post['author_name'] ?? 'Jovani';
$cardAuthorSlug = ($post['author_slug'] ?? '') ?: slugify($cardAuthorName);
$cardAuthorAvatar = author_avatar_url($cardAuthorName, $post['author_avatar_url'] ?? null);
$isSpanish = ($GLOBALS['locale'] ?? DEFAULT_LOCALE) === 'es';
$commentLabel = $isSpanish ? 'comentarios' : 'comments';
$ratingLabel = $isSpanish ? 'valoración' : 'rating';
$cardCommentCount = (int) ($post['approved_comment_count'] ?? 0);
$cardRating = (float) ($post['average_rating'] ?? 0);
$cardExcerpt = post_display_excerpt($post, 155);
?>
<article class="post-card">
    <a class="post-card-media" href="<?= e(post_url($post, $GLOBALS['locale'])) ?>">
        <?php if ($cover): ?>
            <img src="<?= e($cover) ?>" alt="<?= e($post['title']) ?>" loading="lazy" decoding="async">
        <?php endif; ?>
        <span class="post-card-cover-meta">
            <?php if (!empty($post['category_name'])): ?>
                <span class="meta-chip" style="--category-accent: <?= e($post['category_color'] ?? '#c9853b') ?>">
                    <i class="<?= e($post['category_icon'] ?? 'bi bi-megaphone-fill') ?>"></i>
                    <?= e($post['category_name']) ?>
                </span>
            <?php endif; ?>
            <?php if ($cardRating > 0): ?><span class="cover-stat-badge"><i class="bi bi-star-fill" aria-hidden="true"></i> <?= number_format($cardRating, 1) ?></span><?php endif; ?>
        </span>
    </a>
    <div class="post-card-body">
        <div class="post-meta">
            <span><?= (int) ($post['read_time'] ?? 1) ?> min</span>
            <span><?= e(date_label($post['published_at'] ?? $post['updated_at'] ?? '')) ?></span>
        </div>
        <h3><a href="<?= e(post_url($post, $GLOBALS['locale'])) ?>"><?= e($post['title']) ?></a></h3>
        <?php if ($cardExcerpt !== ''): ?>
            <p><?= e($cardExcerpt) ?></p>
        <?php endif; ?>
        <a class="post-card-author" href="<?= e(url_path('/author/' . $cardAuthorSlug, $GLOBALS['locale'])) ?>">
            <img src="<?= e($cardAuthorAvatar) ?>" alt="<?= e($cardAuthorName) ?>" loading="lazy" decoding="async">
            <span>
                <strong><?= e($cardAuthorName) ?></strong>
                <small><?= e(date_label($post['published_at'] ?? '')) ?></small>
            </span>
        </a>
        <div class="post-card-footer">
            <?php if ($cardCommentCount > 0 || $cardRating > 0): ?><div class="post-card-signal">
                <?php if ($cardCommentCount > 0): ?>
                <span class="post-card-signal-pill post-card-signal-comments">
                    <span class="post-card-signal-icon" aria-hidden="true"><i class="bi bi-chat-left-text"></i></span>
                    <span class="post-card-signal-copy">
                        <strong><?= $cardCommentCount ?></strong>
                        <small><?= e($commentLabel) ?></small>
                    </span>
                </span>
                <?php endif; ?>
                <?php if ($cardRating > 0): ?>
                <span class="post-card-signal-pill post-card-signal-rating">
                    <span class="post-card-signal-icon" aria-hidden="true"><i class="bi bi-star-fill"></i></span>
                    <span class="post-card-signal-copy">
                        <strong><?= number_format($cardRating, 1) ?></strong>
                        <small><?= e($ratingLabel) ?></small>
                    </span>
                </span>
                <?php endif; ?>
            </div><?php endif; ?>
            <a class="post-link" href="<?= e(post_url($post, $GLOBALS['locale'])) ?>"><?= e(t('post.read_article')) ?> <i class="bi bi-arrow-right-short"></i></a>
        </div>
    </div>
</article>
