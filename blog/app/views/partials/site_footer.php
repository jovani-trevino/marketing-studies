<?php $es = $locale === 'es'; ?>
<footer class="ms-footer">
    <div class="container ms-footer-top">
        <div class="ms-footer-brand">
            <a class="ms-brand" href="<?= e($siteHomeUrl) ?>"><i class="bi bi-book" aria-hidden="true"></i><span>Marketing<strong>Studies<span>.</span></strong></span></a>
            <p><?= e($site['site_tagline'] ?? ($es ? 'Investigación, estrategia y marketing con criterio.' : 'Research, strategy and thoughtful marketing.')) ?></p>
            <span><?= $es ? 'Una publicación de Jovani Treviño.' : 'A publication by Jovani Treviño.' ?></span>
        </div>
        <div><h3><?= $es ? 'Explorar' : 'Explore' ?></h3><a href="<?= e($blogIndexUrl) ?>"><?= $es ? 'Todos los artículos' : 'All articles' ?></a><a href="<?= e(url_path('/case-studies', $locale)) ?>"><?= $es ? 'Casos de estudio' : 'Case studies' ?></a><a href="<?= e(url_path('/lab', $locale)) ?>"><?= $es ? 'Recursos y herramientas' : 'Resources & tools' ?></a></div>
        <div><h3><?= $es ? 'Conectar' : 'Connect' ?></h3><a href="<?= e(base_path() . '/portfolio/') ?>"><?= $es ? 'Portafolio y CV' : 'Portfolio & CV' ?></a><a href="<?= e($contactUrl) ?>"><?= $es ? 'Hablemos de tu proyecto' : 'Let’s talk about your project' ?></a><?php if (!empty($site['contact_email'])): ?><a href="mailto:<?= e($site['contact_email']) ?>"><?= e($site['contact_email']) ?></a><?php endif; ?><div class="ms-footer-socials"><?php foreach ($socialLinks as $link): ?><a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($link['label']) ?>"><i class="<?= e($link['icon']) ?>" aria-hidden="true"></i></a><?php endforeach; ?></div></div>
    </div>
    <?php if (trim((string) ($site['ad_footer'] ?? '')) !== ''): ?><div class="container ms-ad-slot"><?= render_ad($site['ad_footer'], 'Sponsor', 'Responsive') ?></div><?php endif; ?>
    <div class="container ms-footer-bottom"><span>&copy; <?= date('Y') ?> <?= e($siteTitle) ?></span><span><?= $es ? 'Hecho con curiosidad y criterio.' : 'Made with curiosity and purpose.' ?></span><div><a href="<?= e(url_path('/privacy', $locale)) ?>"><?= $es ? 'Privacidad' : 'Privacy' ?></a><button type="button" data-cookie-settings><?= $es ? 'Cookies' : 'Cookies' ?></button><a href="<?= e(url_path('/admin')) ?>">CMS</a></div></div>
</footer>
<div class="ms-toast" id="ms-toast" role="status" aria-live="polite" hidden></div>
