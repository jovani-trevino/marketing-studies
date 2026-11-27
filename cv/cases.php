<?php

declare(strict_types=1);

$allPublicCases = json_decode((string) file_get_contents(__DIR__ . '/cases-data.json'), true, flags: JSON_THROW_ON_ERROR);
$isCaseEs = $caseLocale === 'es';
$i = $isCaseEs ? 0 : 1;
$caseBase = $isCaseEs ? '/casos-anonimos' : '/anonymous-case-studies';
$caseAltBase = $isCaseEs ? '/anonymous-case-studies' : '/casos-anonimos';
$caseOrigin = 'https://marketing-studies.com';
$caseSafe = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$currentCase = null;
foreach ($allPublicCases as $row) {
    if ($row['slug'] === $caseSlug) {
        $currentCase = $row;
        break;
    }
}
$notFound = $caseSlug !== '' && $currentCase === null;
$casePath = $caseBase . ($currentCase ? '/' . $currentCase['slug'] : '');
$caseAltPath = $caseAltBase . ($currentCase ? '/' . $currentCase['slug'] : '');
if ($notFound) {
    http_response_code(404);
    header('X-Robots-Tag: noindex, nofollow');
} elseif (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) !== $casePath) {
    header('Location: ' . $casePath, true, 301);
    exit;
}
header('Content-Type: text/html; charset=UTF-8');
if ($caseMethod === 'HEAD') exit;
$caseText = $isCaseEs ? [
    'title' => 'Casos de estudio',
    'description' => 'Trabajo en campañas, medición, automatización y desarrollo web. Presento entregables y resultados sin identificar clientes.',
    'intro' => 'Presento el reto, lo que hice y qué quedó medido o comprobado. Las identidades de clientes están omitidas. Las imágenes son reconstrucciones editoriales, no capturas de las herramientas del cliente.',
    'work' => 'Qué hice',
    'results' => 'Qué muestran los resultados',
    'limits' => 'Cómo leer estas cifras',
    'back' => 'Todos los casos',
    'cv' => 'Ver CV',
    'portfolio' => 'Portafolio',
    'contact' => 'Contacto',
    'skip' => 'Saltar al contenido',
    'nav' => 'Navegación del portafolio',
    'groups' => ['campaigns' => 'Campañas y audiencias', 'systems' => 'Datos, CRM y automatización', 'web' => 'Desarrollo y experiencia web'],
    'diagram' => 'Reconstrucción editorial · No es una captura real',
    'diagram_alt' => 'Mockup anónimo del tipo de trabajo en este caso',
    'read' => 'Ver caso',
    'unavailable' => 'No encontramos ese caso',
    'unavailable_desc' => 'La dirección no corresponde a un caso de estudio. Puedes volver al índice.',
    'period' => 'Uso periodos, valores redondeados y notas de atribución para dar contexto sin revelar información que permita identificar a un cliente. No sumo indicadores de diferentes proyectos.',
    'artifacts' => 'Método',
] : [
    'title' => 'Case studies',
    'description' => 'Work in campaigns, measurement, automation, and web development. Deliverables and results without identifying clients.',
    'intro' => 'I share the challenge, what I did, and what was measured or verified. Client identities are withheld. Images are editorial reconstructions, not screenshots from client tools.',
    'work' => 'What I did',
    'results' => 'What the results show',
    'limits' => 'How to read these figures',
    'back' => 'All case studies',
    'cv' => 'View CV',
    'portfolio' => 'Portfolio',
    'contact' => 'Contact',
    'skip' => 'Skip to content',
    'nav' => 'Portfolio navigation',
    'groups' => ['campaigns' => 'Campaigns and audiences', 'systems' => 'Data, CRM, and automation', 'web' => 'Web development and experience'],
    'diagram' => 'Editorial reconstruction · Not an actual screenshot',
    'diagram_alt' => 'Anonymous mockup of this type of work',
    'read' => 'View case',
    'unavailable' => 'Case study not found',
    'unavailable_desc' => 'This address does not match a case study. You can return to the index.',
    'period' => 'Periods, rounded figures, and attribution notes provide context without exposing details that could identify a client. I do not combine figures from different projects.',
    'artifacts' => 'Method',
];
$caseTitle = $notFound ? $caseText['unavailable'] : ($currentCase ? $currentCase['title'][$i] : $caseText['title']);
$caseDescription = $notFound ? $caseText['unavailable_desc'] : ($currentCase ? $currentCase['desc'][$i] : $caseText['description']);
$caseMedia = static fn (array $row): string => '/static/cases/cas-' . $row['asset'] . '.svg';
?>
<!doctype html>
<html lang="<?= $caseSafe($caseLocale) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $caseSafe($caseTitle) ?> | Jovani Treviño · Marketing Studies</title>
<meta name="description" content="<?= $caseSafe($caseDescription) ?>">
<?php if (!$notFound): ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="Marketing Studies">
<meta property="og:title" content="<?= $caseSafe($caseTitle) ?>">
<meta property="og:description" content="<?= $caseSafe($caseDescription) ?>">
<meta property="og:url" content="<?= $caseSafe($caseOrigin . $casePath) ?>">
<meta property="og:locale" content="<?= $isCaseEs ? 'es_MX' : 'en_US' ?>">
<meta property="og:image" content="<?= $caseSafe($caseOrigin . ($currentCase ? $caseMedia($currentCase) : '/static/cases/cas-agency.svg')) ?>">
<meta property="og:image:alt" content="<?= $caseSafe($caseText['diagram_alt']) ?>">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => $currentCase ? 'WebPage' : 'CollectionPage',
    'name' => $caseTitle, 'description' => $caseDescription, 'inLanguage' => $caseLocale,
    'url' => $caseOrigin . $casePath, 'author' => ['@type'=>'Person','name'=>'Jovani Treviño'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) ?></script>
<?php endif; ?>
<?php if (!$notFound): ?>
<link rel="canonical" href="<?= $caseSafe($caseOrigin . $casePath) ?>">
<link rel="alternate" hreflang="<?= $isCaseEs ? 'en' : 'es' ?>" href="<?= $caseSafe($caseOrigin . $caseAltPath) ?>">
<?php endif; ?>
<link rel="stylesheet" href="/cv/cases.css">
</head>
<body class="pc-body">
<a class="pc-skip" href="#case-main"><?= $caseSafe($caseText['skip']) ?></a>
<header class="pc-header">
<nav class="pc-wrap pc-top" aria-label="<?= $caseSafe($caseText['nav']) ?>">
<a class="pc-brand" href="/portfolio/">Jovani Treviño <small>Marketing Studies</small></a>
<div>
<a href="/cv"><?= $caseSafe($caseText['cv']) ?></a>
<a href="/portfolio/"><?= $caseSafe($caseText['portfolio']) ?></a>
<a href="/portfolio/#contacto"><?= $caseSafe($caseText['contact']) ?></a>
<a href="<?= $caseSafe($caseAltPath) ?>" hreflang="<?= $isCaseEs ? 'en' : 'es' ?>" lang="<?= $isCaseEs ? 'en' : 'es' ?>"><?= $isCaseEs ? 'EN' : 'ES' ?></a>
</div>
</nav>
</header>
<main id="case-main" class="pc-wrap">
<?php if ($notFound): ?>
<header class="pc-hero">
<h1><?= $caseSafe($caseTitle) ?></h1><p><?= $caseSafe($caseDescription) ?></p>
<a class="pc-button" href="<?= $caseSafe($caseBase) ?>"><?= $caseSafe($caseText['back']) ?></a>
</header>
<?php elseif ($currentCase): ?>
<article class="pc-detail">
<header class="pc-hero">
<a class="pc-back" href="<?= $caseSafe($caseBase) ?>">← <?= $caseSafe($caseText['back']) ?></a>
<p class="pc-kicker"><?= $caseSafe($caseText['groups'][$currentCase['group']]) ?></p>
<h1><?= $caseSafe($caseTitle) ?></h1>
<p><?= $caseSafe($caseDescription) ?></p>
</header>
<figure class="pc-media">
<img src="<?= $caseSafe($caseMedia($currentCase)) ?>" width="960" height="540" decoding="async" alt="<?= $caseSafe($caseText['diagram_alt']) ?>">
<figcaption><?= $caseSafe($caseText['diagram']) ?></figcaption>
</figure>
<div class="pc-content">
<section aria-labelledby="case-work"><h2 id="case-work"><?= $caseSafe($caseText['work']) ?></h2><ul>
<?php foreach ($currentCase['work'] as $bullet): ?><li><?= $caseSafe($bullet[$i]) ?></li><?php endforeach; ?>
</ul></section>
<section aria-labelledby="case-results"><h2 id="case-results"><?= $caseSafe($caseText['results']) ?></h2>
<dl class="pc-metrics">
<?php foreach ($currentCase['metrics'] as [$metric, $labelEs, $labelEn]): ?><div><dt><?= $caseSafe($i === 0 ? $labelEs : $labelEn) ?></dt><dd><?= $caseSafe($metric) ?></dd></div><?php endforeach; ?>
</dl>
<?php if ($currentCase['slug'] === 'lanzamientos'): ?>
<figure class="pc-chart">
<h3><?= $isCaseEs ? 'Costo por clic relativo en TikTok' : 'Relative cost per click on TikTok' ?></h3>
<dl>
<div><dt><?= $isCaseEs ? 'Creativo de referencia' : 'Reference creative' ?></dt><dd><span class="pc-bar" style="--bar:100%"></span><strong>100</strong></dd></div>
<div><dt><?= $isCaseEs ? 'Otro creativo' : 'Other creative' ?></dt><dd><span class="pc-bar pc-bar-2" style="--bar:30%"></span><strong>≈30</strong></dd></div>
</dl>
<figcaption><?= $isCaseEs ? 'Índice: CPC de referencia = 100. Comparación histórica entre dos creativos de la misma plataforma; no fue un experimento controlado.' : 'Index: reference CPC = 100. Historical comparison of two creatives on the same platform; not a controlled experiment.' ?></figcaption>
</figure>
<?php elseif (in_array($currentCase['slug'], ['captacion','evento'], true)): ?>
<?php $isContact = $currentCase['slug'] === 'captacion'; $ctrFill = $isContact ? 27 : 11; $ctrLabel = $isContact ? '≈27%' : '>11%'; ?>
<figure class="pc-chart">
<h3><?= $isCaseEs ? 'Tasa de clics en búsqueda' : 'Search click-through rate' ?></h3>
<dl><div><dt><?= $isCaseEs ? 'CTR reportado' : 'Reported CTR' ?></dt><dd><span class="pc-bar pc-bar-2" style="--bar:<?= $ctrFill ?>%"></span><strong><?= $caseSafe($ctrLabel) ?></strong></dd></div></dl>
<figcaption><?= $isCaseEs
    ? ($isContact ? 'Escala 0–100%. Clics ÷ impresiones en el mismo reporte histórico de búsqueda, cifra aproximada. No es tasa de conversión.' : 'Escala 0–100%. La barra marca un mínimo: el CTR histórico fue superior a 11%. No lo comparo con las reproducciones de video de otro periodo.')
    : ($isContact ? 'Scale 0–100%. Clicks divided by impressions in the same historical Search report; approximate. This is not a conversion rate.' : 'Scale 0–100%. The bar shows a lower bound: historical CTR exceeded 11%. It is not compared with video views from another period.') ?></figcaption>
</figure>
<?php endif; ?>
<aside class="pc-note"><h3><?= $caseSafe($caseText['limits']) ?></h3><p><?= $caseSafe($currentCase['note'][$i]) ?></p></aside>
</section>
</div>
</article>
<?php else: ?>
<header class="pc-hero">
<p class="pc-kicker"><?= $isCaseEs ? 'PORTAFOLIO PROFESIONAL' : 'PROFESSIONAL PORTFOLIO' ?></p>
<h1><?= $caseSafe($caseTitle) ?></h1>
<p><?= $caseSafe($caseDescription) ?></p>
<p class="pc-intro"><?= $caseSafe($caseText['intro']) ?></p>
<nav class="pc-group-nav" aria-label="<?= $isCaseEs ? 'Áreas de trabajo' : 'Work areas' ?>">
<?php foreach ($caseText['groups'] as $group => $label): ?><a href="#<?= $caseSafe($group) ?>"><?= $caseSafe($label) ?></a><?php endforeach; ?>
</nav>
</header>
<?php foreach ($caseText['groups'] as $group => $label): ?>
<section id="<?= $caseSafe($group) ?>" class="pc-group" aria-labelledby="heading-<?= $caseSafe($group) ?>">
<h2 id="heading-<?= $caseSafe($group) ?>"><?= $caseSafe($label) ?></h2>
<div class="pc-grid">
<?php foreach ($allPublicCases as $row): if ($row['group'] !== $group) continue; ?>
<article class="pc-card">
<a class="pc-cover" href="<?= $caseSafe($caseBase . '/' . $row['slug']) ?>" aria-label="<?= $caseSafe($row['title'][$i]) ?>"><img src="<?= $caseSafe($caseMedia($row)) ?>" width="960" height="540" loading="lazy" decoding="async" alt=""></a>
<div class="pc-card-body">
<p class="pc-label"><?= $caseSafe($caseText['diagram']) ?></p>
<h3><a href="<?= $caseSafe($caseBase . '/' . $row['slug']) ?>"><?= $caseSafe($row['title'][$i]) ?></a></h3>
<p><?= $caseSafe($row['desc'][$i]) ?></p>
<?php $leadMetric = $row['metrics'][0] ?? []; if ($leadMetric): ?>
<p class="pc-highlight"><strong><?= $caseSafe($leadMetric[0]) ?></strong> <?= $caseSafe($leadMetric[$i + 1]) ?></p>
<?php endif; ?>
<a class="pc-read" href="<?= $caseSafe($caseBase . '/' . $row['slug']) ?>" aria-label="<?= $caseSafe($caseText['read'] . ': ' . $row['title'][$i]) ?>"><?= $caseSafe($caseText['read']) ?> <span aria-hidden="true">↗</span></a>
</div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<aside class="pc-note pc-method"><h2><?= $caseSafe($caseText['limits']) ?></h2><p><?= $caseSafe($caseText['period']) ?></p></aside>
<?php endif; ?>
</main>
<footer class="pc-footer"><div class="pc-wrap">Jovani Treviño · Marketing Studies <a href="/portfolio/#contacto"><?= $caseSafe($caseText['contact']) ?></a></div></footer>
</body>
</html>
