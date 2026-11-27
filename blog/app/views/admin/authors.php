<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header">
            <div>
                <span class="eyebrow">Equipo editorial</span>
                <h1>Autores</h1>
                <p>Administra perfiles, fotos, biografía y enlaces sociales para mostrar autores reales en el blog.</p>
            </div>
            <a href="<?= e(url_path('/admin/authors/new')) ?>" class="btn btn-dark rounded-pill px-4">Nuevo autor</a>
        </div>

        <div class="admin-card mb-4"><?php $listPath = '/admin/authors'; $filterLabel = 'Filtrar autores'; $filterStates = ['active'=>'Activo','inactive'=>'Inactivo']; $searchHint = 'Nombre, rol o biografía…'; require __DIR__ . '/list_filters.php'; ?></div>
        <?php if (!$authors): ?>
            <div class="empty-state">
                <h2 class="h4">Sin autores en esta vista</h2>
                <p>Busca otro nombre o crea un autor para asignarlo a tus artículos.</p>
                <a href="<?= e(url_path('/admin/authors/new')) ?>" class="btn btn-dark rounded-pill px-4">Crear autor</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($authors as $author): ?>
                    <?php
                    $professionalLinks = author_visible_professional_links($author);
                    $authorOrcidId = orcid_normalize_id((string) ($author['orcid_id'] ?? ''));
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="admin-card admin-author-card">
                            <div class="admin-author-head">
                                <img src="<?= e(author_avatar_url($author['name'], $author['avatar_url'] ?? '')) ?>" alt="<?= e($author['name']) ?>" class="admin-author-avatar" loading="lazy" decoding="async">
                                <span class="badge <?= ($author['status'] ?? 'active') === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= ($author['status'] ?? 'active') === 'active' ? 'Activo' : 'Inactivo' ?></span>
                            </div>
                            <h2 class="h4 mb-1"><?= e($author['name']) ?></h2>
                            <p class="mb-2"><?= e($author['role'] ?: 'Autor') ?></p>
                            <small class="text-body-secondary d-block mb-3">/author/<?= e($author['slug']) ?> · <?= (int) ($author['total_posts'] ?? 0) ?> posts</small>
                            <?php if ($authorOrcidId !== ''): ?>
                                <a class="author-orcid-badge admin-orcid-badge mb-3" href="<?= e(orcid_profile_url($authorOrcidId)) ?>" target="_blank" rel="noopener noreferrer">
                                    <span>iD</span>
                                    <strong><?= e($authorOrcidId) ?></strong>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($author['bio'])): ?>
                                <p class="admin-author-bio"><?= e($author['bio']) ?></p>
                            <?php endif; ?>
                            <div class="admin-author-links">
                                <?php foreach ($professionalLinks as $link): ?>
                                    <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($link['label']) ?>" title="<?= e($link['label']) ?>"><i class="<?= e($link['icon']) ?>"></i></a>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mt-4">
                                <a href="<?= e(url_path('/admin/authors/' . $author['id'] . '/edit')) ?>" class="btn btn-sm btn-dark rounded-pill">Editar</a>
                                <?php if (($author['status'] ?? 'active') === 'active'): ?><a href="<?= e(url_path('/author/' . $author['slug'])) ?>" class="btn btn-sm btn-outline-secondary rounded-pill" target="_blank" rel="noopener">Ver perfil</a><?php endif; ?>
                                <form method="post" action="<?= e(url_path('/admin/authors/' . $author['id'] . '/delete')) ?>" onsubmit="return confirm('Eliminar este autor? Los posts conservaran autor manual.');">
                                    <?= csrf_input() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Eliminar</button>
                                </form>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php $paginationPath = '/admin/authors'; require __DIR__ . '/pagination.php'; ?>
    </div>
</section>
