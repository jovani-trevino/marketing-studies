<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header"><div><span class="eyebrow">Moderacion</span><h1>Comentarios</h1><p>Aprueba, rechaza o marca spam.</p></div></div>
        <div class="filter-pills mb-4">
            <?php foreach (['all', 'pending', 'approved', 'rejected', 'spam'] as $item): ?>
                <a href="<?= e(url_path('/admin/comments?status=' . $item)) ?>" class="filter-pill <?= $status === $item ? 'active' : '' ?>"><?= e($item) ?></a>
            <?php endforeach; ?>
        </div>
        <div class="comment-admin-grid">
            <?php foreach ($comments as $comment): ?>
                <article class="admin-card comment-admin-card">
                    <div class="comment-card-head"><div><strong><?= e($comment['author_name']) ?></strong><small class="d-block text-body-secondary"><?= e($comment['author_email']) ?></small></div><?= rating_stars((int) $comment['rating']) ?></div>
                    <p><?= e($comment['body']) ?></p>
                    <div class="small text-body-secondary mb-3">En <a href="<?= e(post_url(['id' => $comment['post_id'], 'slug' => $comment['post_slug'], 'title' => $comment['post_title'], 'category_slug' => $comment['category_slug'] ?? 'marketing'])) ?>"><?= e($comment['post_title']) ?></a> · <?= e(date_label($comment['created_at'])) ?></div>
                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                        <span class="badge <?= $comment['status'] === 'approved' ? 'text-bg-success' : ($comment['status'] === 'pending' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= e($comment['status']) ?></span>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach (['approved', 'pending', 'rejected', 'spam'] as $next): ?>
                                <?php if ($next !== $comment['status']): ?>
                                    <form method="post" action="<?= e(url_path('/admin/comments/' . $comment['id'] . '/status')) ?>"><?= csrf_input() ?><input type="hidden" name="status" value="<?= e($next) ?>"><button class="btn btn-sm btn-outline-dark rounded-pill" type="submit"><?= e($next) ?></button></form>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <form method="post" action="<?= e(url_path('/admin/comments/' . $comment['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminar comentario?');"><?= csrf_input() ?><button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">Eliminar</button></form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
