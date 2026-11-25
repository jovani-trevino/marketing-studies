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

        <form method="post" enctype="multipart/form-data">
            <?= csrf_input() ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="admin-card">
                        <div class="mb-3">
                            <label class="form-label">Titulo</label>
                            <input name="title" class="form-control" value="<?= e($resource['title']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input name="slug" class="form-control" value="<?= e($resource['slug']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripcion</label>
                            <textarea name="description" rows="5" class="form-control" required><?= e($resource['description']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL o ruta de descarga</label>
                            <input name="download_url" class="form-control" value="<?= e($resource['download_url']) ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Subir archivo descargable</label>
                            <input type="file" name="download_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip">
                            <small class="text-body-secondary">Si subes archivo, reemplaza la ruta de descarga.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="admin-card">
                        <h2 class="h5 mb-3">Publicacion</h2>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="draft" <?= $resource['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option>
                                <option value="published" <?= $resource['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <input name="resource_type" class="form-control" value="<?= e($resource['resource_type']) ?>">
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
                        <h2 class="h5 mb-3">Credito</h2>
                        <div class="mb-3">
                            <label class="form-label">Autor interno</label>
                            <input name="author_name" class="form-control" value="<?= e($resource['author_name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referencia externa</label>
                            <input name="source_name" class="form-control" value="<?= e($resource['source_name']) ?>">
                        </div>
                        <div>
                            <label class="form-label">URL referencia</label>
                            <input type="url" name="source_url" class="form-control" value="<?= e($resource['source_url']) ?>">
                        </div>
                    </div>

                    <button class="btn btn-dark w-100 rounded-pill mt-4">Guardar material</button>
                </div>
            </div>
        </form>
    </div>
</section>
