<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Gestion editorial</span>
                <h1><?= $caseMode ? 'Casos de estudio' : 'Todos los posts' ?></h1>
                <p><?= $caseMode ? 'Administra breakdowns, resultados y aprendizajes reales.' : 'Controla borradores, destacados, SEO y categorias.' ?></p>
            </div>
            <a href="<?= e(url_path($caseMode ? '/admin/posts/new?category=casos-de-estudio' : '/admin/posts/new')) ?>" class="btn btn-dark rounded-pill px-4"><?= $caseMode ? 'Nuevo caso' : 'Nuevo post' ?></a>
        </div>
        <div class="admin-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0 admin-table">
                    <thead><tr><th>Titulo</th><th>Categoria</th><th>Estado</th><th>SEO</th><th>Comentarios</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><div class="fw-semibold"><?= e($post['title']) ?></div><div class="small text-body-secondary"><?= e($post['slug']) ?></div></td>
                                <td><?= e($post['category_name'] ?? 'Sin categoria') ?></td>
                                <td><span class="badge <?= $post['status'] === 'published' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= e($post['status']) ?></span><?php if ($post['is_featured']): ?> <span class="badge text-bg-dark">Destacado</span><?php endif; ?></td>
                                <td class="small text-body-secondary"><?= e($post['seo_title'] ?: 'Sin SEO title') ?></td>
                                <td><?= (int) ($post['approved_comments'] ?? 0) ?> aprobados</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <?php if ($post['status'] === 'published'): ?><a href="<?= e(post_url($post)) ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Ver</a><?php endif; ?>
                                        <a href="<?= e(url_path('/admin/posts/' . $post['id'] . '/edit')) ?>" class="btn btn-sm btn-dark rounded-pill">Editar</a>
                                        <form method="post" action="<?= e(url_path('/admin/posts/' . $post['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminar este post?');"><?= csrf_input() ?><button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">Eliminar</button></form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
