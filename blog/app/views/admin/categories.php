<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div><span class="eyebrow">Arquitectura editorial</span><h1>Categorías</h1><p>Define color, icono y promesa de cada frente editorial.</p></div>
            <a href="<?= e(url_path('/admin/categories/new')) ?>" class="btn btn-dark rounded-pill px-4">Nueva categoria</a>
        </div>
        <div class="admin-card mb-4"><?php $listPath = '/admin/categories'; $filterLabel = 'Filtrar categorías'; $filterStates = []; $searchHint = 'Nombre, URL o descripción…'; require __DIR__ . '/list_filters.php'; ?></div>
        <?php if (!$categories): ?><div class="admin-card empty-state"><h2 class="h4">Sin categorías en esta vista</h2><p>Prueba otra búsqueda o crea una categoría.</p></div><?php endif; ?>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <?php $systemCategory = system_category_definition((string) ($category['base_slug'] ?? $category['slug'] ?? '')); ?>
                <div class="col-md-6 col-xl-4">
                    <div class="admin-card category-admin-card" style="--category-accent: <?= e($category['accent_color']) ?>">
                        <div class="category-admin-head"><span class="category-card-icon"><i class="<?= e($category['icon_class']) ?>"></i></span><span class="mini-swatch"><?= e($category['accent_color']) ?></span></div>
                        <h2 class="h4 mt-3"><?= e($category['tree_label'] ?? $category['name']) ?></h2>
                        <p><?= e($category['description']) ?></p>
                        <small class="text-body-secondary d-block"><?= e('Slug: ' . ($category['slug'] ?? '')) ?></small>
                        <?php if (!empty($category['name_en']) || !empty($category['slug_en'])): ?>
                            <small class="text-body-secondary d-block"><?= e('EN: ' . (($category['name_en'] ?? '') ?: ($category['base_name'] ?? $category['name'] ?? '')) . ' · ' . (($category['slug_en'] ?? '') ?: ($category['base_slug'] ?? $category['slug'] ?? ''))) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($category['name_es']) || !empty($category['slug_es'])): ?>
                            <small class="text-body-secondary d-block"><?= e('ES: ' . (($category['name_es'] ?? '') ?: ($category['base_name'] ?? $category['name'] ?? '')) . ' · ' . (($category['slug_es'] ?? '') ?: ($category['base_slug'] ?? $category['slug'] ?? ''))) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($category['parent_name'])): ?>
                            <small class="text-body-secondary d-block"><?= e('Padre: ' . $category['parent_name']) ?></small>
                        <?php endif; ?>
                        <small class="text-body-secondary d-block"><?= e('Menu: ' . ((string) ($category['show_in_nav'] ?? '1') === '1' ? 'Visible' : 'Oculto') . ' · Orden ' . ((int) ($category['nav_order'] ?? 0))) ?></small>
                        <?php if ($systemCategory): ?>
                            <small class="text-body-secondary d-block"><?= e('Sistema: ' . ($systemCategory['publish_copy'] ?? 'Se administra desde su modulo del CMS.')) ?></small>
                        <?php endif; ?>
                        <small class="text-body-secondary d-block mb-3"><?= e($category['spotlight']) ?></small>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?= e(url_path('/admin/categories/' . $category['id'] . '/edit')) ?>" class="btn btn-sm btn-dark rounded-pill">Editar</a>
                            <?php if ($systemCategory): ?>
                                <span class="btn btn-sm btn-outline-secondary rounded-pill disabled" aria-disabled="true">Protegida</span>
                            <?php else: ?>
                                <form method="post" action="<?= e(url_path('/admin/categories/' . $category['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminar categoria?');"><?= csrf_input() ?><button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">Eliminar</button></form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php $paginationPath = '/admin/categories'; require __DIR__ . '/pagination.php'; ?>
    </div>
</section>
