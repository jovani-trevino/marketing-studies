<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Growth Lab</span>
                <h1><?= $resourceId ? 'Editar material' : 'Nuevo material' ?></h1>
                <p>Si viene de otra fuente, agrega referencia para dar credito.</p>
            </div>
            <a href="<?= e(url_path('/admin/lab-resources')) ?>" class="btn btn-outline-dark rounded-pill px-4">Volver</a>
        </div>

        <form data-admin-unsaved method="post" enctype="multipart/form-data" data-admin-resource-form>
            <?= csrf_input() ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="admin-card">
                        <div class="mb-3">
                            <label for="field-lab_resource_form-title" class="form-label">Titulo</label>
                            <input id="field-lab_resource_form-title" name="title" class="form-control" value="<?= e($resource['title']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="field-lab_resource_form-slug" class="form-label">Slug</label>
                            <input id="field-lab_resource_form-slug" name="slug" class="form-control" readonly aria-describedby="resource-slug-help" value="<?= e($resource['slug']) ?>"><small id="resource-slug-help" class="text-body-secondary">Se genera al crear el material y se conserva al editarlo.</small>
                        </div>
                        <div class="mb-3">
                            <label for="field-lab_resource_form-description" class="form-label">Descripcion</label>
                            <textarea id="field-lab_resource_form-description" name="description" rows="5" class="form-control" required><?= e($resource['description']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="field-lab_resource_form-download_url" class="form-label">URL o ruta de descarga</label>
                            <input id="field-lab_resource_form-download_url" name="download_url" class="form-control" value="<?= e($resource['download_url']) ?>" aria-describedby="resource-file-help">
                        </div>
                        <div>
                            <label for="field-lab_resource_form-download_file" class="form-label">Subir archivo descargable</label>
                            <input id="field-lab_resource_form-download_file" type="file" name="download_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip">
                            <small class="text-body-secondary" id="resource-file-help">Pega una URL o selecciona un archivo de hasta 15 MB. Al subir un archivo, su ruta reemplaza el enlace escrito.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="admin-card">
                        <h2 class="h5 mb-3">Publicacion</h2>
                        <div class="mb-3">
                            <label for="field-lab_resource_form-status" class="form-label">Estado</label>
                            <select id="field-lab_resource_form-status" name="status" class="form-select">
                                <option value="draft" <?= $resource['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option>
                                <option value="published" <?= $resource['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="field-lab_resource_form-resource_type" class="form-label">Tipo</label>
                            <input id="field-lab_resource_form-resource_type" name="resource_type" class="form-control" value="<?= e($resource['resource_type']) ?>">
                        </div>
                        <label class="form-label">Portada del recurso</label>
                        <?php admin_image_field('cover_image', 'cover_image_file', 'Portada del recurso', $resource['cover_image'] ?? '', [
                            'id' => 'lab-cover-image',
                            'button_label' => 'Subir portada',
                            'manual_label' => 'Ruta de la portada',
                            'placeholder' => '/uploads/lab/recurso.jpg',
                            'hint' => 'Usa una portada clara para el listado del Lab.',
                            'empty_title' => 'Portada del recurso',
                            'empty_copy' => 'Sube la imagen principal de este material.',
                            'drop_hint' => 'Tambien puedes arrastrar aqui la portada del recurso.',
                        ]); ?>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="featured" <?= $resource['is_featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="featured">Destacado</label>
                        </div>
                    </div>

                    <div class="admin-card mt-4">
                        <h2 class="h5 mb-3">Crédito</h2>
                        <div class="mb-3">
                            <label for="field-lab_resource_form-author_name" class="form-label">Autor interno</label>
                            <input id="field-lab_resource_form-author_name" name="author_name" class="form-control" value="<?= e($resource['author_name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="field-lab_resource_form-source_name" class="form-label">Referencia externa</label>
                            <input id="field-lab_resource_form-source_name" name="source_name" class="form-control" value="<?= e($resource['source_name']) ?>">
                        </div>
                        <div>
                            <label for="field-lab_resource_form-source_url" class="form-label">URL referencia</label>
                            <input id="field-lab_resource_form-source_url" type="url" name="source_url" class="form-control" value="<?= e($resource['source_url']) ?>">
                        </div>
                    </div>

                    <button class="btn btn-dark w-100 rounded-pill mt-4">Guardar material</button>
                </div>
            </div>
        </form>
    </div>
</section>
