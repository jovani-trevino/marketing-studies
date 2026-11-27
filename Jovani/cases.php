<?php
declare(strict_types=1);

function professional_cases(): array
{
    static $cases = null;
    return $cases ??= json_decode((string) file_get_contents(__DIR__ . '/../blog/content/professional-cases.json'), true, 512, JSON_THROW_ON_ERROR);
}

function professional_areas(): array
{
    return json_decode((string) file_get_contents(__DIR__ . '/../blog/content/professional-areas.json'), true, 512, JSON_THROW_ON_ERROR);
}

function professional_case_url(array $case): string
{
    return jovani_app_url('/casos/' . $case['id']);
}

function professional_case_deliverables(string $id): ?array
{
    static $index = null;
    $index ??= json_decode((string) file_get_contents(__DIR__ . '/../blog/content/professional-deliverables.json'), true, 512, JSON_THROW_ON_ERROR);
    return $index[$id] ?? null;
}

function professional_case_art(array $case, bool $priority = false): void
{
    $work = professional_case_deliverables($case['id']);
    if ($work) {
        echo '<img class="cs-cover cs-cover-original" src="' . e(jovani_local_asset_url('assets/case-work/' . $work['cover'])) . '" alt="' . e($work['cover_alt']) . '" width="' . (int)$work['cover_width'] . '" height="' . (int)$work['cover_height'] . '" loading="' . ($priority ? 'eager' : 'lazy') . '" decoding="async">';
        return;
    }
    $media=json_decode((string)file_get_contents(__DIR__.'/../blog/content/professional-media.json'),true,512,JSON_THROW_ON_ERROR);
    $asset=isset($media[$case['id']]) ? 'assets/case-premium/'.$media[$case['id']] : 'assets/case-illustrations/'.$case['id'].'.svg';
    echo '<img class="cs-cover cs-cover-specific" src="' . e(jovani_local_asset_url($asset)) . '" alt="Representación conceptual: ' . e($case['title']) . '" width="1200" height="800" loading="' . ($priority ? 'eager' : 'lazy') . '" decoding="async">';
}

function professional_case_chart(array $case): void
{
    $chart = $case['chart'];
    ?>
    <figure class="mi-chart" data-case-chart>
        <figcaption><strong><?= e($chart ? $chart['title'] : 'Cómo se conectan los entregables') ?></strong><span><?= e($chart ? $chart['unit'] : 'Diagrama del proceso · sin escala cuantitativa') ?></span></figcaption>
        <div class="cs-chart-tools"><span><?= $chart ? 'Lectura de resultados' : 'Recorrido del proyecto' ?></span><button class="cs-replay" type="button" data-chart-replay hidden>Repetir animación</button></div>
        <?php if ($chart): ?>
            <div class="mi-bars">
                <?php foreach ($chart['rows'] as [$label, $value]): ?>
                <div class="mi-bar-row">
                    <div class="mi-bar-label"><span><?= e($label) ?></span><strong><?= e(number_format((float) $value, fmod((float) $value, 1.0) === 0.0 ? 0 : 2, '.', ',')) ?></strong></div>
                    <div class="mi-track"><span class="mi-fill" style="--bar-width:<?= min(100, max(0, (float) $value / (float) $chart['max'] * 100)) ?>%"></span><?php if (isset($chart['baseline'])): ?><span class="mi-baseline" style="left:<?= (float) $chart['baseline'] / (float) $chart['max'] * 100 ?>%" aria-hidden="true"></span><?php endif; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="mi-scale">Escala: 0–<?= e((string) $chart['max']) ?><?= isset($chart['baseline']) ? ' · línea de referencia: 100' : '' ?></p>
            <details class="cs-chart-table"><summary>Consultar los datos</summary><table><caption><?= e($chart['unit']) ?></caption><thead><tr><th scope="col">Indicador</th><th scope="col">Valor</th></tr></thead><tbody><?php foreach ($chart['rows'] as [$label, $value]): ?><tr><th scope="row"><?= e($label) ?></th><td><?= e(number_format((float) $value, fmod((float) $value, 1.0) === 0.0 ? 0 : 2, '.', ',')) ?></td></tr><?php endforeach; ?></tbody></table></details>
        <?php else: ?>
            <ol class="mi-process"><?php foreach ($case['steps'] as $step): ?><li><?= e($step) ?></li><?php endforeach; ?></ol>
        <?php endif; ?>
    </figure>
    <?php
}
