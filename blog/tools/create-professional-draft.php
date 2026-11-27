<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/i18n.php';
require dirname(__DIR__) . '/app/helpers.php';
require dirname(__DIR__) . '/app/models.php';
require dirname(__DIR__) . '/app/professional.php';
$id = $argv[1] ?? 'article-01';
$template = professional_template($id);
if (!$template) { fwrite(STDERR, "Plantilla desconocida.\n"); exit(1); }
if (!in_array('--apply', $argv, true)) { echo json_encode(['mode'=>'dry-run','title'=>$template['title'],'status'=>'draft'], JSON_UNESCAPED_UNICODE) . "\n"; exit; }
ensure_runtime_schema();
$slug = 'jovani-' . $id;
db()->beginTransaction();
try {
    if (db_one('SELECT id FROM posts WHERE slug = ?', [$slug])) { db()->rollBack(); echo "El borrador ya existe; se conserva sin cambios.\n"; exit; }
    $author = db_one("SELECT id FROM authors WHERE slug = 'jovani-trevino' AND status = 'active'");
    $category = $template['category'] ? db_one('SELECT id FROM categories WHERE slug = ?', [$template['category']]) : null;
    $body = sanitize_article_html(markdown_to_html($template['content_markdown']));
    db_run("INSERT INTO posts (title,slug,excerpt,content_markdown,content_html,status,author_name,author_id,category_id,read_time,created_at,updated_at) VALUES (?,?,?,?,?,'draft','Jovani Treviño',?,?,?,?,?)", [$template['title'],$slug,$template['excerpt'],$template['content_markdown'],$body,$author['id'] ?? null,$category['id'] ?? null,estimate_read_time($body),date('c'),date('c')]);
    db()->commit();
    echo "Borrador creado. No se publicó ni se envió newsletter.\n";
} catch (Throwable $error) { if(db()->inTransaction()) db()->rollBack(); fwrite(STDERR,"No se creó el borrador. Revisa la configuración local.\n"); exit(1); }
