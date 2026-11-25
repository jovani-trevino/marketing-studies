<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header"><div><span class="eyebrow">Growth Lab</span><h1>Materiales descargables</h1><p>Administra plantillas, checklists y referencias.</p></div><a href="<?= e(url_path('/admin/lab-resources/new')) ?>" class="btn btn-dark rounded-pill px-4">Nuevo material</a></div>
        <div class="admin-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0 admin-table">
                    <thead><tr><th>Material</th><th>Tipo</th><th>Credito</th><th>Estado</th><th>Descargas</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($resources as $resource): ?>
                            <tr>
                                <td><div class="fw-semibold"><?= e($resource['title']) ?></div><div class="small text-body-secondary"><?= e($resource['slug']) ?></div></td>
                                <td><?= e($resource['resource_type']) ?></td>
                                <td><?= e($resource['source_name'] ?: $resource['author_name']) ?></td>
                                <td><span class="badge <?= $resource['status'] === 'published' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= e($resource['status']) ?></span></td>
                                <td><?= (int) $resource['download_count'] ?></td>
                                <td class="text-end"><div class="d-flex justify-content-end gap-2 flex-wrap"><a href="<?= e(url_path('/admin/lab-resources/' . $resource['id'] . '/edit')) ?>" class="btn btn-sm btn-dark rounded-pill">Editar</a><form method="post" action="<?= e(url_path('/admin/lab-resources/' . $resource['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminar material?');"><?= csrf_input() ?><button class="btn btn-sm btn-outline-danger rounded-pill">Eliminar</button></form></div></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
