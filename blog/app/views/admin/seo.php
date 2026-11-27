<section class="section-space"><div class="container">
    <?php require __DIR__ . '/nav.php'; ?>
    <div class="admin-header"><div><span class="eyebrow">PUBLICAR CON CONFIANZA</span><h1>SEO y visibilidad.</h1><p>Lo técnico se prepara automáticamente. Aquí puedes cuidar lo que hace único a cada artículo.</p></div><a class="btn btn-dark" href="<?= e(url_path('/admin/posts/new')) ?>">Nuevo artículo</a></div>
    <div class="studio-automation-grid">
        <?php foreach ([['bi-card-text','Metadatos','Título y descripción a partir del contenido, con opción de personalizarlos.'],['bi-diagram-3','Sitemap dinámico','Incluye artículos publicados y sus fechas de actualización.'],['bi-code-square','Datos estructurados','Autor, fechas, idioma y portada de cada artículo publicado.'],['bi-shield-check','Control de indexación','CMS y búsquedas internas con noindex; URL canónica sin parámetros de seguimiento.']] as [$icon,$heading,$copy]): ?>
        <div class="admin-card studio-automation"><i class="bi <?= e($icon) ?>" aria-hidden="true"></i><span class="studio-tag">Automático</span><h2 class="h5"><?= e($heading) ?></h2><p><?= e($copy) ?></p></div>
        <?php endforeach; ?>
    </div>
    <div class="admin-card mt-4"><div class="studio-card-heading"><div><span class="eyebrow">REVISIÓN EDITORIAL</span><h2 class="h4">Contenido listo para descubrir</h2><p class="text-body-secondary mb-0">Sugerencias sobre tus artículos; no es una medición de posicionamiento.</p></div><span class="studio-tag"><?= (int) $total ?> artículos</span></div>
        <?php $listPath = '/admin/seo'; $filterLabel = 'Filtrar revisión editorial'; $filterStates = ['draft'=>'Borrador','published'=>'Publicado']; $searchHint = 'Título, extracto o URL…'; require __DIR__ . '/list_filters.php'; ?>
        <?php if (!$posts): ?><div class="empty-state"><h3>Sin artículos en esta vista</h3><p>Cuando crees un artículo, podrás revisar aquí su preparación editorial.</p><a class="btn btn-dark" href="<?= e(url_path('/admin/posts/new')) ?>">Crear artículo</a></div><?php endif; ?>
        <div class="studio-review-list">
        <?php foreach ($posts as $item): $issues = seo_post_review($item); ?>
            <article class="studio-review-row"><div><span class="studio-tag <?= $issues ? 'needs-review' : 'is-ready' ?>"><?= $issues ? count($issues) . (count($issues) === 1 ? ' sugerencia' : ' sugerencias') : 'Revisión básica completa' ?></span><h3 class="h6 mt-2 mb-1"><?= e($item['title']) ?></h3><small class="text-body-secondary"><?= $item['status'] === 'published' ? 'Publicado' : 'Borrador · fuera del sitemap' ?></small><?php if ($issues): ?><ul class="studio-review-issues"><?php foreach ($issues as $issue): ?><li><?= e($issue) ?></li><?php endforeach; ?></ul><?php endif; ?></div><a class="btn btn-outline-dark" href="<?= e(url_path('/admin/posts/' . $item['id'] . '/edit')) ?>">Editar<span class="visually-hidden"> <?= e($item['title']) ?></span></a></article>
        <?php endforeach; ?>
        </div>
        <?php $paginationPath = '/admin/seo'; require __DIR__ . '/pagination.php'; ?>
    </div>
    <div class="studio-resource-links"><a href="<?= e(url_path('/sitemap.xml')) ?>" target="_blank" rel="noopener">Ver sitemap XML</a><a href="<?= e(url_path('/robots.txt')) ?>" target="_blank" rel="noopener">Ver robots.txt</a><span>Los cambios se reflejan al guardar. No requieren regeneración manual.</span></div>
</div></section>
