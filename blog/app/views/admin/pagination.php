<?php
$page = max(1, (int) ($page ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$total = max(0, (int) ($total ?? 0));
$pageSize = max(1, (int) ($pageSize ?? 30));
$paginationPath = $paginationPath ?? '/admin/posts';
$paginationFilters = is_array($filters ?? null) ? $filters : [];
$paginationLink = static function (int $targetPage) use ($paginationPath, $paginationFilters): string {
    $query = array_filter($paginationFilters, static fn($value) => $value !== '' && $value !== null);
    $query['page'] = $targetPage;
    return url_path($paginationPath . '?' . http_build_query($query));
};
?>
<div class="admin-pagination">
    <p><?= $total ? (int) (($page - 1) * $pageSize + 1) : 0 ?>–<?= (int) min($page * $pageSize, $total) ?> de <?= $total ?> resultados</p>
    <?php if ($totalPages > 1): ?>
        <nav class="admin-pagination-controls" aria-label="Páginas de resultados">
            <?php if ($page > 1): ?><a class="btn btn-sm btn-outline-dark" href="<?= e($paginationLink($page - 1)) ?>" rel="prev">Anterior</a><?php else: ?><span class="btn btn-sm btn-outline-dark disabled" aria-disabled="true">Anterior</span><?php endif; ?>
            <span class="admin-page-number" aria-current="page">Página <?= $page ?> de <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?><a class="btn btn-sm btn-outline-dark" href="<?= e($paginationLink($page + 1)) ?>" rel="next">Siguiente</a><?php else: ?><span class="btn btn-sm btn-outline-dark disabled" aria-disabled="true">Siguiente</span><?php endif; ?>
        </nav>
    <?php endif; ?>
</div>
