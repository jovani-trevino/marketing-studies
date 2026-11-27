<?php
declare(strict_types=1);
if (!isset($selectedCase) || !is_array($selectedCase)) { http_response_code(404); return; }
$case=$selectedCase; $story=$case['story'];
$deliverables=professional_case_deliverables($case['id']);
$position=array_search($case['id'],array_column($cases,'id'),true);
$nextCase=$cases[($position+1)%count($cases)];
$readingMinutes=max(2,(int)ceil(str_word_count(implode(' ',array_merge(array_values($story),[$case['challenge'],$case['role'],$case['result'],$case['limitation']],$case['actions'])))/190));
?>
<article class="mi-case-detail mi-editorial" data-area="<?= e($case['area']) ?>">
<header class="mi-project-hero"><div class="jt-container">
<nav class="mi-breadcrumb" aria-label="Ruta del caso"><a href="<?= e($homeUrl) ?>">Jovani Treviño</a><span>/</span><a href="<?= e($libraryUrl) ?>">Casos de estudio</a><span>/</span><span><?= e($areas[$case['area']]) ?></span></nav>
<div class="mi-project-grid"><div><span class="jt-kicker">Caso <?= sprintf('%02d',$position+1) ?> / <?= count($cases) ?> · <?= e($case['sector']) ?></span><h1><?= e($case['title']) ?></h1><p class="jt-lead"><?= e($case['summary']) ?></p><div class="mi-project-tags"><span><?= e($case['evidence_kind']) ?></span><span><?= $readingMinutes ?> min de lectura</span></div></div>
<div class="mi-project-art<?= $deliverables ? ' cs-project-original' : '' ?>"><?php if($deliverables): ?><a href="#entregables"><?php professional_case_art($case, true); ?><span class="cs-original-label">Ver el proyecto realizado <span aria-hidden="true">↓</span></span></a><?php else: ?><?php professional_case_art($case, true); ?><div><strong><?= e($case['headline']) ?></strong><span><?= e($case['headline_label']) ?></span></div><small><?= e($areas[$case['area']]) ?></small><?php endif; ?></div></div>
<dl class="mi-facts"><div><dt>Área</dt><dd><?= e($areas[$case['area']]) ?></dd></div><div><dt>Periodo</dt><dd><?= e($case['period']) ?></dd></div><div><dt>Mi participación</dt><dd><?= e($case['role']) ?></dd></div></dl>
</div></header>
<div class="mi-case-nav"><nav class="jt-container" aria-label="Secciones del caso"><?php if($deliverables): ?><a href="#entregables">Ver el trabajo</a><?php endif; ?><a href="#galeria">Galería</a><a href="#contexto">El reto</a><a href="#proceso">El proceso</a><a href="#evidencia"><?= $case['chart'] ? 'Resultados' : 'Entregables' ?></a><a href="#aprendizaje">Aprendizajes</a></nav></div>
<div class="jt-container mi-case-body">
<?php if($deliverables) require __DIR__ . '/case-deliverables.php'; ?>
<?php if(!$deliverables): ?>
<section class="mi-production"><div class="mi-section-label"><span>Producción</span><h2>Del concepto<br> al formato.</h2></div><div class="mi-story-copy">
<p>Este recorrido visual resume los componentes del trabajo. Es una representación conceptual anonimizada, no una captura del proyecto original.</p>
<div class="mi-production-board" data-case-chart><div class="mi-production-screen"><span><?= e($case['discipline']) ?></span><strong><?= e($case['title']) ?></strong><div class="mi-production-frames"><?php foreach($case['steps'] as $i=>$step): ?><div style="--frame:<?= $i ?>"><b><?= sprintf('%02d',$i+1) ?></b><span><?= e($step) ?></span></div><?php endforeach; ?></div></div><button class="cs-replay" type="button" data-chart-replay hidden>Repetir recorrido</button></div>
<h3>Herramientas y tecnologías</h3>
<?php if(in_array($case['area'],['contenido','diseno'],true)): ?>
<p>Mi conjunto de herramientas creativas documentado incluye <strong>Adobe Photoshop CC, Illustrator CC, After Effects CC, Premiere Pro CC y Media Encoder CC</strong>: edición de imágenes, diseño vectorial, animación, edición de video y exportación.</p>
<?php elseif(in_array($case['area'],['web','crm'],true)): ?>
<p>Mi perfil técnico documenta <strong>Visual Studio Code, HTML5, CSS3, JavaScript, WordPress y Bootstrap</strong> para desarrollo e interfaces, junto con Photoshop e Illustrator para preparar recursos visuales.</p>
<?php else: ?><p>El trabajo se apoya en las plataformas y documentos descritos en el proceso y la referencia de este caso. El perfil histórico también documenta Microsoft Office y herramientas de producción de Adobe para preparar materiales y presentaciones.</p><?php endif; ?>
<p class="mi-tool-note">Referencia de herramientas: portafolio profesional histórico, sección de conocimientos y habilidades. Esta lista describe el conjunto documentado del perfil; no confirma que todas las aplicaciones se utilizaran en este proyecto.</p>
<a class="jt-btn jt-btn-primary" href="<?= e(jovani_local_asset_url('assets/downloads/casos/'.$case['id'].'.pdf')) ?>" download>Descargar este caso en PDF</a>
</div></section>
<?php endif; ?>

<?php require __DIR__.'/case-gallery.php'; ?>
<section id="contexto" class="mi-story-section"><div class="mi-section-label"><span>01</span><h2>El contexto.<br> La pregunta.</h2></div><div class="mi-story-copy"><p class="mi-story-intro"><?= e($case['challenge']) ?></p><p><?= e($story['context']) ?></p><div class="mi-objective"><span>Objetivo del trabajo</span><h3><?= e($story['objective']) ?></h3></div></div></section>
<?php $platformIndex=json_decode((string)file_get_contents(__DIR__.'/../blog/content/professional-platforms.json'),true,512,JSON_THROW_ON_ERROR); $casePlatforms=$platformIndex[$case['id']]??[]; if($casePlatforms): ?>
<section class="mi-platform-section" aria-labelledby="platform-title"><span class="jt-kicker">Canales y formatos</span><h2 id="platform-title">Dónde se desarrolló el trabajo.</h2><div class="mi-platform-grid"><?php foreach($casePlatforms as [$logo,$platform,$format,$description]): ?><article class="mi-platform-card"><div class="mi-platform-brand"><img src="<?= e(jovani_local_asset_url('assets/platforms/'.$logo.'.svg')) ?>" width="40" height="40" alt=""><h3><?= e($platform) ?></h3></div><strong><?= e($format) ?></strong><p><?= e($description) ?></p></article><?php endforeach; ?></div><p class="mi-tool-note">Plataformas y formatos identificados en la evidencia del caso. Los logotipos identifican sus servicios y no implican certificación o asociación.</p></section>
<?php endif; ?>
<section id="proceso" class="mi-story-section"><div class="mi-section-label"><span>02</span><h2>Del problema<br> al entregable.</h2></div><div class="mi-story-copy"><p><?= e($case['role']) ?></p><ol class="mi-action-cards"><?php foreach($case['actions'] as $i=>$action): ?><li><span><?= sprintf('%02d',$i+1) ?></span><p><?= e($action) ?></p></li><?php endforeach; ?></ol><h3>Cómo está construido este caso</h3><p><?= e($story['approach']) ?></p><?php if($deliverables): ?><div class="cs-case-actions"><a class="jt-btn jt-btn-primary" href="#entregables">Explorar los entregables ↑</a><a class="jt-btn jt-btn-secondary" href="<?= e(jovani_local_asset_url('assets/downloads/casos/'.$case['id'].'.pdf')) ?>" download>Descargar caso en PDF</a></div><?php endif; ?></div></section>
<section id="evidencia" class="mi-evidence-stage"><div class="mi-stage-heading"><span class="jt-kicker">03 · Evidencia del proyecto</span><h2><?= $case['chart'] ? 'Los resultados, en perspectiva.' : 'Una forma clara de explicar el trabajo.' ?></h2><p><?= e($case['period']) ?> · <?= e($case['evidence_kind']) ?></p></div>
<?php if($case['metrics']): ?><div class="mi-metrics"><?php foreach($case['metrics'] as [$value,$label,$note]): ?><div><strong><?= e($value) ?></strong><span><?= e($label) ?></span><small><?= e($note) ?></small></div><?php endforeach; ?></div><?php endif; ?>
<?php professional_case_chart($case); ?>
<div class="mi-delivery"><h3><?= $case['chart'] ? 'Qué muestra el reporte' : 'El entregable documentado' ?></h3><p><?= e($case['result']) ?></p></div>
<?php $mockup=['experiencia-web'=>'Mockup-Web-Industrial.svg','crm-inmobiliario'=>'Mockup-CRM.svg'][$case['id']]??null; if(!$deliverables && $mockup): ?><figure class="mi-mockup"><img src="<?= e(jovani_local_asset_url('assets/mockups/'.$mockup)) ?>" width="1200" height="760" loading="lazy" alt="Reconstrucción conceptual anónima del enfoque de <?= e($areas[$case['area']]) ?>"><figcaption>Reconstrucción visual anonimizada para explicar el enfoque. No es una captura del sistema original.</figcaption></figure><?php endif; ?>
</section>
<section id="aprendizaje" class="mi-story-section"><div class="mi-section-label"><span>04</span><h2>La lectura<br> profesional.</h2></div><div class="mi-story-copy"><p class="mi-story-intro"><?= e($story['takeaway']) ?></p><div class="mi-source-note"><h3>Alcance de la evidencia</h3><p><?= e($case['limitation']) ?></p><p><strong>Referencia:</strong> <?= e($case['source_label'] ?? $case['source']) ?></p><?php if(!$deliverables): ?><small>Presentación anonimizada de resultados; identidad del cliente reservada. Los materiales originales y activos de marca requieren autorización del titular para su exposición.</small><?php endif; ?></div></div></section>
<section class="mi-project-contact"><span class="jt-kicker">De este caso a tu proyecto</span><h2>Una pregunta clara.<br>Un trabajo que se puede explicar.</h2><a class="jt-btn jt-btn-primary" href="mailto:<?= e($contactEmail) ?>">Conversar con Jovani ↗</a><a class="mi-download-link" href="<?= e(jovani_local_asset_url('assets/downloads/jovani-portafolio.pdf')) ?>">Descargar resumen del portafolio en PDF</a></section>
<section class="mi-related"><span class="jt-kicker">Sigue explorando</span><h2>Más en <?= e($areas[$case['area']]) ?></h2><ul><?php foreach($cases as $related): if($related['area']!==$case['area']||$related['id']===$case['id'])continue; ?><li><a href="<?= e(professional_case_url($related)) ?>"><?= e($related['title']) ?> ↗</a><small><?= e($related['evidence_kind']) ?></small></li><?php endforeach; ?></ul></section>
<a class="mi-next-case" href="<?= e(professional_case_url($nextCase)) ?>"><span>Siguiente caso</span><strong><?= e($nextCase['title']) ?></strong><span aria-hidden="true">→</span></a>
</div></article>
