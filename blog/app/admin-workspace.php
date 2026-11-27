<?php

// Only scalar, bounded values are accepted from list filters and return links.
function admin_filter_value(string $key, ?array $source = null): string
{
    $value = ($source ?? $_GET)[$key] ?? '';
    return is_string($value) || is_int($value) ? mb_substr(trim((string) $value), 0, 200) : '';
}

function admin_list_data(string $module): array
{
    $definitions = [
        'comments' => ['comments JOIN posts ON posts.id = comments.post_id LEFT JOIN categories ON categories.id = posts.category_id', 'comments', ['pending','approved','rejected','spam'], ['comments.author_name','comments.author_email','comments.body','posts.title'], 'comments.*, posts.id AS post_id, posts.title AS post_title, posts.slug AS post_slug, posts.status AS post_status, categories.slug AS category_slug', 'created_at'],
        'newsletter' => ['newsletter_subscribers', 'newsletter_subscribers', ['active','unsubscribed'], ['email','source'], '*', 'created_at'],
        'lab-resources' => ['lab_resources', 'lab_resources', ['draft','published'], ['title','description','resource_type','source_name'], '*', 'updated_at'],
        'seo' => ['posts LEFT JOIN categories ON categories.id = posts.category_id', 'posts', ['draft','published'], ['posts.title','posts.excerpt','posts.slug'], 'posts.*, categories.slug AS category_slug', 'updated_at'],
    ];
    [$from, $table, $states, $columns, $select, $date] = $definitions[$module];
    $filters = ['q' => admin_filter_value('q'), 'status' => admin_filter_value('status')];
    if (!in_array($filters['status'], $states, true)) $filters['status'] = '';
    $where = []; $params = [];
    if ($filters['status'] !== '') { $where[] = "$table.status = ?"; $params[] = $filters['status']; }
    if ($filters['q'] !== '') {
        $where[] = '(' . implode(' OR ', array_map(static fn($column) => "$column LIKE ? ESCAPE '!'", $columns)) . ')';
        array_push($params, ...array_fill(0, count($columns), search_like_pattern($filters['q'])));
    }
    $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $total = (int) db_one("SELECT COUNT(*) AS total FROM $from$clause", $params)['total'];
    $pageSize = 30; $totalPages = max(1, (int) ceil($total / $pageSize));
    $page = min($totalPages, max(1, (int) admin_filter_value('page')));
    $rows = db_all("SELECT $select FROM $from$clause ORDER BY $table.$date DESC, $table.id DESC LIMIT ? OFFSET ?", [...$params, $pageSize, ($page - 1) * $pageSize]);
    return compact('rows','filters','total','pageSize','totalPages','page');
}

function admin_return_query(array $source): string
{
    $values = [];
    foreach (['q','status','category','page'] as $key) {
        $value = admin_filter_value($key, $source);
        if ($value !== '') $values[$key] = $key === 'page' ? max(1, (int) $value) : $value;
    }
    return http_build_query($values);
}

function admin_return_input(): string
{
    return '<input type="hidden" name="return_query" value="' . e(admin_return_query($_GET)) . '">';
}

function admin_list_return(string $path): string
{
    $values = [];
    $raw = $_POST['return_query'] ?? '';
    parse_str(is_string($raw) ? substr($raw, 0, 4096) : '', $values);
    $query = admin_return_query($values);
    return url_path($path . ($query !== '' ? '?' . $query : ''));
}

function admin_category_options(): array
{
    return array_map(static function (array $category): array {
        $category['slug'] = $category['base_slug'] ?? $category['slug'];
        return $category;
    }, public_category_options(false));
}

function admin_filter_collection(array $rows, array $fields, array $states = []): array
{
    $filters = ['q'=>admin_filter_value('q'), 'status'=>admin_filter_value('status')];
    if (!in_array($filters['status'], $states, true)) $filters['status'] = '';
    $rows = array_values(array_filter($rows, static function ($row) use ($filters, $fields): bool {
        if ($filters['status'] !== '' && ($row['status'] ?? '') !== $filters['status']) return false;
        $text = implode(' ', array_map(static fn($key) => $row[$key] ?? '', $fields));
        return $filters['q'] === '' || mb_stripos($text, $filters['q']) !== false;
    }));
    $total = count($rows); $pageSize = 30; $totalPages = max(1, (int) ceil($total / $pageSize));
    $page = min($totalPages, max(1, (int) admin_filter_value('page')));
    $rows = array_slice($rows, ($page - 1) * $pageSize, $pageSize);
    return compact('rows','filters','total','pageSize','totalPages','page');
}

function admin_post_list_path(?int $categoryId): string
{
    $category = $categoryId ? db_one('SELECT slug FROM categories WHERE id = ?', [$categoryId]) : null;
    return $category && canonical_category_slug($category['slug']) === 'casos-de-estudio' ? '/admin/case-studies' : '/admin/posts';
}
