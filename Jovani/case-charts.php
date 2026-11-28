<?php
declare(strict_types=1);

function professional_case_charts(array $case): array
{
    if (!$case['chart']) return [];
    static $config = null;
    $config ??= json_decode((string)file_get_contents(__DIR__.'/../blog/content/professional-charts.json'), true, 512, JSON_THROW_ON_ERROR);
    $definition = $config[$case['id']] ?? ['type'=>'bars','takeaway'=>$case['result'],'note'=>$case['limitation']];
    $primary = array_replace($case['chart'], $definition);
    unset($primary['extra']);
    return isset($definition['extra']) ? [$primary, $definition['extra']] : [$primary];
}

function professional_chart_number(float $value): string
{
    return number_format($value, fmod($value, 1.0) === 0.0 ? 0 : 2, '.', ',');
}

function professional_render_chart(array $chart, array $case): void
{
    $colors = ['#147d78','#b85530','#6550a1','#327bb4'];
    $total = array_sum(array_column($chart['rows'],1));
    $percent = str_contains($chart['unit'], '%');
    ?>
    <figure class="mi-chart cs-data-chart" data-case-chart data-chart-type="<?= e($chart['type']) ?>">
      <figcaption><span class="cs-chart-eyebrow"><?= e(['donut'=>'Distribución de clics','rate'=>'Indicador de respuesta','stat'=>'Volumen registrado','bars'=>'Comparación de resultados'][$chart['type']]) ?></span><strong><?= e($chart['title']) ?></strong><span><?= e($chart['unit']) ?></span></figcaption>
      <div class="cs-chart-tools"><span><?= e($case['period']) ?></span><button class="cs-replay" type="button" data-chart-replay hidden>Repetir animación</button></div>
      <?php if ($chart['type']==='donut'): ?>
      <div class="cs-composition"><div class="cs-donut-wrap"><svg class="cs-donut" viewBox="0 0 220 220" aria-hidden="true"><circle cx="110" cy="110" r="85" fill="none" stroke="#e2e8e8" stroke-width="29"/>
      <?php $offset=0; foreach($chart['rows'] as $i=>[$label,$value]): $share=$total>0 ? $value/$total*100 : 0; ?>
      <circle class="cs-donut-segment" cx="110" cy="110" r="85" fill="none" stroke="<?= $colors[$i%4] ?>" stroke-width="29" pathLength="100" stroke-dasharray="<?= $share ?> <?= 100-$share ?>" stroke-dashoffset="<?= -$offset ?>" transform="rotate(-90 110 110)"/>
      <?php $offset+=$share; endforeach; ?></svg><div class="cs-donut-total"><strong><?= e(professional_chart_number((float)$total)) ?></strong><span>clics totales</span></div></div>
      <ul class="cs-chart-key"><?php foreach($chart['rows'] as $i=>[$label,$value]): ?><li><span class="cs-chart-dot" style="background:<?= $colors[$i%4] ?>" aria-hidden="true"></span><div><strong><?= e($label) ?></strong><span><?= e(professional_chart_number((float)$value)) ?> clics</span></div><b><?= number_format($total>0?$value/$total*100:0,1) ?>%</b></li><?php endforeach; ?></ul></div>
      <?php elseif($chart['type']==='rate'): [$label,$value]=$chart['rows'][0]; ?>
      <div class="cs-rate"><strong><?= e(professional_chart_number((float)$value)) ?><span>%</span></strong><span><?= e($label) ?></span></div>
      <div class="mi-track cs-rate-track" aria-hidden="true"><span class="mi-fill" style="--bar-width:<?= (float)$value ?>%"></span></div><div class="cs-axis"><span>0%</span><span>50%</span><span>100%</span></div>
      <?php elseif($chart['type']==='stat'): ?>
      <div class="cs-stat-pair"><?php foreach($case['metrics'] as [$value,$label,$note]): ?><div><strong><?= e($value) ?></strong><span><?= e($label) ?></span></div><?php endforeach; ?></div>
      <?php else: ?>
      <div class="mi-bars"><?php foreach($chart['rows'] as $i=>[$label,$value]): ?><div class="mi-bar-row"><div class="mi-bar-label"><span><?= e($label) ?></span><strong><?= e(professional_chart_number((float)$value)) ?><?= $percent?'%':'' ?></strong></div><div class="mi-track"><span class="mi-fill" style="--bar-width:<?= min(100,max(0,$value/$chart['max']*100)) ?>%;--chart-color:<?= $colors[$i%4] ?>"></span><?php if(isset($chart['baseline'])): ?><span class="mi-baseline" style="left:<?= $chart['baseline']/$chart['max']*100 ?>%" aria-hidden="true"></span><?php endif; ?></div></div><?php endforeach; ?></div>
      <div class="cs-axis"><span>0</span><span><?= e(professional_chart_number((float)$chart['max'])) ?><?= $percent?'%':'' ?></span></div><?php if(isset($chart['baseline'])): ?><p class="mi-scale">Línea de referencia: periodo anterior = <?= e((string)$chart['baseline']) ?></p><?php endif; ?>
      <?php endif; ?>
      <p class="cs-chart-insight"><?= e($chart['takeaway']) ?></p>
      <details class="cs-chart-table"><summary>Ver cifras y metodología</summary><table><caption><?= e($chart['unit']) ?></caption><thead><tr><th scope="col">Indicador</th><th scope="col">Valor</th></tr></thead><tbody><?php foreach($chart['rows'] as [$label,$value]): ?><tr><th scope="row"><?= e($label) ?></th><td><?= e(professional_chart_number((float)$value)) ?><?= $percent?'%':'' ?></td></tr><?php endforeach; ?></tbody></table><p><?= e($chart['note']) ?></p></details>
      <p class="cs-chart-source">Referencia: <?= e($case['source_label']??$case['source']) ?></p>
    </figure>
    <?php
}
