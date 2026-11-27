<?php
declare(strict_types=1);
if (!isset($case)) { http_response_code(404); return; }
$galleryIndex=json_decode((string)file_get_contents(__DIR__.'/../blog/content/professional-media.json'),true,512,JSON_THROW_ON_ERROR);
$galleryFile=$galleryIndex[$case['id']]??null;
if (!$galleryFile) return;
$galleryImages=[
 ['assets/case-premium/'.$galleryFile,'Presentación conceptual','Visualización del área de trabajo. Recurso ilustrativo compartido entre proyectos afines; no es un entregable original ni acredita resultados.',1536,1024],
 ['assets/case-illustrations/'.$case['id'].'.svg','Componentes del caso','Lámina explicativa de este caso. Los ejemplos de interfaz son ilustrativos; las cifras, cuando aparecen, corresponden al resumen documentado.',1200,800]
];
?>
<section class="mi-showcase mi-full-gallery" id="galeria" aria-labelledby="gallery-title">
<div class="mi-gallery-heading"><div><span class="jt-kicker">Muestrario visual</span><h2 id="gallery-title">Explora el enfoque del proyecto.</h2></div><p>Dos perspectivas para entender el trabajo: su presentación visual y los componentes que lo explican.</p></div>
<div class="mi-gallery-grid">
<?php foreach($galleryImages as $i=>[$file,$title,$caption,$width,$height]): ?>
<figure><a href="<?= e(jovani_local_asset_url($file)) ?>" target="_blank" rel="noopener" aria-label="Ampliar <?= e($title) ?> (abre en otra pestaña)"><img src="<?= e(jovani_local_asset_url($file)) ?>" width="<?= $width ?>" height="<?= $height ?>" loading="lazy" decoding="async" alt="<?= e($title.': '.$case['title']) ?>"><span class="mi-gallery-open">Ampliar imagen ↗</span></a><figcaption><span class="mi-gallery-number">0<?= $i+1 ?></span><div><strong><?= e($title) ?></strong><p><?= e($caption) ?></p></div></figcaption></figure>
<?php endforeach; ?>
</div></section>
