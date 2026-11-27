<?php

// Shared by the public renderer, the editor and the SEO review. No remote calls.
function seo_text(string $value, int $limit = 160): string
{
    $value = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $value) ?? '';
    $value = preg_replace('#</(?:p|div|li|h[1-6])>|<br\s*/?>#i', ' ', $value) ?? '';
    $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    if (mb_strlen($text) <= $limit) return $text;
    $cut = mb_substr($text, 0, $limit - 1);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $limit * .65) $cut = mb_substr($cut, 0, $space);
    return rtrim($cut, " ,.;:") . '…';
}

function seo_description(array $post): string
{
    foreach (['seo_description', 'excerpt', 'content_html', 'content_markdown'] as $key) {
        $source = trim((string) ($post[$key] ?? ''));
        if ($source === '') continue;
        if ($key === 'content_markdown') $source = markdown_to_html($source);
        $text = seo_text($source, 160);
        if ($text !== '') return $text;
    }
    return '';
}

function seo_canonical_url(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $params = [];
    if (preg_match('#/blog/?$#', $path)) {
        if (is_string($_GET['category'] ?? null) && trim($_GET['category']) !== '') $params['category'] = canonical_category_slug($_GET['category']);
        if (is_scalar($_GET['page'] ?? null) && (int) $_GET['page'] > 1) $params['page'] = (int) $_GET['page'];
    }
    return absolute_url($path) . ($params ? '?' . http_build_query($params) : '');
}

function seo_article_schema(array $post, string $locale, string $url, array $site): array
{
    if (!empty($post['id'])) {
        $translation = get_post_translation((int) $post['id'], $locale);
        if (strtotime((string) ($translation['updated_at'] ?? '')) > strtotime((string) ($post['updated_at'] ?? ''))) $post['updated_at'] = $translation['updated_at'];
    }
    $schema = [
        '@context' => 'https://schema.org', '@type' => 'BlogPosting',
        '@id' => $url . '#article', 'url' => $url, 'mainEntityOfPage' => $url,
        'headline' => $post['title'], 'description' => seo_description($post),
        'inLanguage' => $locale,
        'author' => ['@type' => 'Person', 'name' => $post['author_display_name'] ?? $post['author_name'] ?? $site['author_name'] ?? ''],
        'publisher' => ['@type' => 'Organization', 'name' => $site['site_title'] ?? 'Marketing Studies', 'url' => absolute_url(url_path('/'))],
    ];
    if (!empty($post['author_slug'])) $schema['author']['url'] = absolute_url(url_path('/author/' . $post['author_slug'], $locale));
    foreach (['published_at' => 'datePublished', 'updated_at' => 'dateModified'] as $field => $property) {
        $stamp = strtotime((string) ($post[$field] ?? ''));
        if ($stamp !== false) $schema[$property] = date(DATE_W3C, $stamp);
    }
    $image = media_url(($post['cover_image'] ?? '') ?: ($site['site_share_image'] ?? ''));
    if ($image !== '') $schema['image'] = [absolute_url($image)];
    if (!empty($post['category_name'])) $schema['articleSection'] = $post['category_name'];
    return $schema;
}

function seo_post_review(array $post): array
{
    $issues = [];
    if (seo_description($post) === '') $issues[] = 'Añade un resumen o contenido para generar la descripción.';
    if (trim((string) ($post['cover_image'] ?? '')) === '') $issues[] = 'Añade una portada propia para compartir este artículo.';
    if (empty($post['category_id'])) $issues[] = 'Selecciona una categoría para organizar el artículo.';
    $html = ($post['content_html'] ?? '') ?: markdown_to_html($post['content_markdown'] ?? '');
    if (text_word_count($html) > 250 && !preg_match('/<h[2-3]\b/i', $html)) $issues[] = 'Divide el texto en secciones con subtítulos.';
    if (preg_match('/<img\b(?![^>]*\balt\s*=\s*[\x22\x27][^\x22\x27]+[\x22\x27])[^>]*>/i', $html)) $issues[] = 'Describe las imágenes del contenido con texto alternativo.';
    return $issues;
}

function admin_seo(): void
{
    $data = admin_list_data('seo');
    view('admin/seo', $data + ['metaTitle'=>'SEO y visibilidad', 'posts'=>$data['rows']]);
}
