<?php
$author = is_array($author ?? null) ? $author : [];
$authorProfessionalLinks = is_array($authorProfessionalLinks ?? null) ? $authorProfessionalLinks : [];
if (!$authorProfessionalLinks) {
    $authorProfessionalLinks = author_professional_links($author);
}
$authorOrcidId = orcid_normalize_id((string) ($author['orcid_id'] ?? ''));
$authorOrcidUrl = $authorOrcidId !== '' ? orcid_profile_url($authorOrcidId) : '';
$orcidConfigured = orcid_is_configured();
?>
<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Perfil editorial</span>
                <h1><?= $authorId ? 'Editar autor' : 'Nuevo autor' ?></h1>
                <p>La foto y datos de este autor apareceran en posts, tarjetas y pagina de perfil.</p>
            </div>
            <a href="<?= e(url_path('/admin/authors')) ?>" class="btn btn-outline-dark rounded-pill px-4">Volver</a>
        </div>

        <form method="post" enctype="multipart/form-data" autocomplete="off" data-admin-author-form="<?= $authorId ? 'edit' : 'new' ?>">
            <?= csrf_input() ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="admin-card">
                        <span class="eyebrow">Base canonica</span>
                        <h2 class="h5 mb-3">Identidad del autor</h2>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-control" value="<?= e($author['name']) ?>" autocomplete="off" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($author['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactive" <?= ($author['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control" value="<?= e($author['slug']) ?>" placeholder="jovani" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <input type="text" name="role" class="form-control" value="<?= e($author['role']) ?>" placeholder="Founder / Editor" autocomplete="off">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Headline / posicionamiento</label>
                                <input type="text" name="headline" class="form-control" value="<?= e($author['headline']) ?>" placeholder="Especialista en estrategia, datos, growth systems y ejecucion digital" autocomplete="off">
                                <small class="text-body-secondary">Linea corta que resume el perfil y sale destacada en la pagina publica.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Biografia</label>
                                <textarea name="bio" rows="5" class="form-control" placeholder="Cuenta quien es este autor y que tipo de contenido escribe." autocomplete="off"><?= e($author['bio']) ?></textarea>
                                <small class="text-body-secondary">Base comun del perfil. Si una version EN o ES queda vacia, el sitio usa este contenido.</small>
                            </div>
                        </div>
                    </div>
                    <div class="admin-card mt-4">
                        <span class="eyebrow">Expertise</span>
                        <h2 class="h5 mb-3">Areas, perfiles y highlights</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Areas de especialidad</label>
                                <textarea name="expertise_areas" rows="5" class="form-control" placeholder="Marketing Intelligence&#10;Performance&#10;SEO tecnico&#10;Growth Systems" autocomplete="off"><?= e($author['expertise_areas']) ?></textarea>
                                <small class="text-body-secondary">Una por linea o separadas por coma.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Perfiles / sombreros</label>
                                <textarea name="profile_labels" rows="5" class="form-control" placeholder="Strategist&#10;Operator&#10;Builder&#10;Analyst" autocomplete="off"><?= e($author['profile_labels']) ?></textarea>
                                <small class="text-body-secondary">Sirve para mostrar varios enfoques del mismo autor.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Highlights clave</label>
                                <textarea name="highlights" rows="5" class="form-control" placeholder="Disena ecosistemas digitales medibles.&#10;Conecta estrategia, datos y ejecucion.&#10;Convierte ideas en sistemas funcionales." autocomplete="off"><?= e($author['highlights']) ?></textarea>
                                <small class="text-body-secondary">Estos puntos salen como bullets en el perfil publico.</small>
                            </div>
                        </div>
                    </div>
                    <div class="admin-card mt-4">
                        <span class="eyebrow">Contenido por idioma</span>
                        <h2 class="h5 mb-3">Versiones EN / ES</h2>
                        <p class="text-body-secondary mb-4">Cambia de pestaña para editar la version publica del perfil segun el idioma activo del sitio.</p>
                        <ul class="nav nav-pills admin-language-tabs mb-4" id="author-translation-tabs" role="tablist">
                            <?php foreach (SUPPORTED_LOCALES as $tabIndex => $itemLocale): ?>
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link <?= $tabIndex === 0 ? 'active' : '' ?>"
                                        id="author-translation-tab-<?= e($itemLocale) ?>"
                                        data-bs-toggle="pill"
                                        data-bs-target="#author-translation-panel-<?= e($itemLocale) ?>"
                                        type="button"
                                        role="tab"
                                        aria-controls="author-translation-panel-<?= e($itemLocale) ?>"
                                        aria-selected="<?= $tabIndex === 0 ? 'true' : 'false' ?>"
                                    ><?= e(strtoupper($itemLocale)) ?></button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="tab-content">
                            <?php foreach (SUPPORTED_LOCALES as $tabIndex => $itemLocale): ?>
                                <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : '' ?>" id="author-translation-panel-<?= e($itemLocale) ?>" role="tabpanel" aria-labelledby="author-translation-tab-<?= e($itemLocale) ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Rol <?= e(strtoupper($itemLocale)) ?></label>
                                            <input type="text" name="role_<?= e($itemLocale) ?>" class="form-control" value="<?= e($author['role_' . $itemLocale] ?? '') ?>" placeholder="<?= e($itemLocale === 'en' ? 'Editor' : 'Editor') ?>" autocomplete="off">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Headline <?= e(strtoupper($itemLocale)) ?></label>
                                            <input type="text" name="headline_<?= e($itemLocale) ?>" class="form-control" value="<?= e($author['headline_' . $itemLocale] ?? '') ?>" placeholder="<?= e($itemLocale === 'en' ? 'Marketing Intelligence, SEO and performance specialist' : 'Especialista en estrategia, datos y ejecucion digital') ?>" autocomplete="off">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Biografia <?= e(strtoupper($itemLocale)) ?></label>
                                            <textarea name="bio_<?= e($itemLocale) ?>" rows="5" class="form-control" autocomplete="off"><?= e($author['bio_' . $itemLocale] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Areas de especialidad <?= e(strtoupper($itemLocale)) ?></label>
                                            <textarea name="expertise_areas_<?= e($itemLocale) ?>" rows="5" class="form-control" placeholder="<?= e($itemLocale === 'en' ? "Marketing Intelligence\nPerformance\nSEO\nGrowth Systems" : "Marketing Intelligence\nPerformance\nSEO tecnico\nGrowth Systems") ?>" autocomplete="off"><?= e($author['expertise_areas_' . $itemLocale] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Perfiles / sombreros <?= e(strtoupper($itemLocale)) ?></label>
                                            <textarea name="profile_labels_<?= e($itemLocale) ?>" rows="5" class="form-control" placeholder="<?= e($itemLocale === 'en' ? "Strategist\nOperator\nBuilder\nAnalyst" : "Estratega\nOperador\nBuilder\nAnalista") ?>" autocomplete="off"><?= e($author['profile_labels_' . $itemLocale] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Highlights <?= e(strtoupper($itemLocale)) ?></label>
                                            <textarea name="highlights_<?= e($itemLocale) ?>" rows="5" class="form-control" placeholder="<?= e($itemLocale === 'en' ? "Builds measurable digital ecosystems.\nConnects strategy, data and execution.\nTurns ideas into functional systems." : "Disena ecosistemas digitales medibles.\nConecta estrategia, datos y ejecucion.\nConvierte ideas en sistemas funcionales.") ?>" autocomplete="off"><?= e($author['highlights_' . $itemLocale] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="admin-card mt-4">
                        <span class="eyebrow">Perfiles profesionales</span>
                        <h2 class="h5 mb-3">Enlaces del autor</h2>
                        <p class="text-body-secondary mb-3">Activa solo los perfiles que quieras mostrar. Puedes guardar la URL y dejarla apagada si todavia no quieres publicarla.</p>
                        <div class="row g-3">
                            <?php foreach ($authorProfessionalLinks as $link): ?>
                                <div class="col-md-6">
                                    <div class="admin-card h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                            <div>
                                                <label class="form-label d-flex align-items-center gap-2 mb-1" for="professional-link-url-<?= e($link['key']) ?>">
                                                    <i class="<?= e($link['icon']) ?>"></i>
                                                    <span><?= e($link['label']) ?></span>
                                                </label>
                                                <p class="text-body-secondary small mb-0"><?= e($link['description']) ?></p>
                                            </div>
                                            <div class="form-check form-switch mt-1">
                                                <input class="form-check-input" type="checkbox" id="professional-link-enabled-<?= e($link['key']) ?>" name="professional_links[<?= e($link['key']) ?>][enabled]" <?= !empty($link['enabled']) ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="professional-link-enabled-<?= e($link['key']) ?>">Visible</label>
                                            </div>
                                        </div>
                                        <input
                                            type="url"
                                            name="professional_links[<?= e($link['key']) ?>][url]"
                                            id="professional-link-url-<?= e($link['key']) ?>"
                                            class="form-control"
                                            value="<?= e($link['url']) ?>"
                                            placeholder="<?= e($link['placeholder']) ?>"
                                            autocomplete="off"
                                        >
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="admin-card">
                        <span class="eyebrow">Foto</span>
                        <h2 class="h5 mb-3">Avatar del autor</h2>
                        <?php admin_image_field('avatar_url', 'avatar_file', 'Avatar del autor', $author['avatar_url'] ?? '', [
                            'id' => 'author-avatar',
                            'button_label' => 'Subir avatar',
                            'hide_manual_input' => true,
                            'hint' => 'JPG, PNG, WEBP o GIF. Maximo 5 MB.',
                            'empty_title' => 'Avatar del autor',
                            'empty_copy' => 'Sube la foto de perfil desde tu PC.',
                            'drop_hint' => 'Tambien puedes arrastrar aqui la imagen del autor.',
                            'status_ready' => trim((string) ($author['avatar_url'] ?? '')) !== '' ? 'Avatar listo.' : 'Sin avatar todavia.',
                        ]); ?>
                        <div class="admin-media-caption">
                            <strong><?= e($author['name'] ?: 'Nuevo autor') ?></strong>
                            <small><?= e($author['headline'] ?: ($author['role'] ?: 'Vista previa del perfil')) ?></small>
                        </div>
                    </div>
                    <div class="admin-card admin-orcid-card mt-4">
                        <span class="eyebrow">ORCID</span>
                        <h2 class="h5 mb-2">ORCID iD verificado</h2>
                        <p class="text-body-secondary mb-3">Conecta este autor con ORCID para guardar un identificador autenticado desde Developer Tools.</p>
                        <?php if (!$authorId): ?>
                            <div class="alert alert-light border mb-0">Guarda el autor primero para poder conectarlo con ORCID.</div>
                        <?php elseif ($authorOrcidId !== ''): ?>
                            <a class="author-orcid-badge admin-orcid-badge mb-3" href="<?= e($authorOrcidUrl) ?>" target="_blank" rel="noopener noreferrer">
                                <span>iD</span>
                                <strong><?= e($authorOrcidId) ?></strong>
                            </a>
                            <?php if (!empty($author['orcid_name']) || !empty($author['orcid_verified_at'])): ?>
                                <small class="text-body-secondary d-block mb-3">
                                    <?= !empty($author['orcid_name']) ? e((string) $author['orcid_name']) . ' · ' : '' ?>
                                    Verificado <?= e(date_label((string) ($author['orcid_verified_at'] ?? ''))) ?>
                                </small>
                            <?php endif; ?>
                            <form method="post" action="<?= e(url_path('/admin/authors/' . $authorId . '/orcid/disconnect')) ?>" onsubmit="return confirm('Desconectar este ORCID del autor?');">
                                <?= csrf_input() ?>
                                <button class="btn btn-outline-danger rounded-pill w-100" type="submit">Desconectar ORCID</button>
                            </form>
                        <?php elseif ($orcidConfigured): ?>
                            <a class="btn btn-dark rounded-pill w-100" href="<?= e(url_path('/admin/authors/' . $authorId . '/orcid/connect')) ?>">Conectar con ORCID</a>
                            <small class="text-body-secondary d-block mt-3">Se abrira ORCID para autorizar y regresar al CMS.</small>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">Faltan ORCID Client ID y Client Secret en la configuracion.</div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-dark rounded-pill w-100 mt-4" type="submit">Guardar autor</button>
                </div>
            </div>
        </form>
    </div>
</section>
<?php if (!empty($authorFormIsFresh)): ?>
<script>
(() => {
    const resetFreshAuthorForm = () => {
        const form = document.querySelector('[data-admin-author-form="new"]');
        if (!form) {
            return;
        }

        form.reset();
        form.querySelectorAll("[data-admin-media-url]").forEach((input) => {
            input.dispatchEvent(new Event("input", { bubbles: true }));
        });
    };

    window.addEventListener("pageshow", resetFreshAuthorForm, { once: true });
})();
</script>
<?php endif; ?>
