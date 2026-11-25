<?php $filters = $filters ?? ['q' => '', 'status' => '']; $contactLabels = ['new' => 'Nuevo', 'read' => 'Leído', 'replied' => 'Respondido']; ?>
<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header"><div><span class="eyebrow">Conversaciones</span><h1>Tu bandeja de entrada</h1><p>Consultas y oportunidades recibidas desde el formulario de contacto.</p></div><a href="<?= e(url_path('/#contact')) ?>" class="btn btn-outline-dark" target="_blank" rel="noopener">Ver formulario <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
        <div class="admin-card admin-list-card mb-4">
            <form class="admin-filter-bar" method="get" action="<?= e(url_path('/admin/contacts')) ?>" role="search" aria-label="Filtrar mensajes">
                <div class="admin-search-field"><label for="contact-search">Buscar</label><input id="contact-search" type="search" name="q" class="form-control" value="<?= e($filters['q'] ?? '') ?>" placeholder="Nombre, correo o mensaje…"></div>
                <div><label for="contact-status-filter">Estado</label><select id="contact-status-filter" name="status" class="form-select"><option value="">Todos los mensajes</option><?php foreach ($contactLabels as $key => $label): ?><option value="<?= e($key) ?>"<?= ($filters['status'] ?? '') === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
                <button class="btn btn-dark" type="submit">Filtrar</button>
                <?php if (array_filter($filters)): ?><a class="btn btn-outline-dark" href="<?= e(url_path('/admin/contacts')) ?>">Limpiar</a><?php endif; ?>
            </form>
        </div>
        <div class="admin-contact-grid">
            <?php foreach ($contacts as $contact): ?>
                <article class="admin-card admin-contact-card">
                    <div class="admin-contact-heading"><div><h2 class="h5 mb-1"><?= e($contact['name']) ?></h2><a href="mailto:<?= e($contact['email']) ?>" class="small text-body-secondary"><?= e($contact['email']) ?></a></div><div class="admin-contact-meta"><span class="badge <?= ($contact['status'] ?? 'new') === 'new' ? 'text-bg-warning' : 'text-bg-secondary' ?>"><?= e($contactLabels[$contact['status'] ?? 'new'] ?? 'Nuevo') ?></span><time datetime="<?= e($contact['created_at']) ?>"><?= e(date_label($contact['created_at'])) ?></time></div></div>
                    <h3 class="h6 mt-4"><?= e($contact['topic'] ?: 'Consulta desde el sitio') ?></h3>
                    <p class="admin-contact-message"><?= e($contact['message']) ?></p>
                    <div class="admin-contact-actions"><a class="btn btn-sm btn-outline-dark" href="mailto:<?= e($contact['email']) ?>?subject=<?= e(rawurlencode('Re: ' . ($contact['topic'] ?: 'Marketing Studies'))) ?>"><i class="bi bi-envelope" aria-hidden="true"></i> Abrir correo</a><form method="post" action="<?= e(url_path('/admin/contacts/' . $contact['id'] . '/status')) ?>"><?= csrf_input() ?><label class="visually-hidden" for="contact-state-<?= (int) $contact['id'] ?>">Estado del mensaje de <?= e($contact['name']) ?></label><select class="form-select form-select-sm" id="contact-state-<?= (int) $contact['id'] ?>" name="status"><?php foreach ($contactLabels as $key => $label): ?><option value="<?= e($key) ?>"<?= ($contact['status'] ?? 'new') === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select><button type="submit" class="btn btn-sm btn-dark">Actualizar estado</button></form></div>
                </article>
            <?php endforeach; ?>
            <?php if (!$contacts): ?><div class="admin-card empty-state"><i class="bi bi-inbox" aria-hidden="true"></i><h2 class="h4">Sin mensajes en esta vista</h2><p>Las consultas que recibas desde tu página aparecerán aquí.</p></div><?php endif; ?>
        </div>
        <p class="admin-context-note">«Abrir correo» inicia tu aplicación de email. Marcar un mensaje como respondido solo actualiza su estado.</p>
        <?php $paginationPath = '/admin/contacts'; require __DIR__ . '/pagination.php'; ?>
    </div>
</section>
