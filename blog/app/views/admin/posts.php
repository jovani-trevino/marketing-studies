<?php
$filters = $filters ?? ['q' => '', 'status' => '', 'category' => ''];
$caseMode = (bool) ($caseMode ?? false);
$listPath = $caseMode ? '/admin/case-studies' : '/admin/posts';
?>
<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Gestión editorial</span>
                <h1><?= $caseMode ? 'Casos de estudio' : 'Tu biblioteca de ideas' ?></h1>
                <p><?= $caseMode ? 'Documenta resultados, decisiones y aprendizajes que puedas compartir.' : 'Artículos, borradores y versiones de una publicación en movimiento.' ?></p>
            </div>
            <a href="<?= e(url_path($caseMode ? '/admin/posts/new?category=casos-de-estudio' : '/admin/posts/new')) ?>" class="btn btn-dark"><i class="bi bi-plus-lg" aria-hidden="true"></i> <?= $caseMode ? 'Nuevo caso' : 'Nuevo artículo' ?></a>
        </div>
        <div class="admin-card admin-list-card">
            <form class="admin-filter-bar" method="get" action="<?= e(url_path($listPath)) ?>" role="search" aria-label="Filtrar artículos">
                <div class="admin-search-field"><label for="admin-post-search">Buscar</label><input id="admin-post-search" type="search" name="q" class="form-control" value="<?= e($filters['q'] ?? '') ?>" placeholder="Título o contenido…"></div>
                <div><label for="admin-post-status">Estado</label><select id="admin-post-status" class="form-select" name="status"><option value="">Todos los estados</option><option value="published"<?= ($filters['status'] ?? '') === 'published' ? ' selected' : '' ?>>Publicado</option><option value="draft"<?= ($filters['status'] ?? '') === 'draft' ? ' selected' : '' ?>>Borrador</option></select></div>
                <?php if (!$caseMode): ?>
                    <div><label for="admin-post-category">Categoría</label><select id="admin-post-category" class="form-select" name="category"><option value="">Todas las categorías</option><?php foreach (($categories ?? []) as $category): ?><option value="<?= e($category['slug']) ?>"<?= ($filters['category'] ?? '') === $category['slug'] ? ' selected' : '' ?>><?= e($category['tree_label'] ?? $category['name']) ?></option><?php endforeach; ?></select></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-dark">Filtrar</button>
                <?php if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '' || (!$caseMode && ($filters['category'] ?? '') !== '')): ?><a class="btn btn-outline-dark" href="<?= e(url_path($listPath)) ?>">Limpiar</a><?php endif; ?>
            </form>
            <?php if ($posts): ?>
                <div class="table-responsive" tabindex="0" role="region" aria-label="Lista de artículos">
                    <table class="table align-middle mb-0 admin-table">
                        <thead><tr><th scope="col">Artículo</th><th scope="col">Categoría</th><th scope="col">Estado</th><th scope="col">SEO</th><th scope="col">Comentarios</th><th scope="col" class="text-end">Acciones</th></tr></thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td class="admin-title-cell"><a href="<?= e(url_path('/admin/posts/' . $post['id'] . '/edit')) ?>" class="fw-semibold"><?= e($post['title']) ?></a><div class="small text-body-secondary admin-slug"><?= e($post['slug']) ?></div></td>
                                    <td><?= e($post['category_name'] ?? 'Sin categoría') ?></td>
                                    <td><span class="badge <?= $post['status'] === 'published' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $post['status'] === 'published' ? 'Publicado' : 'Borrador' ?></span><?php if ($post['is_featured']): ?> <span class="badge text-bg-dark">Destacado</span><?php endif; ?></td>
                                    <td class="small text-body-secondary admin-seo-cell"><?= e($post['seo_title'] ?: 'Título del artículo') ?></td>
                                    <td class="small"><?= (int) ($post['approved_comments'] ?? 0) ?> aprobados</td>
                                    <td class="text-end"><div class="admin-row-actions">
                                        <?php if ($post['status'] === 'published'): ?><a href="<?= e(post_url($post)) ?>" class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener">Ver <span class="visually-hidden"><?= e($post['title']) ?></span></a><?php endif; ?>
                                        <a href="<?= e(url_path('/admin/posts/' . $post['id'] . '/edit')) ?>" class="btn btn-sm btn-dark">Editar <span class="visually-hidden"><?= e($post['title']) ?></span></a>
                                        <form method="post" action="<?= e(url_path('/admin/posts/' . $post['id'] . '/delete')) ?>" onsubmit="return confirm('¿Eliminar este artículo de forma permanente? Esta acción no se puede deshacer.');"><?= csrf_input() ?><button class="btn btn-sm btn-outline-danger" type="submit">Eliminar <span class="visually-hidden"><?= e($post['title']) ?></span></button></form>
                                    </div></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state compact-empty"><i class="bi bi-journal-text" aria-hidden="true"></i><h2 class="h4">No hay artículos en esta vista</h2><p>Prueba otra búsqueda o comienza con una idea nueva.</p><a href="<?= e(url_path('/admin/posts/new')) ?>" class="btn btn-dark">Escribir un artículo</a></div>
            <?php endif; ?>
            <?php $paginationPath = $listPath; require __DIR__ . '/pagination.php'; ?>
        </div>
    </div>
</section>
