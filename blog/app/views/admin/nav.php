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
    ['href' => url_path('/admin'), 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'active' => $requestPath === '/admin'],
    ['href' => url_path('/admin/posts'), 'label' => 'Posts', 'icon' => 'bi-journal-richtext', 'active' => str_starts_with($requestPath, '/admin/posts') && !str_contains($requestQuery, 'category=casos-de-estudio')],
    ['href' => url_path('/admin/case-studies'), 'label' => 'Casos', 'icon' => 'bi-briefcase', 'active' => $requestPath === '/admin/case-studies' || str_contains($requestQuery, 'category=casos-de-estudio')],
    ['href' => url_path('/admin/lab-resources'), 'label' => 'Lab', 'icon' => 'bi-box-seam', 'active' => str_starts_with($requestPath, '/admin/lab-resources')],
    ['href' => url_path('/admin/categories'), 'label' => 'Categorias', 'icon' => 'bi-grid-1x2', 'active' => str_starts_with($requestPath, '/admin/categories')],
    ['href' => url_path('/admin/authors'), 'label' => 'Autores', 'icon' => 'bi-people', 'active' => str_starts_with($requestPath, '/admin/authors')],
    ['href' => url_path('/admin/comments'), 'label' => 'Comentarios', 'icon' => 'bi-chat-square-text', 'active' => str_starts_with($requestPath, '/admin/comments')],
    ['href' => url_path('/admin/newsletter'), 'label' => 'Newsletter', 'icon' => 'bi-envelope-paper', 'active' => str_starts_with($requestPath, '/admin/newsletter')],
    ['href' => url_path('/admin/settings'), 'label' => 'Configuracion', 'icon' => 'bi-sliders', 'active' => str_starts_with($requestPath, '/admin/settings')],
];
?>
<nav class="admin-nav-shell bg-white border rounded-4 shadow-sm p-2" aria-label="Navegacion del CMS">
    <div class="admin-nav d-flex align-items-center gap-2">
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
    </div>
</nav>
