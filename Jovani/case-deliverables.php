<?php
declare(strict_types=1);
if (!isset($case, $deliverables) || !$deliverables) return;
$items = $deliverables['items'];
?>
<section class="cs-deliverables" id="entregables" aria-labelledby="deliverables-title">
  <div class="cs-deliverables-heading"><div><span class="jt-kicker">El proyecto realizado</span><h2 id="deliverables-title"><?= e($deliverables['title']) ?></h2></div><p><?= e($deliverables['intro']) ?></p></div>
  <div class="cs-deliverables-grid">
    <?php foreach ($items as $i => $item): $assetUrl = jovani_local_asset_url('assets/case-work/' . $item['file']); ?>
    <figure class="cs-work-item<?= $i === 0 ? ' cs-work-featured' : '' ?>">
      <?php if ($item['type'] === 'video'): ?>
      <video controls playsinline preload="none" poster="<?= e(jovani_local_asset_url('assets/case-work/' . $item['poster'])) ?>" width="<?= (int)$item['width'] ?>" height="<?= (int)$item['height'] ?>" aria-label="<?= e($item['title']) ?>" aria-describedby="work-caption-<?= $i ?>">
        <source src="<?= e($assetUrl) ?>" type="video/mp4">
        Tu navegador no puede reproducir este video. <a href="<?= e($assetUrl) ?>">Abrir video</a>.
      </video>
      <?php else: ?>
      <a class="cs-work-image" href="<?= e($assetUrl) ?>" target="_blank" rel="noopener" aria-label="Ampliar: <?= e($item['title']) ?> (abre en otra pestaña)">
        <img src="<?= e($assetUrl) ?>" width="<?= (int)$item['width'] ?>" height="<?= (int)$item['height'] ?>" alt="<?= e($item['alt']) ?>" loading="lazy" decoding="async">
        <span class="cs-work-expand" aria-hidden="true">Ampliar ↗</span>
      </a>
      <?php endif; ?>
      <figcaption id="work-caption-<?= $i ?>"><span class="cs-work-number"><?= sprintf('%02d', $i + 1) ?></span><div><strong><?= e($item['title']) ?></strong><p><?= e($item['caption']) ?></p></div><?php if ($item['type'] === 'video'): ?><a href="<?= e($assetUrl) ?>" target="_blank" rel="noopener">Abrir video ↗</a><?php endif; ?></figcaption>
    </figure>
    <?php endforeach; ?>
  </div>
</section>
