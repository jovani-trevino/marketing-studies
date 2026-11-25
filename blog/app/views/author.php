<?php
$authorData = is_array($authorRecord) ? $authorRecord : [];
$authorName = $authorData['name'] ?? $author;
$authorRole = trim((string) ($authorData['role'] ?? ''));
$authorHeadline = trim((string) ($authorData['headline'] ?? ''));
$authorBio = trim((string) ($authorData['bio'] ?? '')) ?: t('author.about_desc');
$authorAvatar = author_avatar_url($authorName, $authorData['avatar_url'] ?? null);
$authorAreas = parse_tag_list((string) ($authorData['expertise_areas'] ?? ''));
$authorProfiles = parse_tag_list((string) ($authorData['profile_labels'] ?? ''));
$authorHighlights = parse_line_list((string) ($authorData['highlights'] ?? ''));
$authorOrcidId = orcid_normalize_id((string) ($authorData['orcid_id'] ?? ''));
$authorOrcidUrl = $authorOrcidId !== '' ? orcid_profile_url($authorOrcidId) : '';
$authorTopicsMap = [];
foreach ($posts as $post) {
    $topic = trim((string) ($post['category_name'] ?? ''));
    if ($topic !== '') {
        $authorTopicsMap[$topic] = $topic;
    }
}
$authorTopics = array_values($authorTopicsMap);
if (!$authorAreas && $authorTopics) {
    $authorAreas = array_slice($authorTopics, 0, 6);
}
$totalPosts = (int) ($stats['total_posts'] ?? count($posts));
$totalReadTime = (int) ($stats['total_read_time'] ?? 0);
$totalCategories = (int) ($stats['total_categories'] ?? count($authorTopics));
$featuredPosts = (int) ($stats['featured_posts'] ?? 0);
$averageReadTime = $totalPosts > 0 ? (int) max(1, ceil($totalReadTime / $totalPosts)) : 0;
$authorBioParagraphs = preg_split('/\R{2,}/', str_replace(["\r\n", "\r"], "\n", $authorBio)) ?: [];
$authorBioParagraphs = array_values(array_filter(array_map('trim', $authorBioParagraphs)));
if (!$authorBioParagraphs) {
    $authorBioParagraphs = [$authorBio];
}
$professionalLinks = author_visible_professional_links($authorData);
$authorHasLinks = $professionalLinks !== [];
?>
<section class="page-hero author-hero">
    <div class="container">
        <div class="author-profile-shell">
            <aside class="author-profile-aside">
                <div class="author-hero-avatar author-hero-photo">
                    <img src="<?= e($authorAvatar) ?>" alt="<?= e($authorName) ?>" loading="eager" decoding="async">
                </div>
                <div class="author-profile-kicker">
                    <span class="eyebrow"><?= e(t('author.eyebrow')) ?></span>
                    <?php if ($authorRole !== ''): ?><span class="author-role-pill"><?= e($authorRole) ?></span><?php endif; ?>
                </div>
                <h1><?= e($authorName) ?></h1>
                <?php if ($authorHeadline !== ''): ?><p class="author-headline"><?= e($authorHeadline) ?></p><?php endif; ?>
                <?php if ($authorOrcidId !== ''): ?>
                    <a class="author-orcid-badge" href="<?= e($authorOrcidUrl) ?>" target="_blank" rel="me noopener noreferrer" aria-label="ORCID iD <?= e($authorOrcidId) ?>">
                        <span>iD</span>
                        <strong><?= e($authorOrcidId) ?></strong>
                    </a>
                <?php endif; ?>
                <?php if ($authorProfiles): ?>
                    <div class="author-pill-list">
                        <?php foreach ($authorProfiles as $profile): ?>
                            <span class="author-pill author-pill-strong"><?= e($profile) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="author-metric-row author-metric-row-rich">
                    <span><strong><?= $totalPosts ?></strong><small><?= e(t('author.posts_stat')) ?></small></span>
                    <span><strong><?= $totalReadTime ?></strong><small><?= e(t('author.minutes_stat')) ?></small></span>
                    <span><strong><?= $totalCategories ?></strong><small><?= e(t('author.categories_stat')) ?></small></span>
                    <span><strong><?= $averageReadTime ?></strong><small><?= e(t('author.avg_read_stat')) ?></small></span>
                </div>
                <?php if ($authorHasLinks): ?>
                    <div class="author-public-links">
                        <?php foreach ($professionalLinks as $link): ?>
                            <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($link['label']) ?>" title="<?= e($link['label']) ?>"><i class="<?= e($link['icon']) ?>"></i></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </aside>
            <div class="author-profile-main">
                <section class="author-bio-card">
                    <span class="eyebrow"><?= e(t('author.about_title')) ?></span>
                    <div class="author-bio-flow">
                        <?php foreach ($authorBioParagraphs as $paragraph): ?>
                            <p><?= e($paragraph) ?></p>
                        <?php endforeach; ?>
                    </div>
                </section>
                <div class="author-detail-grid">
                    <?php if ($authorAreas): ?>
                        <section class="author-detail-card">
                            <span class="eyebrow"><?= e(t('author.expertise_title')) ?></span>
                            <h2><?= e(t('author.expertise_heading')) ?></h2>
                            <div class="author-pill-list">
                                <?php foreach ($authorAreas as $area): ?>
                                    <span class="author-pill"><?= e($area) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    <?php if ($authorProfiles): ?>
                        <section class="author-detail-card">
                            <span class="eyebrow"><?= e(t('author.profiles_title')) ?></span>
                            <h2><?= e(t('author.profiles_heading')) ?></h2>
                            <div class="author-pill-list">
                                <?php foreach ($authorProfiles as $profile): ?>
                                    <span class="author-pill author-pill-strong"><?= e($profile) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    <?php if ($authorHighlights): ?>
                        <section class="author-detail-card">
                            <span class="eyebrow"><?= e(t('author.highlights_title')) ?></span>
                            <h2><?= e(t('author.highlights_heading')) ?></h2>
                            <ul class="author-highlight-list">
                                <?php foreach ($authorHighlights as $highlight): ?>
                                    <li><?= e($highlight) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                    <?php if ($authorTopics): ?>
                        <section class="author-detail-card">
                            <span class="eyebrow"><?= e(t('author.topics_title')) ?></span>
                            <h2><?= e(t('author.topics_heading')) ?></h2>
                            <div class="author-pill-list">
                                <?php foreach (array_slice($authorTopics, 0, 8) as $topic): ?>
                                    <span class="author-pill"><?= e($topic) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    <?php if ($featuredPosts > 0): ?>
                        <section class="author-detail-card author-detail-card-spotlight">
                            <span class="eyebrow"><?= e(t('author.output_title')) ?></span>
                            <h2><?= e(t('author.output_heading')) ?></h2>
                            <p><?= e(t('author.output_copy')) ?></p>
                            <div class="author-output-stat">
                                <strong><?= $featuredPosts ?></strong>
                                <small><?= e(t('author.featured_stat')) ?></small>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space pt-0">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow"><?= e(t('author.eyebrow')) ?></span><h2><?= e(t('author.latest')) ?></h2></div></div>
        <?php if ($posts): ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-xl-4"><?php require __DIR__ . '/partials/post_card.php'; ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3><?= e(t('author.no_posts_title')) ?></h3>
                <p><?= e(t('author.no_posts_desc')) ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>
