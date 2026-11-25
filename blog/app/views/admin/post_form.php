<?php
$extraHead = '<link rel="stylesheet" href="' . e(versioned_asset_url('static/vendor/quill/quill.snow.css')) . '">';
$extraScripts =
    '<script src="' . e(versioned_asset_url('static/vendor/quill/quill.js')) . '"></script>' .
    '<script src="' . e(versioned_asset_url('static/vendor/turndown/turndown.js')) . '"></script>' .
    '<script src="' . e(versioned_asset_url('static/admin-editor.js')) . '"></script>';

if (!function_exists('admin_markdown_editor_stats')) {
    function admin_markdown_editor_stats(string $markdown, string $html): array
    {
        $sourceHtml = trim($html) !== '' ? $html : markdown_to_html($markdown);
        $sections = 0;

        if (trim($markdown) !== '') {
            preg_match_all('/^#{1,3}\s+/m', $markdown, $matches);
            $sections = count($matches[0] ?? []);
        } elseif (trim($sourceHtml) !== '') {
            preg_match_all('/<h[1-3]\b/i', $sourceHtml, $matches);
            $sections = count($matches[0] ?? []);
        }

        return [
            'words' => text_word_count($sourceHtml),
            'read_time' => estimate_read_time($sourceHtml),
            'sections' => $sections,
        ];
    }
}

if (!function_exists('admin_markdown_editor')) {
    function admin_markdown_editor(string $name, string $value, string $htmlName, string $htmlValue, string $label, string $hint, string $editorId, int $rows = 14, bool $required = false): void
    {
        $requiredAttr = $required ? ' required' : '';
        ?>
        <div class="markdown-editor" data-markdown-editor data-editor-id="<?= e($editorId) ?>" data-upload-url="<?= e(url_path('/admin/uploads/image')) ?>" data-csrf-token="<?= e(csrf_token()) ?>" data-base-path="<?= e(base_path()) ?>">
                    <div class="markdown-editor-head">
                        <div>
                            <label class="form-label mb-1" for="editor-<?= e($editorId) ?>"><?= e($label) ?></label>
                            <p class="markdown-editor-hint"><?= e($hint) ?></p>
                        </div>
                        <div class="markdown-editor-stats" aria-live="polite">
                            <span data-md-words>0 palabras</span>
                            <span data-md-read>1 min</span>
                            <span data-md-structure>0 secciones</span>
                            <span data-md-save>Sin cambios locales</span>
                        </div>
                    </div>
                    <div class="editor-recovery" data-md-recovery hidden>
                        <span>Hay una copia de este texto guardada en este navegador.</span>
                        <button type="button" data-md-restore>Restaurar</button>
                        <button type="button" data-md-discard>Descartar</button>
                    </div>
                    <div class="editor-assist-strip">
                        <div class="editor-assist-copy">
                            <strong>Editor visual:</strong> da formato como en Word, sube imagenes desde tu PC y escribe sin ver codigo.
                        </div>
                        <div class="editor-quick-actions">
                            <button type="button" data-md-snippet="intro">Intro</button>
                            <button type="button" data-md-snippet="steps">3 pasos</button>
                            <button type="button" data-md-snippet="cta">CTA</button>
                            <button type="button" data-md-snippet="summary">Resumen</button>
                        </div>
                    </div>
                    <div class="markdown-toolbar" role="toolbar" aria-label="Herramientas del editor">
                        <select data-md-select="block" title="Tipo de bloque" aria-label="Tipo de bloque">
                            <option value="paragraph">Parrafo</option>
                            <option value="h1">Titulo 1</option>
                            <option value="h2">Titulo 2</option>
                            <option value="h3">Titulo 3</option>
                        </select>
                        <select data-md-select="size" title="Tamano de texto" aria-label="Tamano de texto">
                            <option value="normal">Normal</option>
                            <option value="small">Pequeno</option>
                            <option value="large">Grande</option>
                            <option value="huge">Muy grande</option>
                        </select>
                        <button type="button" data-md-command="h1" title="Titulo principal">H1</button>
                        <button type="button" data-md-command="h2" title="Titulo grande">H2</button>
                        <button type="button" data-md-command="h3" title="Subtitulo">H3</button>
                        <button type="button" data-md-command="bold" title="Negritas"><i class="bi bi-type-bold"></i></button>
                        <button type="button" data-md-command="italic" title="Italicas"><i class="bi bi-type-italic"></i></button>
                        <button type="button" data-md-command="underline" title="Subrayado" aria-label="Subrayado"><i class="bi bi-type-underline"></i></button>
                        <button type="button" data-md-command="strike" title="Tachado" aria-label="Tachado"><i class="bi bi-type-strikethrough"></i></button>
                        <button type="button" data-md-command="sub" title="Subindice" aria-label="Subindice"><i class="bi bi-subscript"></i></button>
                        <button type="button" data-md-command="super" title="Superindice" aria-label="Superindice"><i class="bi bi-superscript"></i></button>
                        <button type="button" data-md-command="quote" title="Cita"><i class="bi bi-quote"></i></button>
                        <button type="button" data-md-command="ul" title="Lista"><i class="bi bi-list-ul"></i></button>
                        <button type="button" data-md-command="ol" title="Lista numerada"><i class="bi bi-list-ol"></i></button>
                        <button type="button" data-md-command="check" title="Checklist"><i class="bi bi-check2-square"></i></button>
                        <button type="button" data-md-command="indent-" title="Reducir sangria" aria-label="Reducir sangria"><i class="bi bi-text-indent-left"></i></button>
                        <button type="button" data-md-command="indent+" title="Aumentar sangria" aria-label="Aumentar sangria"><i class="bi bi-text-indent-right"></i></button>
                        <button type="button" data-md-command="align-left" title="Alinear a la izquierda" aria-label="Alinear a la izquierda"><i class="bi bi-text-left"></i></button>
                        <button type="button" data-md-command="align-center" title="Centrar" aria-label="Centrar"><i class="bi bi-text-center"></i></button>
                        <button type="button" data-md-command="align-right" title="Alinear a la derecha" aria-label="Alinear a la derecha"><i class="bi bi-text-right"></i></button>
                        <button type="button" data-md-command="align-justify" title="Justificar" aria-label="Justificar"><i class="bi bi-justify"></i></button>
                        <button type="button" data-md-command="link" title="Link"><i class="bi bi-link-45deg"></i></button>
                        <button type="button" data-md-command="image" title="Subir imagen desde tu equipo"><i class="bi bi-image"></i></button>
                        <button type="button" data-md-command="divider" title="Separador"><i class="bi bi-distribute-horizontal"></i></button>
                        <button type="button" data-md-command="inline-code" title="Codigo en linea" aria-label="Codigo en linea"><i class="bi bi-code"></i></button>
                        <button type="button" data-md-command="code" title="Bloque de codigo"><i class="bi bi-code-slash"></i></button>
                        <button type="button" data-md-command="callout" title="Nota"><i class="bi bi-lightbulb"></i></button>
                        <button type="button" data-md-command="clear" title="Limpiar formato"><i class="bi bi-eraser"></i></button>
                        <button type="button" data-md-command="undo" title="Deshacer"><i class="bi bi-arrow-counterclockwise"></i></button>
                        <button type="button" data-md-command="redo" title="Rehacer"><i class="bi bi-arrow-clockwise"></i></button>
                        <label class="toolbar-color-chip" title="Color del texto">
                            <span>A</span>
                            <input type="color" value="#14243a" data-md-color="color" aria-label="Color del texto">
                        </label>
                        <label class="toolbar-color-chip" title="Color de resaltado">
                            <span>HL</span>
                            <input type="color" value="#fff1b8" data-md-color="background" aria-label="Color de resaltado">
                        </label>
                        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-md-upload-input hidden>
                        <span class="toolbar-spacer"></span>
                        <button type="button" data-md-fullscreen title="Pantalla completa"><i class="bi bi-arrows-fullscreen"></i></button>
                    </div>
                    <div class="markdown-editor-grid">
                        <div class="markdown-source-pane">
                            <div class="visual-editor-host" data-md-editor-host hidden></div>
                            <textarea id="editor-<?= e($editorId) ?>" name="<?= e($name) ?>" class="form-control editor-area markdown-input" rows="<?= (int) $rows ?>" data-md-input data-storage-key="marketing-studies-editor:<?= e($editorId) ?>"<?= $requiredAttr ?>><?= e($value) ?></textarea>
                            <textarea name="<?= e($htmlName) ?>" data-md-html-input hidden><?= e($htmlValue) ?></textarea>
                        </div>
                    </div>
                    <div class="markdown-editor-foot">
                        <span><strong>Tips:</strong> Ctrl+B negritas, Ctrl+I italicas, Ctrl+U subrayado, Ctrl+K link. Haz clic en una imagen, arrastra la esquina para cambiar su tamano y usa la barra espaciadora para moverla entre izquierda, centro y derecha.</span>
                        <span data-md-upload-status>Las imagenes se suben desde tu equipo; tambien puedes arrastrarlas al editor.</span>
                    </div>
        </div>
        <?php
    }
}
?>
<section class="section-space admin-writing-section">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <?php $contentLocales = SUPPORTED_LOCALES; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Editor de contenido</span>
                <h1><?= $postId ? 'Editar artículo' : 'Un nuevo artículo' ?></h1>
                <p>Da forma a tus ideas con imágenes, versiones en dos idiomas y una copia local de tu texto mientras escribes.</p>
            </div>
            <a href="<?= e(url_path('/admin/posts')) ?>" class="btn btn-outline-dark rounded-pill px-4">Volver</a>
        </div>
        <form method="post" class="admin-writing-form" data-admin-editor-form enctype="multipart/form-data">
            <?= csrf_input() ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="admin-card writer-card">
                        <div class="mb-3"><label class="form-label" for="title">Título del artículo</label><textarea id="title" name="title" class="form-control admin-title-input" rows="2" required><?= e($post['title']) ?></textarea></div>
                        <div class="mb-3"><label class="form-label">Slug</label><input type="text" id="slug" name="slug" class="form-control" value="<?= e($post['slug']) ?>" readonly data-auto-slug-target data-auto-slug-source="#title"><small class="text-body-secondary">Se genera desde el título al crear el artículo y se conserva al editarlo.</small></div>
                        <div class="mb-3"><label class="form-label">Extracto</label><textarea name="excerpt" class="form-control" rows="3" required><?= e($post['excerpt']) ?></textarea></div>
                    </div>

                    <?php if ($contentLocales): ?>
                        <div class="admin-card writer-card mt-4">
                            <div class="d-flex justify-content-between gap-3 flex-wrap mb-3">
                                <div>
                                    <span class="eyebrow">Contenido por idioma</span>
                                    <h2 class="h5 mb-1">Versiones EN / ES</h2>
                                    <p class="small text-body-secondary mb-0">Edita el cuerpo visual del articulo desde su idioma correspondiente.</p>
                                </div>
                            </div>
                            <ul class="nav nav-pills admin-language-tabs mb-4" id="translation-tabs" role="tablist">
                                <?php foreach ($contentLocales as $tabIndex => $itemLocale): ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= $tabIndex === 0 ? 'active' : '' ?>" id="translation-tab-<?= e($itemLocale) ?>" data-bs-toggle="pill" data-bs-target="#translation-panel-<?= e($itemLocale) ?>" type="button" role="tab" aria-controls="translation-panel-<?= e($itemLocale) ?>" aria-selected="<?= $tabIndex === 0 ? 'true' : 'false' ?>"><?= e(strtoupper($itemLocale)) ?></button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="tab-content">
                                <?php foreach ($contentLocales as $tabIndex => $itemLocale): ?>
                                    <?php $isDefaultLocale = $itemLocale === DEFAULT_LOCALE; ?>
                                    <?php $translation = $translations[$itemLocale] ?? ['title' => '', 'slug' => '', 'excerpt' => '', 'content_markdown' => '', 'content_html' => '', 'seo_title' => '', 'seo_description' => '']; ?>
                                    <div class="tab-pane fade <?= $tabIndex === 0 ? 'show active' : '' ?>" id="translation-panel-<?= e($itemLocale) ?>" role="tabpanel" aria-labelledby="translation-tab-<?= e($itemLocale) ?>">
                                        <div class="row g-3">
                                            <?php if ($isDefaultLocale): ?>
                                                <div class="col-12">
                                                    <?php admin_markdown_editor('content_markdown', (string) $post['content_markdown'], 'content_html', (string) ($post['content_html'] ?? ''), 'Contenido ' . strtoupper($itemLocale), 'Version visual editable para el idioma ' . strtoupper($itemLocale) . '.', 'post-' . ($postId ?: 'new') . '-base', 18, true); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="col-md-8"><label class="form-label">Titulo <?= e(strtoupper($itemLocale)) ?></label><input type="text" name="translations[<?= e($itemLocale) ?>][title]" class="form-control" value="<?= e($translation['title'] ?? '') ?>"></div>
                                                <div class="col-md-4"><label class="form-label">Slug <?= e(strtoupper($itemLocale)) ?></label><input type="text" name="translations[<?= e($itemLocale) ?>][slug]" class="form-control" value="<?= e($translation['slug'] ?? '') ?>" readonly data-auto-slug-target data-auto-slug-source="[name='translations[<?= e($itemLocale) ?>][title]']"><small class="text-body-secondary">Automatico.</small></div>
                                                <div class="col-12"><label class="form-label">Extracto <?= e(strtoupper($itemLocale)) ?></label><textarea name="translations[<?= e($itemLocale) ?>][excerpt]" class="form-control" rows="3"><?= e($translation['excerpt'] ?? '') ?></textarea></div>
                                                <div class="col-12">
                                                    <?php admin_markdown_editor('translations[' . $itemLocale . '][content_markdown]', (string) ($translation['content_markdown'] ?? ''), 'translations[' . $itemLocale . '][content_html]', (string) ($translation['content_html'] ?? ''), 'Contenido ' . strtoupper($itemLocale), 'Version visual editable para el idioma ' . strtoupper($itemLocale) . '.', 'post-' . ($postId ?: 'new') . '-' . $itemLocale, 12, false); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4">
                    <div class="admin-editor-sidebar">
                    <div class="admin-card">
                        <h2 class="h5 mb-3">Publicacion</h2>
                        <div class="mb-3"><label class="form-label">Estado</label><select name="status" class="form-select"><option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option><option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Publicado</option></select></div>

                        <div class="mb-3">
                            <label class="form-label">Autor</label>
                            <?php if ($authors): ?>
                                <select name="author_id" class="form-select">
                                    <?php foreach ($authors as $authorOption): ?>
                                        <option value="<?= (int) $authorOption['id'] ?>" <?= (string) ($post['author_id'] ?? '') === (string) $authorOption['id'] ? 'selected' : '' ?>><?= e($authorOption['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control" value="<?= e($post['author_name'] ?: ($GLOBALS['site']['author_name'] ?? 'Jovani')) ?>" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="featured" name="is_featured" <?= $post['is_featured'] ? 'checked' : '' ?>><label class="form-check-label" for="featured">Destacado</label></div>
                    </div>
                    <div class="admin-card mt-4">
                        <h2 class="h5 mb-3">Categoria e imagen</h2>
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="category_id" class="form-select">
                                <option value="">Sin categoria</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= (string) $post['category_id'] === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['tree_label'] ?? $category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label class="form-label">Imagen destacada</label>
                        <?php admin_image_field('cover_image', 'cover_image_file', 'Imagen destacada del post', $post['cover_image'] ?? '', [
                            'id' => 'post-cover-image',
                            'button_label' => 'Subir portada',
                            'manual_label' => 'Ruta de la imagen destacada',
                            'placeholder' => '/uploads/posts/mi-post.jpg',
                            'hint' => 'Si eliges una imagen desde tu PC, reemplazara la ruta manual al guardar.',
                            'empty_title' => 'Portada del post',
                            'empty_copy' => 'Sube la imagen destacada para este articulo.',
                            'drop_hint' => 'Tambien puedes arrastrar aqui la portada del post.',
                        ]); ?>
                    </div>

                    <div class="admin-save-panel"><button type="submit" class="btn btn-dark w-100"><i class="bi bi-check2" aria-hidden="true"></i> Guardar artículo</button><p class="admin-save-note" data-admin-save-state role="status">Guarda para actualizar la publicación. La copia local solo conserva el texto en este navegador.</p><?php if (setting_is_enabled($GLOBALS['site']['newsletter_enabled'] ?? '1') && ($post['status'] ?? 'draft') !== 'published'): ?><p class="admin-save-note">Al publicar se intentará enviar la newsletter a los suscriptores activos, según tu configuración de correo.</p><?php endif; ?></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
