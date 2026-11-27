<?php
$automaticDescription = seo_description(array_merge($post, ['seo_description' => '']));
$customTitle = ($post['seo_title'] ?? '') === ($post['title'] ?? '') ? '' : ($post['seo_title'] ?? '');
$customDescription = ($post['seo_description'] ?? '') === $automaticDescription ? '' : ($post['seo_description'] ?? '');
?>
<div class="admin-card studio-seo-preview mb-4" data-seo-preview data-site-title="<?= e($GLOBALS['site']['site_title'] ?? 'Marketing Studies') ?>" data-origin="<?= e(public_origin()) ?>">
    <div class="studio-card-heading"><h2 class="h5 mb-0">Así se verá en búsquedas</h2><span class="studio-tag"><i class="bi bi-stars" aria-hidden="true"></i> Automático</span></div>
    <div class="studio-search-preview">
        <span class="studio-search-site">Marketing Studies</span>
        <small data-seo-url><?= e(public_origin()) ?></small>
        <strong data-seo-title><?= e(($post['seo_title'] ?? '') ?: ($post['title'] ?: 'El título de tu próximo artículo')) ?></strong>
        <p data-seo-description><?= e(seo_description($post) ?: 'Tu descripción se generará con el extracto o el contenido del artículo.') ?></p>
    </div>
    <p class="studio-help">Vista orientativa. El buscador puede mostrar otro título o fragmento.</p>
    <details class="studio-seo-options">
        <summary>Personalizar título y descripción</summary>
        <div class="mt-3"><label class="form-label" for="seo-title">Título SEO</label><input id="seo-title" name="seo_title" class="form-control" maxlength="255" value="<?= e($customTitle) ?>" placeholder="Usar el título del artículo"></div>
        <div class="mt-3"><label class="form-label" for="seo-description">Descripción SEO</label><textarea id="seo-description" name="seo_description" class="form-control" rows="3" maxlength="160" placeholder="Generar desde el contenido"><?= e($customDescription) ?></textarea></div>
        <p class="studio-help mt-2">Deja ambos campos vacíos para actualizar el SEO automáticamente al guardar.</p>
    </details>
    <ul class="studio-readiness" data-seo-checklist aria-label="Revisión editorial">
        <li>La URL se conserva al editar.</li><li>Los borradores no aparecen en el sitemap.</li>
    </ul>
</div>
