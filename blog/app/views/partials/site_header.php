<?php $es = $locale === 'es'; $portfolioUrl = base_path() . '/portfolio/'; ?>
<a class="ms-skip-link" href="#main-content"><?= $es ? 'Ir al contenido' : 'Skip to content' ?></a>
<header class="site-header ms-header<?= $isAdmin ? ' ms-header-admin' : '' ?>">
    <div class="ms-nav-shell container">
        <a class="ms-brand" href="<?= e($siteHomeUrl) ?>" aria-label="<?= e($siteTitle . ($es ? ', inicio' : ', home')) ?>">
            <i class="bi bi-book" aria-hidden="true"></i>
            <?php if ($siteTitle === 'Marketing Studies'): ?><span>Marketing<strong>Studies<span>.</span></strong></span><?php else: ?><span><?= e($siteTitle) ?></span><?php endif; ?>
        </a>
        <?php if (!$isAdmin): ?>
        <nav class="ms-primary-nav" id="ms-primary-nav" aria-label="<?= $es ? 'Navegación principal' : 'Main navigation' ?>">
            <a href="<?= e($blogIndexUrl) ?>" <?= $innerPath === '/blog' ? 'aria-current="page"' : '' ?>><?= $es ? 'Artículos' : 'Articles' ?></a>
            <div class="dropdown">
                <button class="ms-nav-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><?= $es ? 'Temas' : 'Topics' ?> <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
                <div class="dropdown-menu ms-topic-menu">
                    <?php foreach ($navCategories as $navCategory): ?>
                        <?php if (in_array($navCategory['slug'] ?? '', ['lab','laboratory','laboratorio'], true)) continue; ?>
                        <a class="dropdown-item" href="<?= e(url_path('/' . $navCategory['slug'], $locale)) ?>"><?= e($navCategory['name']) ?></a>
                        <?php foreach (flatten_category_tree($navCategory['children'] ?? [], 1) as $navChild): ?>
                            <a class="dropdown-item ms-topic-child" href="<?= e(url_path('/' . $navChild['slug'], $locale)) ?>"><?= e($navChild['name']) ?></a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="<?= e(url_path('/lab', $locale)) ?>" <?= $innerPath === '/lab' ? 'aria-current="page"' : '' ?>>Lab</a>
            <a href="<?= e($profilePageUrl) ?>" <?= in_array($innerPath, ['/jovani','/about'], true) ? 'aria-current="page"' : '' ?>><?= $es ? 'El autor' : 'The author' ?></a>
            <a class="ms-mobile-portfolio" href="<?= e($portfolioUrl) ?>"><?= $es ? 'Portafolio y CV' : 'Portfolio & CV' ?></a>
        </nav>
        <?php endif; ?>
        <div class="ms-header-tools">
            <?php if (!$isAdmin): ?>
            <button class="ms-icon-button" type="button" data-open-search aria-controls="ms-search-dialog" aria-label="<?= $es ? 'Buscar artículos' : 'Search articles' ?>"><i class="bi bi-search" aria-hidden="true"></i></button>
            <a class="ms-language" href="<?= e($es ? $enUrl : $esUrl) ?>" lang="<?= $es ? 'en' : 'es' ?>" hreflang="<?= $es ? 'en' : 'es' ?>"><?= $es ? 'EN' : 'ES' ?></a>
            <?php endif; ?>
            <button type="button" class="theme-toggle ms-icon-button ms-theme-toggle" id="theme-toggle" aria-label="<?= $es ? 'Cambiar tema' : 'Change theme' ?>" aria-pressed="false"><i class="bi bi-moon-stars theme-toggle-icon" aria-hidden="true"></i><span class="theme-toggle-label visually-hidden"><?= e(t('theme.dark')) ?></span></button>
            <?php if ($isAdmin): ?>
                <a class="ms-admin-site-link" href="<?= e($siteHomeUrl) ?>"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Ver sitio</a>
                <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                <form action="<?= e(url_path('/admin/logout')) ?>" method="post"><?= csrf_input() ?><button class="ms-admin-logout" type="submit">Cerrar sesión</button></form>
                <?php endif; ?>
            <?php else: ?>
                <a class="ms-portfolio-link" href="<?= e($portfolioUrl) ?>">Jovani Treviño <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                <button class="ms-icon-button ms-menu-toggle" type="button" aria-controls="ms-primary-nav" aria-expanded="false" aria-label="<?= $es ? 'Abrir menú' : 'Open menu' ?>"><i class="bi bi-list" aria-hidden="true"></i></button>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php if (!$isAdmin): ?>
<dialog class="ms-search-dialog" id="ms-search-dialog">
    <div class="ms-search-heading"><h2><?= $es ? '¿Qué quieres explorar?' : 'What would you like to explore?' ?></h2><button type="button" class="ms-icon-button" data-close-search aria-label="<?= $es ? 'Cerrar búsqueda' : 'Close search' ?>"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    <form action="<?= e($blogIndexUrl) ?>" method="get" class="ms-dialog-form">
        <label class="visually-hidden" for="ms-search-query"><?= e(t('blog.search')) ?></label>
        <i class="bi bi-search" aria-hidden="true"></i><input id="ms-search-query" name="q" type="search" placeholder="<?= $es ? 'Analítica, estrategia, comportamiento…' : 'Analytics, strategy, behaviour…' ?>" value="<?= e($headerSearchQuery) ?>" maxlength="200" autocomplete="off">
        <button class="btn btn-dark" type="submit"><?= $es ? 'Buscar' : 'Search' ?></button>
    </form>
    <p><?= $es ? 'Explora ideas. Conecta aprendizajes.' : 'Explore ideas. Connect the learnings.' ?></p>
</dialog>
<?php endif; ?>
