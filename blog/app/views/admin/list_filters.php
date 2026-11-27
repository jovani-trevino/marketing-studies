<form class="admin-filter-bar" method="get" action="<?= e(url_path($listPath)) ?>" role="search" aria-label="<?= e($filterLabel) ?>">
    <div class="admin-search-field"><label for="workspace-search">Buscar</label><input id="workspace-search" type="search" name="q" class="form-control" maxlength="200" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= e($searchHint ?? 'Escribe para buscar…') ?>"></div>
    <?php if (!empty($filterStates)): ?><div><label for="workspace-status">Estado</label><select id="workspace-status" name="status" class="form-select"><option value="">Todos los estados</option><?php foreach ($filterStates as $value => $label): ?><option value="<?= e($value) ?>"<?= ($filters['status'] ?? '') === $value ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <button class="btn btn-dark" type="submit">Filtrar</button>
    <?php if (array_filter($filters ?? [])): ?><a class="btn btn-outline-dark" href="<?= e(url_path($listPath)) ?>">Limpiar</a><?php endif; ?>
</form>
