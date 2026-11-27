<?php declare(strict_types=1); ?>
<section class="cs-library-hero"><div class="jt-container">
<div class="cs-library-heading"><div><span class="jt-kicker">Portafolio seleccionado</span><h1>Ideas en acción.<br><em>Resultados con contexto.</em></h1></div><div class="cs-library-description"><p>Una mirada al trabajo detrás de cada campaña, cada experiencia y cada decisión.</p><span><?= count($cases) ?> casos de estudio <i></i> <?= count($areas) ?> disciplinas</span></div></div>
<div class="cs-discipline-strip" aria-hidden="true"><span>Estrategia</span><span>Creatividad</span><span>Datos</span><span>Tecnología</span></div>
</div></section>
<section class="jt-section mi-selected cs-library" id="portafolio"><div class="jt-container">
<?php require __DIR__ . '/case-cards.php'; ?>
<p class="mi-confidentiality">Piezas originales, mockups del archivo profesional y resultados documentados. Cada caso identifica sus entregables y el alcance de la evidencia.</p>
</div></section>
<section class="cs-library-contact jt-container"><span class="jt-kicker">El siguiente proyecto</span><h2>¿Qué podemos<br>construir juntos?</h2><a class="jt-btn jt-btn-primary" href="mailto:<?= e($contactEmail) ?>">Hablemos de tu proyecto</a></section>
