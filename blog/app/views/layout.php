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
    <meta name="robots" content="index,follow">
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
            const storedTheme = localStorage.getItem("gb-theme");
            const fallbackTheme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            const theme = storedTheme === "dark" || storedTheme === "light" ? storedTheme : fallbackTheme;
            document.documentElement.setAttribute("data-bs-theme", theme);
            document.documentElement.setAttribute("data-theme-mode", theme);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= e(versioned_asset_url('static/brand/favicon.png')) ?>">
    <link rel="apple-touch-icon" href="<?= e(versioned_asset_url('static/brand/favicon.png')) ?>">
    <link rel="stylesheet" href="<?= e(versioned_asset_url('static/styles.css')) ?>">
    <link rel="stylesheet" href="<?= e(versioned_asset_url('static/php-site.css')) ?>">
    <?php if (!empty($extraHead)): ?>
        <?= $extraHead ?>
    <?php endif; ?>
    <?php if (!empty($site['adsense_script'])): ?>
        <?= $site['adsense_script'] ?>
    <?php endif; ?>
</head>
<body class="<?= $isAdmin ? 'admin-body' : 'public-body' ?>">
    <div class="scroll-progress" id="scroll-progress" aria-hidden="true"></div>

    <header class="site-header">
        <nav class="navbar navbar-expand-xl">
            <div class="container py-3">
                <a class="navbar-brand brand-official has-official-logo" href="<?= e($siteHomeUrl) ?>">
                    <span class="brand-logo-shell">
                        <img src="<?= e($logo) ?>" alt="<?= e($siteTitle) ?> logo" class="brand-logo">
                    </span>
                    <span class="visually-hidden"><?= e($siteTitle) ?></span>
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="<?= e(t('nav.open_menu')) ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <?php if (!$isAdmin): ?>
                    <form class="header-search-form header-search-mobile d-xl-none"
                          method="get"
                          action="<?= e($blogIndexUrl) ?>"
                          role="search"
                          aria-label="<?= e(t('nav.search_label')) ?>">
                        <label class="visually-hidden" for="header-search-mobile"><?= e(t('nav.search_label')) ?></label>
                        <span class="header-search-icon" aria-hidden="true"><i class="bi bi-search"></i></span>
                        <input id="header-search-mobile"
                               type="search"
                               name="q"
                               value="<?= e($headerSearchQuery) ?>"
                               placeholder="<?= e(t('nav.search_placeholder')) ?>"
                               autocomplete="off"
                               inputmode="search">
                        <button class="header-search-button" type="submit" aria-label="<?= e(t('nav.search_button')) ?>">
                            <span><?= e(t('nav.search_button')) ?></span>
                            <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </button>
                    </form>
                <?php endif; ?>
                <div class="collapse navbar-collapse" id="main-nav">
                    <ul class="navbar-nav ms-auto align-items-xl-center gap-xl-3">
                        <?php foreach ($navCategories as $category): ?>
                            <?php if (!empty($category['children'])): ?>
                                <?php $categoryChildren = flatten_category_tree($category['children'], 1); ?>
                                <li class="nav-item dropdown nav-item-category-group">
                                    <a class="nav-link nav-link-category-featured nav-link-category-dropdown" href="#" id="nav-category-<?= (int) $category['id'] ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span><?= e($category['name']) ?></span>
                                        <i class="bi bi-chevron-down nav-dropdown-chevron" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu nav-category-menu" aria-labelledby="nav-category-<?= (int) $category['id'] ?>">
                                        <li><a class="dropdown-item nav-category-overview" href="<?= e(url_path('/' . $category['slug'], $locale)) ?>"><?= e(($locale === 'es' ? 'Ver todo ' : 'View all ') . $category['name']) ?></a></li>
                                        <?php foreach ($categoryChildren as $child): ?>
                                            <li><a class="dropdown-item" href="<?= e(url_path('/' . $child['slug'], $locale)) ?>"><?= e($child['tree_label'] ?? $child['name']) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-category-featured" href="<?= e(url_path('/' . $category['slug'], $locale)) ?>"><?= e($category['name']) ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <li class="nav-item">
                            <div class="language-switcher">
                                <a href="<?= e($enUrl) ?>" class="<?= $locale === 'en' ? 'active' : '' ?>">EN</a>
                                <a href="<?= e($esUrl) ?>" class="<?= $locale === 'es' ? 'active' : '' ?>">ES</a>
                            </div>
                        </li>
                        <?php if ($isAdmin): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= e(url_path('/admin')) ?>">CMS</a></li>
                            <li class="nav-item">
                                <form method="post" action="<?= e(url_path('/admin/logout')) ?>">
                                    <?= csrf_input() ?>
                                    <button class="btn btn-outline-dark rounded-pill px-3" type="submit">Salir</button>
                                </form>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <button type="button" class="theme-toggle theme-toggle-floating" id="theme-toggle" aria-label="Theme" aria-pressed="false">
        <i class="bi bi-moon-stars-fill theme-toggle-icon" aria-hidden="true"></i>
        <span class="theme-toggle-label"><?= e(t('theme.dark')) ?></span>
    </button>

    <main>
        <div class="container">
            <?php foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> border-0 shadow-sm rounded-4 mt-3"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
        </div>
        <?= $content ?>
    </main>

    <footer class="site-footer footer-studio">
        <div class="footer-studio-shell">
            <div class="footer-studio-brand-row">
                <a href="<?= e($siteHomeUrl) ?>" class="footer-studio-brand-link">
                    <img src="<?= e($logo) ?>" alt="<?= e($siteTitle) ?> logo" class="footer-studio-logo">
                </a>
                <p><?= e($site['site_tagline'] ?? '') ?></p>
            </div>
            <div class="footer-studio-utility">
                <nav class="footer-studio-links" aria-label="Footer">
                    <a href="<?= e(url_path('/case-studies', $locale)) ?>"><?= e($locale === 'es' ? 'Casos de estudio' : 'Case studies') ?></a>
                    <a href="<?= e(url_path('/lab', $locale)) ?>">Lab</a>
                    <a href="<?= e($newsletterUrl) ?>">Newsletter</a>
                    <a href="<?= e($contactUrl) ?>"><?= e($locale === 'es' ? 'Contacto' : 'Contact') ?></a>
                </nav>
                <div class="footer-studio-socials">
                    <?php if ($socialLinks): ?>
                        <?php foreach ($socialLinks as $link): ?>
                            <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($link['label']) ?>"><i class="<?= e($link['icon']) ?>"></i></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span><?= e($locale === 'es' ? 'Agrega tus redes desde el CMS.' : 'Add your social links from the CMS.') ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-studio-ad">
                <?= render_ad($site['ad_footer'] ?? '', 'Footer Sponsor', 'Responsive') ?>
            </div>
            <div class="footer-studio-bottom">
                <span>&copy; <?= date('Y') ?> <?= e($siteTitle) ?>. <?= e(t('footer.rights')) ?></span>
                <span class="footer-studio-bottom-links">
                    <a href="<?= e(url_path('/admin/login')) ?>">Login</a>
                    <a href="<?= e($siteHomeUrl) ?>"><?= e($siteTitle) ?></a>
                </span>
            </div>
        </div>
    </footer>

    <?php if (setting_is_enabled($site['cookie_consent_enabled'] ?? '1')): ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(versioned_asset_url('static/app-ui.js')) ?>"></script>
    <?php if ($isAdmin): ?>
        <script src="<?= e(versioned_asset_url('static/admin-media.js')) ?>"></script>
    <?php endif; ?>
    <?php if (!empty($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
