<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = base_path();
if ($base !== '' && str_starts_with($requestPath, $base)) {
    $requestPath = substr($requestPath, strlen($base)) ?: '/';
}
$requestPath = '/' . trim($requestPath, '/');
$requestPath = $requestPath === '/' ? '/' : rtrim($requestPath, '/');
$requestQuery = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);

$adminLinks = [
    ['href' => url_path('/admin'), 'label' => 'Resumen', 'icon' => 'bi-speedometer2', 'active' => $requestPath === '/admin'],
    ['href' => url_path('/admin/professional'), 'label' => 'Mi perfil y agenda', 'icon' => 'bi-person-workspace', 'active' => $requestPath === '/admin/professional'],
    ['href' => url_path('/admin/posts'), 'label' => 'Artículos', 'icon' => 'bi-journal-richtext', 'active' => str_starts_with($requestPath, '/admin/posts') && empty($caseMode)],
    ['href' => url_path('/admin/case-studies'), 'label' => 'Casos', 'icon' => 'bi-briefcase', 'active' => $requestPath === '/admin/case-studies' || !empty($caseMode)],
    ['href' => url_path('/admin/lab-resources'), 'label' => 'Lab', 'icon' => 'bi-box-seam', 'active' => str_starts_with($requestPath, '/admin/lab-resources')],
    ['href' => url_path('/admin/categories'), 'label' => 'Categorías', 'icon' => 'bi-grid-1x2', 'active' => str_starts_with($requestPath, '/admin/categories')],
    ['href' => url_path('/admin/authors'), 'label' => 'Autores', 'icon' => 'bi-people', 'active' => str_starts_with($requestPath, '/admin/authors')],
    ['href' => url_path('/admin/comments'), 'label' => 'Comentarios', 'icon' => 'bi-chat-square-text', 'active' => str_starts_with($requestPath, '/admin/comments')],
    ['href' => url_path('/admin/contacts'), 'label' => 'Mensajes', 'icon' => 'bi-inbox', 'active' => str_starts_with($requestPath, '/admin/contacts')],
    ['href' => url_path('/admin/newsletter'), 'label' => 'Newsletter', 'icon' => 'bi-envelope-paper', 'active' => str_starts_with($requestPath, '/admin/newsletter')],
    ['href' => url_path('/admin/seo'), 'label' => 'SEO y visibilidad', 'icon' => 'bi-search-heart', 'active' => $requestPath === '/admin/seo'],
    ['href' => url_path('/admin/settings'), 'label' => 'Ajustes', 'icon' => 'bi-sliders', 'active' => str_starts_with($requestPath, '/admin/settings')],
];
?>
<div class="admin-nav-shell" data-admin-menu>
    <div class="studio-identity"><span class="studio-monogram">ms.</span><div><strong>Marketing Studies</strong><small>Espacio editorial</small></div></div>
    <div class="admin-nav-heading"><span>TU ESPACIO DE TRABAJO</span><button type="button" class="admin-nav-toggle" data-admin-nav-toggle aria-controls="admin-sections" aria-expanded="true">Secciones <i class="bi bi-chevron-down" aria-hidden="true"></i></button></div>
    <nav id="admin-sections" class="admin-nav" aria-label="Navegación del CMS">
        <?php foreach ($adminLinks as $link): ?>
            <a
                href="<?= e($link['href']) ?>"
                class="admin-nav-link btn btn-sm rounded-pill<?= $link['active'] ? ' is-active' : '' ?>"
                <?= $link['active'] ? ' aria-current="page"' : '' ?>
            >
                <i class="bi <?= e($link['icon']) ?>" aria-hidden="true"></i>
                <span><?= e($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <a class="studio-nav-note" href="<?= e(url_path('/admin/seo')) ?>"><i class="bi bi-stars" aria-hidden="true"></i><strong>Publica con lo esencial listo</strong><span>Metadatos y sitemap se actualizan con tu contenido.</span></a>
</div>
