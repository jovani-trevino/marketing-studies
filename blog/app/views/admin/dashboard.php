<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">MARKETING STUDIES / RESUMEN</span>
                <h1>Ideas que merecen<br>ser compartidas.</h1>
                <p>Tu publicación, tu comunidad y el siguiente paso. Todo en un solo lugar.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= e(url_path('/admin/professional')) ?>" class="btn btn-outline-dark rounded-pill px-4">Mi perfil y agenda</a>
                <a href="<?= e(url_path('/admin/posts/new')) ?>" class="btn btn-dark rounded-pill px-4">Nuevo artículo</a>
                <a href="<?= e(url_path('/admin/posts/new?category=casos-de-estudio')) ?>" class="btn btn-outline-dark rounded-pill px-4">Nuevo caso</a>
                <a href="<?= e(url_path('/admin/comments?status=pending')) ?>" class="btn btn-outline-dark rounded-pill px-4">Revisar comentarios</a>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-3"><div class="stat-card"><span>Publicados</span><strong><?= (int) ($stats['published_count'] ?? 0) ?></strong><small>Artículos visibles</small></div></div>
            <div class="col-md-3"><div class="stat-card"><span>Borradores</span><strong><?= (int) ($stats['draft_count'] ?? 0) ?></strong><small>Ideas en proceso</small></div></div>
            <div class="col-md-3"><div class="stat-card"><span>Comentarios</span><strong><?= (int) ($commentStats['total_comments'] ?? 0) ?></strong><small><?= (int) ($commentStats['pending_comments'] ?? 0) ?> por moderar</small></div></div>
            <div class="col-md-3"><div class="stat-card"><span>Newsletter</span><strong><?= (int) ($newsletterStats['active'] ?? 0) ?></strong><small>Suscriptores activos</small></div></div>
        </div>
        <div class="studio-action-grid">
            <a class="studio-action" href="<?= e(url_path('/admin/contacts?status=new')) ?>"><i class="bi bi-inbox" aria-hidden="true"></i><div><strong>Atiende tus mensajes</strong><span><?= (int) ($contactStats['new_count'] ?? 0) ?> consultas nuevas por revisar</span></div></a>
            <a class="studio-action" href="<?= e(url_path('/admin/posts?status=draft')) ?>"><i class="bi bi-pencil-square" aria-hidden="true"></i><div><strong>Retoma tus ideas</strong><span><?= (int) ($stats['draft_count'] ?? 0) ?> borradores para seguir escribiendo</span></div></a>
            <a class="studio-action" href="<?= e(url_path('/admin/seo')) ?>"><i class="bi bi-search-heart" aria-hidden="true"></i><div><strong>Cuida tu visibilidad</strong><span>Revisa portadas, resúmenes y estructura</span></div></a>
            <a class="studio-action" href="<?= e(url_path('/admin/comments?status=pending')) ?>"><i class="bi bi-chat-square-text" aria-hidden="true"></i><div><strong>Escucha a tu comunidad</strong><span><?= (int) ($commentStats['pending_comments'] ?? 0) ?> comentarios pendientes</span></div></a>
        </div>
        <div class="admin-card mt-4">
            <div class="section-head mb-3"><div><span class="eyebrow">Continúa donde lo dejaste</span><h2 class="h4 mb-0">Últimos artículos editados</h2></div></div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 admin-table">
                    <thead><tr><th scope="col">Título</th><th scope="col">Categoría</th><th scope="col">Estado</th><th scope="col">Actualizado</th></tr></thead>
                    <tbody>
                        <?php if (!$recentPosts): ?><tr><td colspan="4"><div class="empty-state compact-empty"><h3>Tu siguiente idea puede empezar aquí</h3><p>Crea un borrador para darle forma a tu primer artículo.</p><a class="btn btn-dark" href="<?= e(url_path('/admin/posts/new')) ?>">Escribir un artículo</a></div></td></tr><?php endif; ?>
                        <?php foreach ($recentPosts as $post): ?>
                            <tr>
                                <td><a href="<?= e(url_path('/admin/posts/' . $post['id'] . '/edit')) ?>" class="fw-semibold text-decoration-none"><?= e($post['title']) ?></a></td>
                                <td><?= e($post['category_name'] ?? 'Sin categoría') ?></td>
                                <td><span class="badge <?= $post['status'] === 'published' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $post['status'] === 'published' ? 'Publicado' : 'Borrador' ?></span></td>
                                <td><?= e(date_label($post['updated_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
