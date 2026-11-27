<section class="section-space admin-newsletter-page">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Email marketing</span>
                <h1>Newsletter</h1>
                <p>Administra suscriptores, revisa campañas y envia un correo cuando publiques contenido nuevo.</p>
            </div>
            <a href="<?= e(url_path('/#newsletter')) ?>" class="btn btn-outline-dark rounded-pill px-4">Ver formulario</a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="stat-card"><span>Total</span><strong><?= (int) ($stats['total'] ?? 0) ?></strong><small>Suscriptores registrados</small></div></div>
            <div class="col-md-4"><div class="stat-card"><span>Activos</span><strong><?= (int) ($stats['active'] ?? 0) ?></strong><small>Disponibles para próximos envíos</small></div></div>
            <div class="col-md-4"><div class="stat-card"><span>Bajas</span><strong><?= (int) ($stats['unsubscribed'] ?? 0) ?></strong><small>Usuarios dados de baja</small></div></div>
        </div>

        <div class="admin-card newsletter-panel newsletter-add-card mb-4">
            <form method="post" action="<?= e(url_path('/admin/newsletter/subscribers/new')) ?>" class="newsletter-add-form">
                <?= csrf_input() ?><?= admin_return_input() ?>
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                    <div>
                        <span class="eyebrow">Alta manual</span>
                        <h2 class="h4 mb-1">Agregar suscriptor</h2>
                        <p class="text-body-secondary mb-0">Registra correos desde el CMS y reactiva suscriptores sin crear duplicados.</p>
                    </div>
                    <button class="btn btn-dark rounded-pill px-4" type="submit">
                        <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Agregar
                    </button>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label" for="newsletter-subscriber-email">Email</label>
                        <input class="form-control" type="email" id="newsletter-subscriber-email" name="email" required placeholder="persona@empresa.com">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="newsletter-subscriber-locale">Idioma</label>
                        <select class="form-select" id="newsletter-subscriber-locale" name="locale">
                            <option value="en">EN</option>
                            <option value="es">ES</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="newsletter-subscriber-status">Estado</label>
                        <select class="form-select" id="newsletter-subscriber-status" name="status">
                            <option value="active">Activo</option>
                            <option value="unsubscribed">Baja</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label" for="newsletter-subscriber-source">Origen</label>
                        <input class="form-control" type="text" id="newsletter-subscriber-source" name="source" value="cms" maxlength="64">
                    </div>
                </div>
            </form>
        </div>
        <div class="admin-card newsletter-panel mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <span class="eyebrow">Transporte de envio</span>
                    <h2 class="h4 mb-1"><?= e($mailerStatus['label'] ?? 'mail() del servidor') ?></h2>
                    <p class="text-body-secondary mb-0"><?= e($mailerStatus['summary'] ?? '') ?></p>
                </div>
                <a href="<?= e(url_path('/admin/settings')) ?>" class="btn btn-outline-dark rounded-pill px-4">Ajustar SMTP</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="admin-card newsletter-panel">
                    <div class="section-head mb-3">
                        <div>
                            <span class="eyebrow">Suscriptores</span>
                            <h2 class="h4 mb-0">Personas registradas</h2>
                        </div>
                    </div>
                    <?php $listPath = '/admin/newsletter'; $filterLabel = 'Filtrar suscriptores'; $filterStates = ['active'=>'Activo','unsubscribed'=>'Baja']; $searchHint = 'Correo u origen…'; require __DIR__ . '/list_filters.php'; ?>
                    <?php if ($subscribers): ?>
                        <div class="table-responsive">
                            <table class="table align-middle admin-table newsletter-table">
                                <thead><tr><th>Email</th><th>Idioma</th><th>Estado</th><th>Alta</th><th>Último envío</th><th class="text-end">Accion</th></tr></thead>
                                <tbody>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td><strong><?= e($subscriber['email']) ?></strong><div class="small text-body-secondary"><?= e($subscriber['source'] ?? 'home') ?></div></td>
                                            <td><?= e(strtoupper($subscriber['locale'] ?? 'en')) ?></td>
                                            <td><span class="badge <?= ($subscriber['status'] ?? 'active') === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= ($subscriber['status'] ?? 'active') === 'active' ? 'Activo' : 'Baja' ?></span></td>
                                            <td><?= e(date_label($subscriber['created_at'] ?? '')) ?></td>
                                            <td><?= e(!empty($subscriber['last_sent_at']) ? date_label($subscriber['last_sent_at']) : 'Sin envío') ?></td>
                                            <td class="text-end">
                                                <form method="post" action="<?= e(url_path('/admin/newsletter/subscribers/' . $subscriber['id'] . '/status')) ?>">
                                                    <?= csrf_input() ?><?= admin_return_input() ?>
                                                    <?php $nextStatus = ($subscriber['status'] ?? 'active') === 'active' ? 'unsubscribed' : 'active'; ?>
                                                    <input type="hidden" name="status" value="<?= e($nextStatus) ?>">
                                                    <button class="btn btn-sm btn-outline-dark rounded-pill" type="submit"><?= e($nextStatus === 'active' ? 'Reactivar' : 'Dar baja') ?></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state compact-empty"><h3>Sin suscriptores en esta vista</h3><p>Prueba otros filtros o registra un suscriptor.</p></div>
                    <?php endif; ?>
                    <?php $paginationPath = '/admin/newsletter'; require __DIR__ . '/pagination.php'; ?>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="admin-card newsletter-panel">
                    <span class="eyebrow">Envio manual</span>
                    <h2 class="h4">Enviar nuevo post</h2>
                    <p class="text-body-secondary">El envío automático depende de la configuración del sitio. Desde aquí puedes enviar un artículo o confirmar un reenvío a los suscriptores activos.</p>
                    <div class="newsletter-post-list">
                        <?php if (!$availablePosts): ?><div class="empty-state compact-empty"><h3 class="h5">Aún no hay artículos publicados</h3><p>Los artículos disponibles para enviar aparecerán aquí.</p></div><?php endif; ?>
                        <?php foreach ($availablePosts as $post): ?>
                            <article class="newsletter-post-item">
                                <div>
                                    <strong><?= e($post['title']) ?></strong>
                                    <small><?= e($post['category_name'] ?? 'Sin categoría') ?> · <?= e(!empty($post['newsletter_sent_at']) ? 'Enviado ' . date_label($post['newsletter_sent_at']) : 'No enviado') ?></small>
                                </div>
                                <form method="post" action="<?= e(url_path('/admin/newsletter/posts/' . $post['id'] . '/send')) ?>" onsubmit="return confirm('¿Enviar este artículo por correo a los suscriptores activos? Esta acción inicia un envío real.');">
                                    <?= csrf_input() ?><?= admin_return_input() ?>
                                    <?php if (!empty($post['newsletter_sent_at'])): ?><input type="hidden" name="force" value="1"><?php endif; ?>
                                    <button class="btn btn-sm btn-dark rounded-pill" type="submit"><?= e(!empty($post['newsletter_sent_at']) ? 'Reenviar' : 'Enviar') ?></button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="admin-card newsletter-panel mt-4">
                    <span class="eyebrow">Historial</span>
                    <h2 class="h4">Historial de envíos</h2>
                    <?php if ($campaigns): ?>
                        <div class="newsletter-campaign-list">
                            <?php foreach ($campaigns as $campaign): ?>
                                <article class="newsletter-campaign-item">
                                    <div>
                                        <strong><?= e($campaign['subject']) ?></strong>
                                        <small><?= e(date_label($campaign['created_at'])) ?> · <?= e($campaign['status']) ?></small>
                                    </div>
                                    <span><?= (int) $campaign['sent_count'] ?>/<?= (int) $campaign['recipient_count'] ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state compact-empty"><h3>Sin campañas</h3><p>Los intentos de envío se registran aquí con su estado y número de destinatarios.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
