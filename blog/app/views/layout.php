<?php
$siteTitle = $site['site_title'] ?? 'Marketing Studies';
$title = (($metaTitle ?? '') && ($metaTitle ?? '') !== 'Home') ? ($metaTitle . ' | ' . $siteTitle) : $siteTitle;
$description = $metaDescription ?? ($site['site_tagline'] ?? '');
$image = media_url($metaImage ?? ($site['site_share_image'] ?? ''));
$imageMeta = $image !== '' ? media_dimensions($image) : [];
$metaType = $metaType ?? (isset($post) ? 'article' : 'website');
$openGraphTitle = $openGraphTitle ?? ($metaTitle ?? $siteTitle);
$openGraphDescription = $openGraphDescription ?? $description;
$twitterTitle = $twitterTitle ?? $openGraphTitle;
$twitterDescription = $twitterDescription ?? $openGraphDescription;
$twitterCard = $twitterCard ?? ($image !== '' ? 'summary_large_image' : 'summary');
$metaImageAlt = trim((string) ($metaImageAlt ?? $openGraphTitle));
$openGraphProfile = is_array($openGraphProfile ?? null) ? $openGraphProfile : [];
$ogLocale = locale_og_code($locale);
$alternateOgLocales = array_values(array_diff(array_map('locale_og_code', SUPPORTED_LOCALES), [$ogLocale]));
$logo = media_url($site['logo_url'] ?? '/static/brand/marketing-studies-logo.png');
$isAdmin = str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', base_path() . '/admin');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (base_path() !== '' && str_starts_with($requestPath, base_path())) {
    $requestPath = substr($requestPath, strlen(base_path())) ?: '/';
}
$innerPath = $requestPath === '/es' ? '/' : preg_replace('#^/es/#', '/', $requestPath);
$innerPath = $innerPath ?: '/';
$enUrl = $alternateEn ?? (base_path() . $innerPath);
$esUrl = $alternateEs ?? (base_path() . '/es' . ($innerPath === '/' ? '' : $innerPath));
$canonicalHref = $canonicalUrl ?? current_url();
$socialLinks = social_links($site);
$siteHomeUrl = url_path('/', $locale);
$blogIndexUrl = url_path('/blog', $locale);
$profilePageUrl = url_path('/jovani', $locale);
$newsletterUrl = $siteHomeUrl . '#newsletter';
$contactUrl = $siteHomeUrl . '#contact';
$headerSearchQuery = trim((string) ($_GET['q'] ?? ''));
?>
<!doctype html>
<html lang="<?= e($locale) ?>" data-bs-theme="light" data-theme-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="robots" content="<?= $isAdmin ? 'noindex,nofollow' : 'index,follow' ?>">
    <meta name="theme-color" content="#f7f4ee">
    <link rel="canonical" href="<?= e(absolute_url($canonicalHref)) ?>">
    <link rel="alternate" hreflang="en" href="<?= e(absolute_url($enUrl)) ?>">
    <link rel="alternate" hreflang="es" href="<?= e(absolute_url($esUrl)) ?>">
    <meta property="og:type" content="<?= e($metaType) ?>">
    <meta property="og:locale" content="<?= e($ogLocale) ?>">
    <?php foreach ($alternateOgLocales as $alternateOgLocale): ?>
        <meta property="og:locale:alternate" content="<?= e($alternateOgLocale) ?>">
    <?php endforeach; ?>
    <meta property="og:title" content="<?= e($openGraphTitle) ?>">
    <meta property="og:description" content="<?= e($openGraphDescription) ?>">
    <meta property="og:url" content="<?= e(absolute_url($canonicalHref)) ?>">
    <meta property="og:site_name" content="<?= e($siteTitle) ?>">
    <?php if ($metaType === 'profile'): ?>
        <?php foreach ($openGraphProfile as $key => $value): ?>
            <?php if (trim((string) $value) !== ''): ?>
                <meta property="profile:<?= e($key) ?>" content="<?= e($value) ?>">
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($image): ?>
        <meta property="og:image" content="<?= e(absolute_url($image)) ?>">
        <meta property="og:image:secure_url" content="<?= e(absolute_url($image)) ?>">
        <meta property="og:image:alt" content="<?= e($metaImageAlt) ?>">
        <?php if (!empty($imageMeta['width']) && !empty($imageMeta['height'])): ?>
            <meta property="og:image:width" content="<?= (int) $imageMeta['width'] ?>">
            <meta property="og:image:height" content="<?= (int) $imageMeta['height'] ?>">
        <?php endif; ?>
        <?php if (!empty($imageMeta['mime'])): ?>
            <meta property="og:image:type" content="<?= e($imageMeta['mime']) ?>">
        <?php endif; ?>
        <meta name="twitter:image" content="<?= e(absolute_url($image)) ?>">
        <meta name="twitter:image:alt" content="<?= e($metaImageAlt) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?= e($twitterCard) ?>">
    <meta name="twitter:title" content="<?= e($twitterTitle) ?>">
    <meta name="twitter:description" content="<?= e($twitterDescription) ?>">
    <script>
        (function () {
            let storedTheme = null; try { storedTheme = localStorage.getItem("gb-theme"); } catch {}
            const fallbackTheme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            const theme = storedTheme === "dark" || storedTheme === "light" ? storedTheme : fallbackTheme;
            document.documentElement.setAttribute("data-bs-theme", theme);
            document.documentElement.setAttribute("data-theme-mode", theme);
        })();
    </script>
    <link rel="stylesheet" href="<?= e(versioned_asset_url('static/vendor/bootstrap/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(versioned_asset_url('static/vendor/bootstrap-icons/bootstrap-icons.min.css')) ?>">
    <link rel="icon" type="image/png" href="<?= e(versioned_asset_url('static/brand/favicon.png')) ?>">
    <link rel="apple-touch-icon" href="<?= e(versioned_asset_url('static/brand/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(versioned_asset_url('static/styles.css')) ?>">
    <link rel="stylesheet" href="<?= e(versioned_asset_url('static/php-site.css')) ?>">
    <link rel="stylesheet" href="<?= e(versioned_asset_url('static/editorial.css')) ?>">
    <?php if ($isAdmin): ?><link rel="stylesheet" href="<?= e(versioned_asset_url('static/admin-editor.css')) ?>"><?php endif; ?>
    <?php if (!empty($extraHead)): ?>
        <?= $extraHead ?>
    <?php endif; ?>
    <?php if (!$isAdmin && !empty($site['adsense_script'])): ?>
        <?= consent_code_payload($site['adsense_script']) ?>
    <?php endif; ?>
</head>
<body class="<?= $isAdmin ? 'admin-body' : 'public-body' ?>">
    <div class="scroll-progress" id="scroll-progress" aria-hidden="true"></div>

    <?php require __DIR__ . '/partials/site_header.php'; ?>

    <main id="main-content">
        <div class="container">
            <?php foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> border-0 shadow-sm rounded-4 mt-3"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
        </div>
        <?= $content ?>
    </main>

    <?php if (!$isAdmin) require __DIR__ . '/partials/site_footer.php'; ?>

    <?php if (!$isAdmin && setting_is_enabled($site['cookie_consent_enabled'] ?? '1')): ?>
        <div class="modal fade" id="cookie-consent-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content cookie-modal-card">
                    <div class="modal-header border-0 pb-0">
                        <h2 class="modal-title h4"><?= e($site['cookie_consent_title'] ?? '') ?></h2>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0"><?= e($site['cookie_consent_message'] ?? '') ?></p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-dark rounded-pill" data-cookie-action="essential"><?= e(t('cookies.essential')) ?></button>
                        <button type="button" class="btn btn-dark rounded-pill" data-cookie-action="accept"><?= e(t('cookies.accept')) ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        window.GB_RUNTIME = {
            cookieConsentEnabled: <?= json_encode(setting_is_enabled($site['cookie_consent_enabled'] ?? '1')) ?>,
            recaptchaEnabled: <?= json_encode(setting_is_enabled($site['recaptcha_enabled'] ?? '0')) ?>,
            recaptchaSiteKey: <?= json_encode($site['recaptcha_site_key'] ?? '') ?>,
            socialLinks: <?= json_encode($socialLinks) ?>,
            themeLabels: { dark: <?= json_encode(t('theme.dark')) ?>, light: <?= json_encode(t('theme.light')) ?> },
            themeSwitchLabel: <?= json_encode(t('theme.switch_label')) ?>
        };
    </script>
    <script src="<?= e(versioned_asset_url('static/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
    <script src="<?= e(versioned_asset_url('static/app-ui.js')) ?>"></script>
    <?php if (!$isAdmin): ?><script src="<?= e(versioned_asset_url('static/editorial.js')) ?>"></script><?php endif; ?>
    <?php if ($isAdmin): ?>
        <script src="<?= e(versioned_asset_url('static/admin-media.js')) ?>"></script>
    <?php endif; ?>
    <?php if (!empty($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
