<?php
declare(strict_types=1);
$directory = sys_get_temp_dir() . '/ms-seo-' . bin2hex(random_bytes(6));
mkdir($directory, 0700, true);
foreach (['MS_DB_DRIVER'=>'sqlite','MS_SQLITE_PATH'=>$directory.'/test.sqlite','MS_CONFIG_FILE'=>$directory.'/none.php','MS_APP_URL'=>'https://example.test','MS_STORAGE_PATH'=>$directory] as $name=>$value) putenv($name.'='.$value);
require dirname(__DIR__).'/blog/app/i18n.php';
require dirname(__DIR__).'/blog/app/helpers.php';
require dirname(__DIR__).'/blog/app/models.php';
require dirname(__DIR__).'/blog/app/controllers.php';
$_SERVER['SCRIPT_NAME']='/index.php'; $_SERVER['REQUEST_URI']='/';
$_GET=[]; $_SESSION=[]; $GLOBALS['locale']='en';
$passed=0;
function verify(bool $ok, string $message): void { global $passed; if (!$ok) throw new RuntimeException($message); $passed++; }
ob_start();
try {
    ensure_runtime_schema(); $GLOBALS['site']=settings();
    verify(slugify('Guía de medición: diseño y acción') === 'guia-de-medicion-diseno-y-accion', 'Spanish slugs differ between operating systems.');
    verify(seo_text('<p>Uno &amp; dos.</p><p>Tres.</p><script>alert(1)</script>')==='Uno & dos. Tres.', 'Description markup or spacing incorrect.');
    verify(mb_strlen(seo_text(str_repeat('áéíóú palabra ', 100)))<=160, 'Multibyte description exceeds limit.');
    verify(seo_description(['content_markdown'=>'## Encabezado' . "\n\n" . 'Contenido útil.'])==='Encabezado Contenido útil.', 'Markdown fallback incorrect.');
    $_SERVER['REQUEST_URI']='/es/blog?page=3&utm_source=test&category=marketing'; $_GET=['page'=>3,'utm_source'=>'test','category'=>'marketing'];
    verify(seo_canonical_url()==='https://example.test/es/blog?category=marketing&page=3', 'Canonical loses pagination or retains tracking.');
    $_SERVER['REQUEST_URI']='/marketing/article?fbclid=test'; $_GET=['fbclid'=>'test'];
    verify(seo_canonical_url()==='https://example.test/marketing/article', 'Article tracking retained.');
    $_GET=[];
    $post=['title'=>'Title </script><script>bad()</script>','author_name'=>'Writer','published_at'=>'2026-01-01T12:00:00-06:00','updated_at'=>'2026-02-01T12:00:00-06:00','content_html'=>'<p>Article body</p>'];
    $schema=seo_article_schema($post,'en','https://example.test/marketing/article',$GLOBALS['site']);
    verify($schema['@type']==='BlogPosting' && $schema['description']==='Article body' && isset($schema['dateModified']), 'Article schema missing factual metadata.');
    $encoded=json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    verify(!str_contains($encoded,'</script>') && json_decode($encoded,true)['headline']===$post['title'], 'Schema permits script breakout.');
    verify(count(seo_post_review(['title'=>'x','content_html'=>'<p>Text</p><img src="/a.png">','cover_image'=>'/cover.png','category_id'=>1]))===1, 'Missing image description not detected.');
    db()->beginTransaction();
    for ($i=1;$i<=1002;$i++) db_run('INSERT INTO posts (title,slug,excerpt,content_markdown,content_html,seo_title,seo_description,status,is_featured,author_name,read_time,created_at,updated_at,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', ['Title '.$i,'article-'.$i,'','', '<p>Base content.</p>','','',$i===1002?'draft':'published',0,'Writer',1,'2026-01-01T12:00:00+00:00','2026-01-01T12:00:00+00:00','2026-01-01T12:00:00+00:00']);
    db()->commit();
    save_post_translation(1,'es',['title'=>'Versión española','content_html'=>'<p>Descripción de la versión española.</p>']);
    $translation=get_post_translation(1,'es');
    verify($translation['seo_description']==='Descripción de la versión española.', 'Translation metadata used base-language text.');
    ob_start(); sitemap_xml(); $index=ob_get_clean();
    $xml=simplexml_load_string($index);
    verify($xml!==false && $xml->getName()==='sitemapindex' && count($xml->sitemap)===2, 'Large sitemap not split.');
    $_GET=['page'=>1]; ob_start(); sitemap_xml(); $first=ob_get_clean();
    $_GET=['page'=>2]; ob_start(); sitemap_xml(); $last=ob_get_clean();
    $lastXml=simplexml_load_string($last);
    verify(count($lastXml->url)===2, 'Second sitemap page should include both article locales.');
    verify(str_contains($last,'article-1') && !str_contains($first.$last,'article-1002'), 'Sitemap omitted published content or included a draft.');
    verify(str_contains($last,'<lastmod>') && str_contains($last,$translation['slug']), 'Sitemap lacks translated URL or modification date.');
    verify(!str_contains($first.$last,'/admin'), 'CMS leaked into sitemap.');
    $_GET=['page'=>3]; ob_start(); sitemap_xml(); ob_end_clean();
    verify(http_response_code()===404, 'Nonexistent sitemap page is indexable.');
} finally {
    ob_end_clean();
    // Only the fixture directory created above is removed.
    foreach (glob($directory.'/*') ?: [] as $file) if (is_file($file)) @unlink($file);
    @rmdir($directory);
}
echo "PASS $passed SEO checks: metadata, translation, escaping, sitemap pagination and drafts.\n";
