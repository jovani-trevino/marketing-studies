<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/cases.php';
jovani_boot_blog('es');
$site = $GLOBALS['site'];
$homeUrl = jovani_app_url('/');
$cvUrl = jovani_app_url('/cv');
$blogUrl = url_path('/', 'es');
$cases = professional_cases();
$areas = professional_areas();
$selectedCase = null;
$caseLibrary = $caseLibrary ?? false;
$libraryUrl = jovani_app_url('/casos');
if (isset($caseRouteSlug) || isset($_GET['caso'])) {
    $slug = $caseRouteSlug ?? (is_string($_GET['caso']) ? $_GET['caso'] : '');
    foreach ($cases as $case) if ($case['id'] === $slug) $selectedCase = $case;
    if (!$selectedCase) { http_response_code(404); header('Content-Type: text/html; charset=utf-8'); echo '<h1>Caso no encontrado</h1><a href="'.e($homeUrl).'">Volver al portafolio</a>'; exit; }
}
$siteTitle = ($selectedCase ? $selectedCase['title'] : ($caseLibrary ? 'Casos de estudio' : 'Especialista en Marketing Intelligence Digital')) . ' | Jovani Treviño';
$description = $selectedCase['summary'] ?? 'Jovani Treviño: análisis de datos, estrategia de marketing, campañas y automatización. Casos de estudio con resultados documentados y clientes anonimizados.';
$canonical = $selectedCase ? professional_case_url($selectedCase) : ($caseLibrary ? $libraryUrl : $homeUrl);
$contactEmail = 'jovani@marketing-studies.com';
$latestPosts = array_slice(array_filter(published_posts(30), static fn(array $post): bool => preg_match('/^Jovani\s+Trevi[ñn]o$/iu', trim((string)($post['author_name'] ?? ''))) === 1), 0, 3);
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($siteTitle) ?></title><meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e(absolute_url($canonical)) ?>"><meta property="og:url" content="<?= e(absolute_url($canonical)) ?>">
<meta property="og:title" content="<?= e($siteTitle) ?>"><meta property="og:description" content="<?= e($description) ?>"><meta property="og:type" content="<?= $selectedCase ? 'article' : 'profile' ?>">
<meta property="og:image" content="<?= e(absolute_url(jovani_local_asset_url('assets/profile-studio-720.webp'))) ?>"><meta name="theme-color" content="#102638">
<link rel="icon" href="<?= e(media_url('/static/brand/favicon.png')) ?>">
<link rel="stylesheet" href="<?= e(jovani_local_asset_url('site.css')) ?>"><link rel="stylesheet" href="<?= e(jovani_local_asset_url('assets/intelligence.css')) ?>">
<link rel="stylesheet" href="<?= e(jovani_local_asset_url('assets/case-editorial.css')) ?>">
<link rel="stylesheet" href="<?= e(brand_asset_url('brand.css')) ?>"><script src="<?= e(brand_asset_url('brand-theme.js')) ?>"></script>
<link rel="stylesheet" href="<?= e(jovani_local_asset_url('assets/case-studio.css')) ?>">
<link rel="stylesheet" href="<?= e(jovani_local_asset_url('assets/case-work.css')) ?>">
</head><body class="jt-page">
<a class="jt-skip-link" href="#contenido">Saltar al contenido</a>
<header class="jt-header"><div class="jt-header-row">
<a class="jt-sidebar-name" href="<?= e($homeUrl) ?>"><span class="jt-sidebar-fullname">Jovani Treviño<span class="cs-brand-dot">.</span></span><small>Marketing Studies</small></a>
<button class="jt-nav-toggle" type="button" aria-expanded="false" aria-controls="jt-nav" data-nav-toggle><span></span><span></span><span></span><span class="visually-hidden">Abrir menú</span></button>
<nav class="jt-nav" id="jt-nav" data-nav aria-label="Secciones del portafolio">
<?php foreach (['perfil'=>'Perfil','portafolio'=>'Casos de estudio','experiencia'=>'Trayectoria','blog'=>'Blog','contacto'=>'Contacto'] as $id=>$label): ?><a href="<?= e($id === 'portafolio' ? $libraryUrl : (($selectedCase || $caseLibrary ? $homeUrl : '') . '#' . $id)) ?>"<?= $id === 'portafolio' && ($selectedCase || $caseLibrary) ? ' class="is-active" aria-current="page"' : '' ?>><span><?= e($label) ?></span></a><?php endforeach; ?>
<button type="button" class="brand-theme-toggle brand-theme-mobile" data-brand-theme-toggle aria-pressed="false"><span data-brand-theme-label>Cambiar tema</span></button>
</nav><div class="jt-sidebar-foot"><span>Marketing Studies</span><a href="<?= e($blogUrl) ?>">Explorar el blog</a><a href="<?= e($cvUrl) ?>">Abrir currículum</a><a href="https://orcid.org/0009-0004-9676-9136" rel="noopener noreferrer" target="_blank">Perfil ORCID</a><button type="button" class="brand-theme-toggle" data-brand-theme-toggle aria-pressed="false"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8"/><path d="M12 4a8 8 0 0 1 0 16Z" fill="currentColor"/></svg><span data-brand-theme-label>Cambiar tema</span></button></div>
</div></header>
<main class="jt-main" id="contenido">
<?php if ($selectedCase): require __DIR__ . '/case-detail.php'; ?>
<?php elseif ($caseLibrary): require __DIR__ . '/case-library.php'; ?>
<?php else: ?>
<section class="jt-hero" id="perfil"><div class="jt-container jt-hero-grid"><div class="jt-hero-copy">
<span class="jt-kicker">Especialista en Marketing Intelligence Digital</span><h1><span>Datos que explican.</span><span>Estrategia que conecta.</span></h1>
<p class="jt-lead">Soy Jovani Treviño. Conecto análisis de datos, campañas y tecnología para entender audiencias, orientar decisiones comerciales y construir mejores experiencias digitales.</p>
<p class="jt-about-text">Mi trabajo va del diagnóstico a la ejecución: publicidad digital, lectura de resultados, contenido, desarrollo web y automatización. Aquí presento proyectos documentados con la identidad de los clientes protegida.</p>
<div class="jt-hero-actions"><a class="jt-btn jt-btn-primary" href="#portafolio">Explorar casos de estudio</a><a class="jt-btn jt-btn-secondary" href="<?= e($cvUrl) ?>">Ver currículum</a></div>
<div class="mi-hero-tags"><span>Analítica aplicada</span><span>Paid media</span><span>CRM y automatización</span></div>
</div><figure class="jt-hero-portrait"><img src="<?= e(jovani_local_asset_url('assets/profile-studio-720.webp')) ?>" width="720" height="900" alt="Retrato profesional de Jovani Treviño" fetchpriority="high"><figcaption><strong>Jovani Treviño</strong><span>Estrategia · Datos · Tecnología</span></figcaption></figure></div></section>
<section class="jt-section mi-selected" id="portafolio"><div class="jt-container"><div class="jt-section-heading"><span class="jt-kicker">Trabajo seleccionado</span><h2>Resultados con contexto.<br>Decisiones con fundamento.</h2><p>Casos de campañas, audiencias y experiencias digitales. Cada caso explica mi participación, el periodo analizado y lo que la evidencia permite afirmar.</p></div>
<?php require __DIR__ . '/case-cards.php'; ?></div></section>
<section class="jt-section" id="experiencia"><div class="jt-container"><div class="jt-section-heading"><span class="jt-kicker">Trayectoria</span><h2>Marketing, análisis y ejecución digital.</h2><p>Una trayectoria que conecta comunicación, tecnología y lectura comercial en distintos sectores.</p></div>
<div class="mi-timeline">
<article><span>2022–2026 · Automotriz</span><h3>Inteligencia digital y operación comercial</h3><p>Análisis de campañas, social media y mensajería. Desarrollo de soporte web, CRM y automatizaciones para conectar inventario, captación y seguimiento.</p></article>
<article><span>Desde 2016 · Entretenimiento</span><h3>Coordinación de marketing digital</h3><p>Calendarización, campañas, contenidos y reportes multicanal. Lectura de visualizaciones, adquisición de audiencia y retención para orientar la promoción.</p></article>
<article><span>2019–2021 · Comercio y servicios</span><h3>SEO/SEM y análisis de campañas</h3><p>Investigación de palabras clave, segmentación, estructura de anuncios y reportes para campañas de Búsqueda y Display.</p></article>
<article><span>2014–2016 · Tecnología inmobiliaria</span><h3>Marketing digital y experiencia de producto</h3><p>Materiales comerciales, capacitación de CRM, email marketing, interfaces y apoyo a la difusión de inventarios.</p></article>
</div></div></section>
<section class="jt-section mi-method" id="metodo"><div class="jt-container"><span class="jt-kicker">Mi método</span><h2>Entender antes de optimizar.</h2><div class="mi-method-grid"><article><h3>Definir la pregunta</h3><p>Traducir el objetivo comercial en una pregunta que pueda responderse con datos.</p></article><article><h3>Revisar la evidencia</h3><p>Comprobar periodo, definición, fuente y comparabilidad de cada indicador.</p></article><article><h3>Conectar la operación</h3><p>Relacionar campaña, contenido, experiencia web y seguimiento comercial.</p></article><article><h3>Documentar decisiones</h3><p>Separar el resultado observado de una hipótesis y de la siguiente acción.</p></article></div></div></section>
<section class="jt-section" id="cv"><div class="jt-container mi-cv-grid"><div><span class="jt-kicker">Perfil profesional</span><h2>Especialista en Marketing Intelligence Digital.</h2><p>Licenciado en Ciencias de la Comunicación, con especialidad en Mercadotecnia, por la Universidad Autónoma de Nuevo León. Formación 2010–2014.</p><p>Analítica aplicada · Google Ads · Meta Ads · Investigación de mercados · Contenido · Experiencia web · CRM · Automatización.</p><div class="jt-cv-actions"><a class="jt-btn jt-btn-primary" href="<?= e($cvUrl) ?>">Abrir CV imprimible</a><a class="jt-btn jt-btn-secondary" href="<?= e(jovani_local_asset_url('assets/downloads/jovani-cv.pdf')) ?>" download>Descargar CV PDF</a><a class="jt-btn jt-btn-secondary" href="<?= e(jovani_local_asset_url('assets/downloads/jovani-portafolio.pdf')) ?>" download>Portafolio PDF</a></div></div><aside class="mi-profile-card"><span class="jt-kicker">Identidad profesional</span><h3>Jovani Treviño</h3><p>Marketing Intelligence Digital</p><a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a><p><a href="https://orcid.org/0009-0004-9676-9136" target="_blank" rel="noopener noreferrer">ORCID · 0009-0004-9676-9136</a></p></aside></div></section>
<section class="jt-section" id="blog"><div class="jt-container"><span class="jt-kicker">Marketing Studies</span><h2>El análisis también se comparte.</h2><p>Artículos sobre medición, estrategia, campañas y experiencias digitales.</p>
<?php if($latestPosts): ?><div class="jt-case-grid"><?php foreach($latestPosts as $post): ?><article class="jt-case-card"><h3><a href="<?= e(post_url($post)) ?>"><?= e($post['title']) ?></a></h3><p><?= e($post['excerpt']) ?></p></article><?php endforeach; ?></div><?php endif; ?>
<a class="jt-btn jt-btn-secondary" href="<?= e($blogUrl) ?>">Leer Marketing Studies</a></div></section>
<section class="jt-section jt-contact-section" id="contacto"><div class="jt-container"><span class="jt-kicker">Trabajemos juntos</span><h2>Una mejor lectura de tus datos.<br>Una dirección más clara para tu marketing.</h2><p>Disponible para posiciones de Marketing Intelligence Digital, consultoría y proyectos que conecten estrategia, análisis y ejecución.</p><a class="jt-btn jt-btn-primary" href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a><p class="mi-confidentiality">Confidencialidad por diseño: los casos públicos omiten la identidad de clientes y sus activos de marca. Los resultados se presentan con periodo, definición y contexto.</p></div></section>
<?php endif; ?>
<footer class="mi-footer">Jovani Treviño · Marketing Studies · <?= date('Y') ?></footer>
</main><script src="<?= e(jovani_local_asset_url('site.js')) ?>" defer></script><script src="<?= e(jovani_local_asset_url('assets/intelligence.js')) ?>" defer></script></body></html>
