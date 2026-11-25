<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Panel editorial</span>
                <h1>Dashboard del blog</h1>
                <p>Controla contenido, comunidad, materiales descargables y monetizacion.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= e(url_path('/admin/posts/new')) ?>" class="btn btn-dark rounded-pill px-4">Nuevo post</a>
                <a href="<?= e(url_path('/admin/posts/new?category=casos-de-estudio')) ?>" class="btn btn-outline-dark rounded-pill px-4">Nuevo caso</a>
                <a href="<?= e(url_path('/admin/comments?status=pending')) ?>" class="btn btn-outline-dark rounded-pill px-4">Revisar comentarios</a>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-3"><div class="stat-card"><span>Publicados</span><strong><?= (int) ($stats['published_count'] ?? 0) ?></strong><small>Articulos visibles</small></div></div>
            <div class="col-md-3"><div class="stat-card"><span>Borradores</span><strong><?= (int) ($stats['draft_count'] ?? 0) ?></strong><small>Ideas en proceso</small></div></div>
            <div class="col-md-3"><div class="stat-card"><span>Comentarios</span><strong><?= (int) ($commentStats['total_comments'] ?? 0) ?></strong><small><?= (int) ($commentStats['pending_comments'] ?? 0) ?> por moderar</small></div></div>
            <div class="col-md-3"><div class="stat-card"><span>Newsletter</span><strong><?= (int) ($newsletterStats['active'] ?? 0) ?></strong><small>Suscriptores activos</small></div></div>
        </div>
        <div class="admin-card mt-4">
            <div class="section-head mb-3"><div><span class="eyebrow">Actividad reciente</span><h2 class="h4 mb-0">Ultimos posts editados</h2></div></div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 admin-table">
                    <thead><tr><th>Titulo</th><th>Categoria</th><th>Estado</th><th>Actualizado</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentPosts as $post): ?>
                            <tr>
                                <td><a href="<?= e(url_path('/admin/posts/' . $post['id'] . '/edit')) ?>" class="fw-semibold text-decoration-none"><?= e($post['title']) ?></a></td>
                                <td><?= e($post['category_name'] ?? 'Sin categoria') ?></td>
                                <td><span class="badge <?= $post['status'] === 'published' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= e($post['status']) ?></span></td>
                                <td><?= e(date_label($post['updated_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
