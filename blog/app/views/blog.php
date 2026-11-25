<section class="page-hero">
    <div class="container">
        <div class="page-hero-panel page-hero-panel-business">
            <span class="eyebrow">Business library</span>
            <h1><?= e(t('blog.title')) ?></h1>
            <p><?= e(t('blog.desc')) ?></p>
        </div>
    </div>
</section>

<section class="section-space pt-0">
    <div class="container">
        <form class="search-panel mb-4" method="get" action="<?= e(url_path('/blog', $locale)) ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label"><?= e(t('blog.search')) ?></label>
                    <input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="SEO, ROAS, funnels, CRO...">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= e(t('blog.category')) ?></label>
                    <select class="form-select" name="category">
                        <option value=""><?= e(t('blog.all')) ?></option>
                        <?php foreach ($allCategories as $category): ?>
                            <option value="<?= e($category['slug']) ?>" <?= $selectedCategory === $category['slug'] ? 'selected' : '' ?>><?= e($category['tree_label'] ?? $category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark w-100" type="submit"><?= e(t('blog.filter')) ?></button>
                </div>
            </div>
        </form>
        <div class="section-head mb-4">
            <div>
                <span class="eyebrow"><?= e(t('blog.results')) ?></span>
                <h2><?= (int) $total ?> <?= e($total === 1 ? t('blog.article') : t('blog.articles')) ?></h2>
            </div>
        </div>
        <?php if ($posts): ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-xl-4">
                        <?php require __DIR__ . '/partials/post_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <nav class="pagination-wrap mt-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <span class="text-body-secondary">Page <?= (int) $page ?> / <?= (int) $totalPages ?></span>
                    <div class="d-flex gap-2">
                        <?php $queryBase = http_build_query(['q' => $search, 'category' => $selectedCategory]); ?>
                        <a class="btn btn-outline-dark rounded-pill <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e(url_path('/blog?' . $queryBase . '&page=' . ($page - 1), $locale)) ?>">Previous</a>
                        <a class="btn btn-dark rounded-pill <?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= e(url_path('/blog?' . $queryBase . '&page=' . ($page + 1), $locale)) ?>">Next</a>
                    </div>
                </div>
            </nav>
        <?php else: ?>
            <div class="empty-state">
                <h3>No articles found</h3>
                <p>Try another category or a broader search.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
