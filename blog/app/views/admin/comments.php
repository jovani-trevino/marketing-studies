<?php $commentLabels = ['all' => 'Todos', 'pending' => 'Pendientes', 'approved' => 'Aprobados', 'rejected' => 'Rechazados', 'spam' => 'Spam']; $commentActions = ['pending' => 'Dejar pendiente', 'approved' => 'Aprobar', 'rejected' => 'Rechazar', 'spam' => 'Marcar spam']; ?>
<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header"><div><span class="eyebrow">Moderación</span><h1>Comentarios</h1><p>Aprueba, rechaza o marca spam.</p></div></div>
        <div class="admin-card mb-4"><?php $listPath = '/admin/comments'; $filterLabel = 'Filtrar comentarios'; $filterStates = ['pending'=>'Pendiente','approved'=>'Aprobado','rejected'=>'Rechazado','spam'=>'Spam']; $searchHint = 'Nombre, correo, comentario o artículo…'; require __DIR__ . '/list_filters.php'; ?></div>
        <div class="comment-admin-grid">
            <?php if (!$comments): ?><div class="admin-card empty-state"><i class="bi bi-chat-square-text" aria-hidden="true"></i><h2 class="h4">Sin comentarios en esta vista</h2><p>Los nuevos comentarios aparecerán aquí para su revisión.</p></div><?php endif; ?>
            <?php foreach ($comments as $comment): ?>
                <article class="admin-card comment-admin-card">
                    <div class="comment-card-head"><div><strong><?= e($comment['author_name']) ?></strong><small class="d-block text-body-secondary"><?= e($comment['author_email']) ?></small></div><?= rating_stars((int) $comment['rating']) ?></div>
                    <p><?= e($comment['body']) ?></p>
                    <div class="small text-body-secondary mb-3">En <a href="<?= e(($comment['post_status'] ?? '') === 'published' ? post_url(['id' => $comment['post_id'], 'slug' => $comment['post_slug'], 'title' => $comment['post_title'], 'category_slug' => $comment['category_slug'] ?? 'marketing']) : url_path('/admin/posts/' . $comment['post_id'] . '/edit')) ?>"><?= e($comment['post_title']) ?></a> · <?= e(date_label($comment['created_at'])) ?></div>
                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                        <span class="badge <?= $comment['status'] === 'approved' ? 'text-bg-success' : ($comment['status'] === 'pending' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= e(['pending'=>'Pendiente','approved'=>'Aprobado','rejected'=>'Rechazado','spam'=>'Spam'][$comment['status']] ?? $comment['status']) ?></span>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach (['approved', 'pending', 'rejected', 'spam'] as $next): ?>
                                <?php if ($next !== $comment['status']): ?>
                                    <form method="post" action="<?= e(url_path('/admin/comments/' . $comment['id'] . '/status')) ?>"><?= csrf_input() ?><?= admin_return_input() ?><input type="hidden" name="status" value="<?= e($next) ?>"><button class="btn btn-sm btn-outline-dark rounded-pill" type="submit"><?= e($commentActions[$next]) ?></button></form>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <form method="post" action="<?= e(url_path('/admin/comments/' . $comment['id'] . '/delete')) ?>" onsubmit="return confirm('¿Eliminar este comentario de forma permanente?');"><?= csrf_input() ?><?= admin_return_input() ?><button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">Eliminar</button></form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php $paginationPath = '/admin/comments'; require __DIR__ . '/pagination.php'; ?>
    </div>
</section>
