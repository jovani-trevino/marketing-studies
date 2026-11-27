<?php

require_once __DIR__ . "/admin-workspace.php";

function dispatch(): void
{
    $rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($rawPath, $base)) {
        $rawPath = substr($rawPath, strlen($base)) ?: '/';
    }
    $path = '/' . trim($rawPath, '/');
    $path = $path === '/' ? '/' : rtrim($path, '/');

    $locale = DEFAULT_LOCALE;
    if ($path === '/es' || str_starts_with($path, '/es/')) {
        $locale = 'es';
        $path = substr($path, 3) ?: '/';
        $path = '/' . trim($path, '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');
    }

    if (str_starts_with($path, '/blog/admin/')) {
        $path = substr($path, 5) ?: '/';
    }

    $GLOBALS['locale'] = $locale;
    $GLOBALS['site'] = settings();
    $GLOBALS['navCategories'] = nav_categories();
    $GLOBALS['allCategories'] = public_category_options(false);

    if (str_starts_with($path, '/admin')) {
        admin_dispatch($path);
        return;
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'POST') {
        public_post_dispatch($path);
        return;
    }

    if (!in_array($method, ['GET','HEAD'], true)) { http_response_code(405); header('Allow: GET, HEAD, POST'); return; }
    public_get_dispatch($path);
}

function public_get_dispatch(string $path): void
{
    if ($path === '/') {
        home_page();
        return;
    }
    if ($path === '/jovani' || $path === '/about') {
        profile_page();
        return;
    }
    if ($path === '/blog') {
        blog_page();
        return;
    }
    if ($path === '/api/posts') {
        posts_api();
        return;
    }
    if ($path === '/lab') {
        lab_page();
        return;
    }
    if ($path === '/privacy' || $path === '/privacidad') {
        view('privacy', ['metaTitle'=>$GLOBALS['locale'] === 'es' ? 'Privacidad' : 'Privacy']);
        return;
    }
    if (preg_match('#^/newsletter/unsubscribe/([a-f0-9]{32,96})$#', $path, $m)) {
        newsletter_unsubscribe_page($m[1]);
        return;
    }
    if (preg_match('#^/lab/download/(\d+)$#', $path, $m)) {
        lab_download((int) $m[1]);
        return;
    }
    if ($path === '/case-studies' || $path === '/casos-de-estudio') {
        category_page('casos-de-estudio');
        return;
    }
    if (preg_match('#^/post/([^/]+)$#', $path, $m)) {
        $post = find_post($m[1], $GLOBALS['locale']);
        if ($post) {
            redirect_to_if_changed(post_url($post, $GLOBALS['locale']), 301);
        }
        post_page($m[1]);
        return;
    }
    if (preg_match('#^/(author|autor)/([^/]+)$#', $path, $m)) {
        author_page($m[2]);
        return;
    }
    if (preg_match('#^/([^/]+)/([^/]+)$#', $path, $m)) {
        post_page($m[2], $m[1]);
        return;
    }
    if ($path === '/sitemap.xml') {
        sitemap_xml();
        return;
    }
    if ($path === '/robots.txt') {
        robots_txt();
        return;
    }

    $slug = trim($path, '/');
    if ($slug !== '' && find_category($slug)) {
        category_page($slug);
        return;
    }

    not_found_page();
}

function public_post_dispatch(string $path): void
{
    if ($path === '/newsletter') {
        subscribe_newsletter();
        return;
    }
    if ($path === '/contact') {
        submit_contact();
        return;
    }
    if (preg_match('#^/post/([^/]+)/comment$#', $path, $m)) {
        create_comment($m[1]);
        return;
    }
    if (preg_match('#^/([^/]+)/([^/]+)/comment$#', $path, $m)) {
        create_comment($m[2], $m[1]);
        return;
    }
    not_found_page();
}

function home_page_payload(): array
{
    $editorialExcludedCategories = ['casos-de-estudio'];
    $featured = featured_post();
    $featuredPosts = featured_posts(4, $editorialExcludedCategories);
    $latest = published_posts(12, 0, 0, '', '', $editorialExcludedCategories);
    $feedExcludeIds = [];
    $feedPosts = published_posts(6, 0, 0, '', '', $editorialExcludedCategories, true);
    $feedHasRemaining = count_published_posts('', '', $editorialExcludedCategories, true) > count($feedPosts);
    $caseStudies = published_posts(3, 0, 0, '', 'casos-de-estudio');
    return [
        'featured' => $featured,
        'latest' => $latest,
        'featuredPosts' => $featuredPosts,
        'feedPosts' => $feedPosts,
        'feedExcludeIds' => $feedExcludeIds,
        'feedHasRemaining' => $feedHasRemaining,
        'caseStudies' => $caseStudies,
        'totalPosts' => count_published_posts('', '', $editorialExcludedCategories),
        'totalCaseStudies' => count_published_posts('', 'casos-de-estudio'),
        'totalLabResources' => count(lab_resources()),
    ];
}

function home_page(): void
{
    view('blog_home', home_page_payload());
}

function profile_page(): void
{
    $locale = $GLOBALS['locale'] ?? DEFAULT_LOCALE;
    $authorRecord = author_by_slug('jovani') ?: author_by_slug('jovani-trevino') ?: [];
    $authorName = trim((string) ($authorRecord['name'] ?? '')) ?: 'Jovani Treviño';
    $filter = $authorRecord
        ? author_post_filter(array_merge($authorRecord, ['name' => $authorName]))
        : ['where' => 'posts.author_name = ?', 'params' => [$authorName]];
    $posts = array_map('localize_post', db_all('SELECT ' . post_select() . " FROM posts LEFT JOIN categories ON categories.id = posts.category_id WHERE posts.status = 'published' AND {$filter['where']} ORDER BY posts.published_at DESC, posts.id DESC LIMIT 3", $filter['params']));
    $totalPosts = (int) (db_one("SELECT COUNT(*) AS total FROM posts WHERE posts.status = 'published' AND {$filter['where']}", $filter['params'])['total'] ?? 0);
    $authorRecord = $authorRecord ? localize_author($authorRecord, $locale) : [];

    $avatar = safe_content_url((string) ($authorRecord['avatar_url'] ?? ''));
    $legacyPortrait = '/static/brand/jovani-trevino.jpg';
    $isLegacyPortrait = in_array('/' . ltrim($avatar, '/'), [$legacyPortrait, '/blog' . $legacyPortrait, base_path() . $legacyPortrait], true);
    $profilePortrait = $avatar !== '' && !$isLegacyPortrait
        ? media_url($avatar)
        : versioned_asset_url('static/brand/jovani-profile.webp');
    $headline = trim((string) ($authorRecord['headline'] ?? '')) ?: trim((string) ($authorRecord['role'] ?? ''));
    $bio = trim((string) ($authorRecord['bio'] ?? ''));
    $description = meta_text(implode(' ', array_filter([$headline, $bio])));
    if ($description === '') {
        $description = $locale === 'es'
            ? $authorName . ': marketing digital, SEO, analítica y estrategia. Conoce su trabajo y sus publicaciones en Marketing Studies.'
            : $authorName . ': digital marketing, SEO, analytics and strategy. Explore his work and writing on Marketing Studies.';
    }
    $nameParts = preg_split('/\s+/', $authorName) ?: [$authorName];

    view('home', [
        'metaTitle' => $authorName,
        'metaDescription' => $description,
        'metaImage' => $profilePortrait,
        'metaImageAlt' => $locale === 'es' ? 'Retrato de ' . $authorName : 'Portrait of ' . $authorName,
        'metaType' => 'profile',
        'canonicalUrl' => absolute_url(url_path('/jovani', $locale)),
        'alternateEn' => absolute_url(url_path('/jovani', 'en')),
        'alternateEs' => absolute_url(url_path('/jovani', 'es')),
        'openGraphProfile' => [
            'first_name' => $nameParts[0],
            'last_name' => implode(' ', array_slice($nameParts, 1)),
            'username' => $authorRecord['slug'] ?? 'jovani',
        ],
        'authorRecord' => $authorRecord,
        'authorName' => $authorName,
        'profilePortrait' => $profilePortrait,
        'posts' => $posts,
        'totalPosts' => $totalPosts,
    ]);
}

function blog_page(): void
{
    $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 200);
    $rawCategory = trim((string) ($_GET['category'] ?? ''));
    $category = $rawCategory !== '' ? canonical_category_slug($rawCategory) : '';
    $limit = 8;
    $total = count_published_posts($search, $category);
    $totalPages = max(1, (int) ceil($total / $limit));
    $page = min($totalPages, max(1, (int) ($_GET['page'] ?? 1)));
    $offset = ($page - 1) * $limit;
    $posts = published_posts($limit, $offset, 0, $search, $category);
    $canonicalParams = array_filter(['category' => $category, 'page' => $page > 1 ? $page : null], static fn($value) => $value !== '' && $value !== null);
    $canonicalQuery = $canonicalParams ? '?' . http_build_query($canonicalParams) : '';
    view('blog', [
        'metaTitle' => t('blog.title'),
        'metaDescription' => t('blog.desc'),
        'canonicalUrl' => absolute_url(url_path('/blog', $GLOBALS['locale']) . $canonicalQuery),
        'alternateEn' => url_path('/blog', 'en') . $canonicalQuery,
        'alternateEs' => url_path('/blog', 'es') . $canonicalQuery,
        'posts' => $posts,
        'total' => $total,
        'search' => $search,
        'selectedCategory' => $category,
        'page' => $page,
        'totalPages' => $totalPages,
    ]);
}

function category_page(string $slug): void
{
    $category = find_category($slug);
    if (!$category) {
        not_found_page();
        return;
    }
    if (($category['slug'] ?? '') === 'lab') {
        lab_page();
        return;
    }
    if ($slug !== ($category['slug'] ?? '')) {
        redirect_to_if_changed(url_path('/' . ($category['slug'] ?? ''), $GLOBALS['locale']), 301);
    }
    $categorySlugs = !empty($category['id']) ? category_descendant_slugs((int) $category['id']) : [(string) ($category['slug'] ?? '')];
    $category['total_posts'] = count_published_posts('', $categorySlugs);
    $posts = published_posts(12, 0, 0, '', $categorySlugs);
    view('category', [
        'metaTitle' => $category['name'],
        'metaDescription' => $category['description'],
        'alternateEn' => url_path('/' . localize_category($category, 'en')['slug'], 'en'),
        'alternateEs' => url_path('/' . localize_category($category, 'es')['slug'], 'es'),
        'category' => $category,
        'leadPost' => $posts[0] ?? null,
        'posts' => array_slice($posts, 1),
    ]);
}

function post_page(string $slug, string $categorySlug = ''): void
{
    $post = find_post($slug, $GLOBALS['locale']);
    if (!$post) {
        not_found_page();
        return;
    }
    $actual = (string) (($post['category_canonical_slug'] ?? '') ?: canonical_category_slug((string) (($post['category_slug'] ?? '') ?: 'marketing')));
    if ($categorySlug !== '') {
        $requested = canonical_category_slug($categorySlug);
        if ($requested !== $actual) {
            redirect_to_if_changed(post_url($post, $GLOBALS['locale']), 301);
        }
    }
    $expectedCategorySlug = localized_category_slug($actual, $GLOBALS['locale']);
    if ($categorySlug !== '' && ($categorySlug !== $expectedCategorySlug || $slug !== ($post['slug'] ?? ''))) {
        redirect_to_if_changed(post_url($post, $GLOBALS['locale']), 301);
    }
    $summary = comment_summary((int) $post['id']);
    $canonicalPostUrl = absolute_url(post_url($post, $GLOBALS['locale']));
    view('post', [
        'metaTitle' => $post['seo_title'] ?: $post['title'],
        'metaDescription' => seo_description($post),
        'metaImage' => $post['cover_image'] ?: $GLOBALS['site']['site_share_image'],
        'canonicalUrl' => $canonicalPostUrl,
        'alternateEn' => absolute_url(post_url($post, 'en')),
        'alternateEs' => absolute_url(post_url($post, 'es')),
        'post' => $post,
        'comments' => comments_for_post((int) $post['id']),
        'summary' => $summary,
        'related' => related_posts($post),
        'shareLinks' => share_links($post['title'], $canonicalPostUrl),
    ]);
}

function author_page(string $slug): void
{
    $authorRecord = author_by_slug($slug);
    $requestedSlug = $slug;
    if ($authorRecord) {
        $filter = author_post_filter($authorRecord);
        $posts = array_map('localize_post', db_all('SELECT ' . post_select() . " FROM posts LEFT JOIN categories ON categories.id = posts.category_id WHERE posts.status = 'published' AND {$filter['where']} ORDER BY posts.published_at DESC", $filter['params']));
        $stats = db_one("SELECT COUNT(*) AS total_posts, COALESCE(SUM(read_time), 0) AS total_read_time, COUNT(DISTINCT category_id) AS total_categories, COALESCE(SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END), 0) AS featured_posts FROM posts WHERE status = 'published' AND {$filter['where']}", $filter['params']) ?: [];
        $author = $authorRecord['name'];
        $authorRecord = localize_author($authorRecord);
    } else {
        $rows = db_all("SELECT DISTINCT author_name FROM posts WHERE status = 'published'");
        $author = null;
        foreach ($rows as $row) {
            if (slugify($row['author_name']) === $slug || ($slug === 'jovani' && str_contains(slugify($row['author_name']), 'jovani'))) {
                $author = $row['author_name'];
                break;
            }
        }
        $author = $author ?: ($GLOBALS['site']['author_name'] ?? 'Jovani');
        $posts = array_map('localize_post', db_all('SELECT ' . post_select() . " FROM posts LEFT JOIN categories ON categories.id = posts.category_id WHERE posts.status = 'published' AND posts.author_name = ? ORDER BY posts.published_at DESC", [$author]));
        $stats = db_one("SELECT COUNT(*) AS total_posts, COALESCE(SUM(read_time), 0) AS total_read_time, COUNT(DISTINCT category_id) AS total_categories, COALESCE(SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END), 0) AS featured_posts FROM posts WHERE status = 'published' AND author_name = ?", [$author]) ?: [];
        $authorRecord = null;
    }
    $authorSlug = trim((string) ($authorRecord['slug'] ?? $requestedSlug)) ?: slugify($author);
    $authorHeadline = trim((string) ($authorRecord['headline'] ?? ''));
    $authorRole = trim((string) ($authorRecord['role'] ?? ''));
    $authorBio = trim((string) ($authorRecord['bio'] ?? ''));
    $authorAreas = parse_tag_list((string) ($authorRecord['expertise_areas'] ?? ''));
    $authorAvatar = author_avatar_url($author, $authorRecord['avatar_url'] ?? null);
    $totalPosts = (int) ($stats['total_posts'] ?? count($posts));
    $shareDescriptionParts = [];
    if ($authorHeadline !== '') {
        $shareDescriptionParts[] = $authorHeadline;
    } elseif ($authorRole !== '') {
        $shareDescriptionParts[] = $authorRole;
    }
    if ($authorBio !== '') {
        $shareDescriptionParts[] = meta_text($authorBio, 120);
    }
    if ($authorAreas) {
        $shareDescriptionParts[] = implode(' · ', array_slice($authorAreas, 0, 3));
    }
    if ($totalPosts > 0) {
        $shareDescriptionParts[] = $GLOBALS['locale'] === 'es'
            ? $totalPosts . ' publicaciones en Marketing Studies.'
            : $totalPosts . ' published pieces on Marketing Studies.';
    }
    $shareDescription = meta_text(implode(' ', array_filter($shareDescriptionParts)));
    if ($shareDescription === '') {
        $shareDescription = meta_text($authorBio !== '' ? $authorBio : t('author.about_desc'));
    }
    $shareTitle = meta_text($author . (($authorHeadline !== '' ? ' | ' . $authorHeadline : ($authorRole !== '' ? ' | ' . $authorRole : ''))), 110);
    $nameParts = preg_split('/\s+/', trim($author)) ?: [];
    $profileMeta = [];
    if ($nameParts) {
        $profileMeta['first_name'] = $nameParts[0];
        if (count($nameParts) > 1) {
            $profileMeta['last_name'] = implode(' ', array_slice($nameParts, 1));
        }
    }
    $profileMeta['username'] = $authorSlug;
    $canonicalAuthorUrl = absolute_url(url_path('/author/' . $authorSlug, $GLOBALS['locale']));
    view('author', [
        'metaTitle' => $author,
        'metaDescription' => $shareDescription,
        'metaImage' => $authorAvatar,
        'metaImageAlt' => $GLOBALS['locale'] === 'es'
            ? 'Foto de perfil de ' . $author
            : 'Profile photo of ' . $author,
        'metaType' => 'profile',
        'canonicalUrl' => $canonicalAuthorUrl,
        'alternateEn' => absolute_url(url_path('/author/' . $authorSlug, 'en')),
        'alternateEs' => absolute_url(url_path('/author/' . $authorSlug, 'es')),
        'openGraphTitle' => $shareTitle,
        'openGraphDescription' => $shareDescription,
        'twitterTitle' => $shareTitle,
        'twitterDescription' => $shareDescription,
        'openGraphProfile' => $profileMeta,
        'author' => $author,
        'authorRecord' => $authorRecord,
        'posts' => $posts,
        'stats' => $stats,
    ]);
}

function lab_page(): void
{
    $resources = lab_resources();
    view('lab', [
        'metaTitle' => 'Lab',
        'metaDescription' => t('lab.desc'),
        'resources' => $resources,
        'featuredResource' => $resources[0] ?? null,
    ]);
}

function lab_download(int $resourceId): void
{
    $resource = db_one("SELECT * FROM lab_resources WHERE id = ? AND status = 'published'", [$resourceId]);
    if (!$resource) {
        not_found_page();
        return;
    }
    $url = safe_content_url((string) $resource['download_url']);
    if ($url === '') { not_found_page(); return; }
    if (preg_match('#^https?://#i', $url)) {
        // An external redirect is not evidence that a file was downloaded.
        redirect_to($url);
    }
    $path = rawurldecode((string) parse_url(media_url($url), PHP_URL_PATH));
    if (base_path() !== '' && str_starts_with($path, base_path() . '/')) $path = substr($path, strlen(base_path()));
    $isUpload = str_starts_with($path, '/uploads/');
    $root = realpath($isUpload ? (string) app_config('uploads_path') : dirname(__DIR__) . '/public');
    if ($root === false) { not_found_page(); return; }
    $relative = $isUpload ? substr($path, 9) : ltrim($path, '/');
    $file = str_contains($relative, "\0") ? false : realpath($root . '/' . $relative);
    $extension = strtolower(pathinfo((string) $file, PATHINFO_EXTENSION));
    if (!$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file) || !is_readable($file)
        || !in_array($extension, ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','md','csv','zip','jpg','jpeg','png','webp','gif'], true)) {
        not_found_page(); return;
    }
    $handle = fopen($file, 'rb');
    if (!$handle) { not_found_page(); return; }
    header('Content-Type: ' . ((new finfo(FILEINFO_MIME_TYPE))->file($file) ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_.-]/', '-', basename($file)) . '"');
    header('Content-Length: ' . filesize($file));
    header('X-Content-Type-Options: nosniff');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        if (fpassthru($handle) !== false) db_run("UPDATE lab_resources SET download_count = download_count + 1 WHERE id = ?", [$resourceId]);
    }
    fclose($handle);
}

function posts_api(): void
{
    $limit = min(max((int) ($_GET['limit'] ?? 6), 1), 12);
    $offset = max((int) ($_GET['offset'] ?? 0), 0);
    $excludeIds = [];
    $excludeIdsRaw = trim((string) ($_GET['exclude_ids'] ?? ''));
    if ($excludeIdsRaw !== '') {
        foreach (explode(',', $excludeIdsRaw) as $chunk) {
            $id = (int) trim($chunk);
            if ($id > 0) {
                $excludeIds[] = $id;
            }
        }
    }
    $legacyExcludeId = max((int) ($_GET['exclude_id'] ?? 0), 0);
    if ($legacyExcludeId > 0) {
        $excludeIds[] = $legacyExcludeId;
    }
    $excludeIds = array_values(array_unique($excludeIds));
    $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 200);
    $category = trim((string) ($_GET['category'] ?? ''));
    $excludeCategory = trim((string) ($_GET['exclude_category'] ?? ''));
    $excludeFeatured = in_array(strtolower(trim((string) ($_GET['exclude_featured'] ?? '0'))), ['1', 'true', 'yes'], true);
    $posts = published_posts($limit + 1, $offset, $excludeIds, $search, $category, $excludeCategory, $excludeFeatured);
    $hasMore = count($posts) > $limit;
    $posts = array_slice($posts, 0, $limit);
    ob_start();
    foreach ($posts as $post) {
        echo '<div class="col-md-6 col-xl-4 infinite-post-item">';
        require __DIR__ . '/views/partials/post_card.php';
        echo '</div>';
    }
    $html = ob_get_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['html' => $html, 'has_more' => $hasMore, 'next_offset' => $offset + count($posts)]);
}

function subscribe_newsletter(): void
{
    $source = is_string($_POST['source'] ?? null) ? $_POST['source'] : 'home';
    $returnPath = $source === 'author-profile' ? '/jovani' : '/';
    $returnUrl = url_path($returnPath, $GLOBALS['locale']) . '#newsletter';
    if (!verify_csrf()) {
        flash('Formulario expirado.', 'danger');
        redirect_to($returnUrl, 303);
    }
    if (!rate_limit_allowed('newsletter', 10, 3600)) { http_response_code(429); echo 'Espera unos minutos antes de volver a intentarlo.'; return; }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191) {
        flash('Agrega un email valido.', 'danger');
        redirect_to($returnUrl, 303);
    }
    subscribe_or_reactivate_newsletter($email, $GLOBALS['locale'], mb_substr($source, 0, 64));
    flash('Ya estas en la lista.', 'success');
    redirect_to($returnUrl, 303);
}

function newsletter_unsubscribe_page(string $token): void
{
    $ok = unsubscribe_newsletter_token($token);
    flash($ok ? 'Te dimos de baja del newsletter.' : 'No encontramos esa suscripcion.', $ok ? 'success' : 'warning');
    redirect_to(url_path('/', $GLOBALS['locale']));
}

function submit_contact(): void
{
    $returnPath = ($_POST['source'] ?? '') === 'author-profile' ? '/jovani' : '/';
    $returnUrl = url_path($returnPath, $GLOBALS['locale']) . '#contact';
    if (!verify_csrf()) {
        flash('Formulario expirado.', 'danger');
        redirect_to($returnUrl, 303);
    }
    if (!rate_limit_allowed('contact', 5, 300)) { http_response_code(429); echo 'Espera unos minutos antes de volver a intentarlo.'; return; }
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $topic = trim((string) ($_POST['topic'] ?? ''));
    if ($name === '' || mb_strlen($name) > 191 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191 || $message === '' || mb_strlen($message) > 10000 || mb_strlen($topic) > 255) {
        flash('Agrega nombre, email y mensaje.', 'danger');
        redirect_to($returnUrl, 303);
    }
    db_run("INSERT INTO contact_messages (name, email, topic, message, locale, status, created_at) VALUES (?, ?, ?, ?, ?, 'new', ?)", [$name, $email, $topic, $message, $GLOBALS['locale'], date('c')]);
    flash('Mensaje recibido. Lo revisamos pronto.', 'success');
    redirect_to($returnUrl, 303);
}

function create_comment(string $slug, string $categorySlug = ''): void
{
    $post = find_post($slug, $GLOBALS['locale']);
    if (!$post || !verify_csrf()) {
        flash('No se pudo enviar el comentario.', 'danger');
        redirect_to($post ? post_url($post, $GLOBALS['locale']) : url_path('/', $GLOBALS['locale']));
    }
    $targetUrl = post_url($post, $GLOBALS['locale']);
    if (!rate_limit_allowed('comment', 5, 300)) { http_response_code(429); echo 'Espera unos minutos antes de volver a comentar.'; return; }
    if (!verify_recaptcha($GLOBALS['site'])) {
        flash('Completa reCAPTCHA para enviar el comentario.', 'danger');
        redirect_to($targetUrl . '#comentarios');
    }
    $name = trim((string) ($_POST['author_name'] ?? ''));
    $email = trim((string) ($_POST['author_email'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
    if ($name === '' || mb_strlen($name) > 191 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191 || $body === '' || mb_strlen($body) > 10000) {
        flash('Completa nombre, email y comentario.', 'danger');
        redirect_to($targetUrl . '#comentarios');
    }
    $status = setting_is_enabled($GLOBALS['site']['comments_moderation_enabled'] ?? '1') ? 'pending' : 'approved';
    db_run("INSERT INTO comments (post_id, author_name, author_email, body, rating, status, ip_address, user_agent, is_recaptcha_verified, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        $post['id'], $name, $email, $body, $rating, $status, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', setting_is_enabled($GLOBALS['site']['recaptcha_enabled'] ?? '0') ? 1 : 0, date('c'), date('c')
    ]);
    flash($status === 'pending' ? 'Comentario enviado a moderacion.' : 'Comentario publicado.', 'success');
    redirect_to($targetUrl . '#comentarios');
}

function sitemap_xml(): void
{
    header('Content-Type: application/xml; charset=utf-8');
    $total = count_published_posts();
    $pages = max(1, (int) ceil($total / 1000));
    $page = max(1, (int) ($_GET['page'] ?? 1));
    if ($pages > 1 && !isset($_GET['page'])) {
        echo '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        for ($i = 1; $i <= $pages; $i++) echo '<sitemap><loc>' . e(absolute_url(url_path('/sitemap.xml')) . '?page=' . $i) . '</loc></sitemap>';
        echo '</sitemapindex>';
        return;
    }
    if ($page > $pages) { http_response_code(404); return; }
    $urls = [
        absolute_url(url_path('/')),
        absolute_url(url_path('/', 'es')),
        absolute_url(url_path('/blog')),
        absolute_url(url_path('/blog', 'es')),
        absolute_url(url_path('/jovani')),
        absolute_url(url_path('/jovani', 'es')),
        absolute_url(url_path('/lab')),
        absolute_url(url_path('/lab', 'es')),
        absolute_url(url_path('/privacy')),
        absolute_url(url_path('/privacy', 'es')),
    ];
    foreach (public_categories(false) as $category) {
        $canonical = (string) ($category['canonical_slug'] ?? $category['slug'] ?? '');
        foreach (SUPPORTED_LOCALES as $language) $urls[] = absolute_url(url_path('/' . localized_category_slug($canonical, $language), $language));
    }
    if ($page > 1) $urls = [];
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach (array_unique($urls) as $url) {
        echo "  <url><loc>" . e($url) . "</loc></url>\n";
    }
    foreach (published_posts(1000, ($page - 1) * 1000) as $post) {
        foreach (SUPPORTED_LOCALES as $language) {
            $translation = get_post_translation((int) $post['id'], $language);
            $modified = max(strtotime((string) ($post['updated_at'] ?? '')) ?: 0, strtotime((string) ($translation['updated_at'] ?? '')) ?: 0);
            echo '<url><loc>' . e(absolute_url(post_url($post, $language))) . '</loc>';
            if ($modified > 0) echo '<lastmod>' . date(DATE_W3C, $modified) . '</lastmod>';
            echo "</url>\n";
        }
    }
    echo "</urlset>";
}

function robots_txt(): void
{
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nAllow: /\nDisallow: " . url_path('/admin') . "\nDisallow: " . url_path('/api') . "\nSitemap: " . absolute_url(url_path('/sitemap.xml')) . "\n";
}

function not_found_page(): void
{
    http_response_code(404);
    view('not_found', ['metaTitle' => '404', 'metaDescription' => 'Page not found', 'metaRobots' => 'noindex,follow']);
}

function admin_dispatch(string $path): void
{
    require_once __DIR__ . '/professional.php';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($path === '/admin/login') {
        admin_login();
        return;
    }
    if ($path === '/admin/logout') {
        if ($method !== 'POST') { http_response_code(405); header('Allow: POST'); return; }
        require_admin();
        if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
        $_SESSION = [];
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', ['expires'=>time()-3600, 'path'=>$cookie['path'], 'secure'=>$cookie['secure'], 'httponly'=>true, 'samesite'=>'Lax']);
        session_destroy();
        redirect_to(url_path('/admin/login'), 303);
    }
    require_admin();
    if ($path === '/admin' || $path === '/admin/') {
        admin_dashboard();
    } elseif ($path === '/admin/professional') {
        professional_workspace();
    } elseif ($path === '/admin/seo') {
        admin_seo();
    } elseif ($path === '/admin/posts') {
        admin_posts();
    } elseif ($path === '/admin/posts/new') {
        admin_post_form();
    } elseif (preg_match('#^/admin/posts/(\d+)/edit$#', $path, $m)) {
        admin_post_form((int) $m[1]);
    } elseif (preg_match('#^/admin/posts/(\d+)/delete$#', $path, $m) && $method === 'POST') {
        admin_delete_post((int) $m[1]);
    } elseif ($path === '/admin/cv') {
        admin_cv($method);
    } elseif ($path === '/admin/case-studies') {
        admin_posts('casos-de-estudio');
    } elseif ($path === '/admin/categories') {
        admin_categories();
    } elseif ($path === '/admin/categories/new') {
        admin_category_form();
    } elseif (preg_match('#^/admin/categories/(\d+)/edit$#', $path, $m)) {
        admin_category_form((int) $m[1]);
    } elseif (preg_match('#^/admin/categories/(\d+)/delete$#', $path, $m) && $method === 'POST') {
        admin_delete_category((int) $m[1]);
    } elseif ($path === '/admin/authors') {
        admin_authors();
    } elseif ($path === '/admin/authors/new') {
        admin_author_form();
    } elseif (preg_match('#^/admin/authors/(\d+)/edit$#', $path, $m)) {
        admin_author_form((int) $m[1]);
    } elseif (preg_match('#^/admin/authors/(\d+)/delete$#', $path, $m) && $method === 'POST') {
        admin_delete_author((int) $m[1]);
    } elseif (preg_match('#^/admin/authors/(\d+)/orcid/connect$#', $path, $m)) {
        admin_orcid_connect((int) $m[1]);
    } elseif (preg_match('#^/admin/authors/(\d+)/orcid/disconnect$#', $path, $m) && $method === 'POST') {
        admin_orcid_disconnect((int) $m[1]);
    } elseif ($path === '/admin/orcid/callback') {
        admin_orcid_callback();
    } elseif ($path === '/admin/comments') {
        admin_comments();
    } elseif (preg_match('#^/admin/comments/(\d+)/status$#', $path, $m) && $method === 'POST') {
        admin_update_comment((int) $m[1]);
    } elseif (preg_match('#^/admin/comments/(\d+)/delete$#', $path, $m) && $method === 'POST') {
        admin_delete_comment((int) $m[1]);
    } elseif ($path === '/admin/newsletter') {
        admin_newsletter();
    } elseif ($path === '/admin/contacts') {
        admin_contacts();
    } elseif (preg_match('#^/admin/contacts/(\d+)/status$#', $path, $m) && $method === 'POST') {
        admin_update_contact((int) $m[1]);
    } elseif ($path === '/admin/newsletter/subscribers/new' && $method === 'POST') {
        admin_add_subscriber();
    } elseif (preg_match('#^/admin/newsletter/subscribers/(\d+)/status$#', $path, $m) && $method === 'POST') {
        admin_update_subscriber((int) $m[1]);
    } elseif (preg_match('#^/admin/newsletter/posts/(\d+)/send$#', $path, $m) && $method === 'POST') {
        admin_send_newsletter_post((int) $m[1]);
    } elseif ($path === '/admin/uploads/image' && $method === 'POST') {
        admin_upload_editor_image();
    } elseif ($path === '/admin/lab-resources') {
        admin_lab_resources();
    } elseif ($path === '/admin/lab-resources/new') {
        admin_lab_form();
    } elseif (preg_match('#^/admin/lab-resources/(\d+)/edit$#', $path, $m)) {
        admin_lab_form((int) $m[1]);
    } elseif (preg_match('#^/admin/lab-resources/(\d+)/delete$#', $path, $m) && $method === 'POST') {
        admin_delete_lab((int) $m[1]);
    } elseif ($path === '/admin/settings') {
        admin_settings();
    } else {
        not_found_page();
    }
}

function admin_username(): string
{
    return (string) ($_SESSION['admin_username'] ?? (admin_account()['username'] ?? ''));
}

function admin_account(?string $username = null): ?array
{
    $stored = [];
    foreach (db_all("SELECT `key`, value FROM settings WHERE `key` IN ('cms_username','cms_password_hash')") as $row) $stored[$row['key']] = $row['value'];
    $hash = trim((string) ($stored['cms_password_hash'] ?? '')) ?: trim((string) app_config('admin_password_hash', ''));
    $legacy = $hash === '' ? (string) app_config('admin_password', '') : '';
    $configuredName = trim((string) ($stored['cms_username'] ?? app_config('admin_username', '')));
    if ($configuredName === '' && ($hash !== '' || $legacy !== '')) $configuredName = 'admin';
    if ($hash !== '' || $legacy !== '') {
        // Preserve the original configured login as the sole authority when it
        // exists; do not reactivate dormant users from an older SQL snapshot.
        return $username === null || $username === $configuredName
            ? ['username'=>$configuredName, 'hash'=>$hash, 'legacy_password'=>$legacy, 'source'=>'settings', 'id'=>null]
            : null;
    }
    $row = $username === null ? db_one('SELECT id, username, password_hash FROM users ORDER BY id LIMIT 1') : db_one('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1', [$username]);
    return $row && $row['password_hash'] !== '' ? ['username'=>$row['username'], 'hash'=>$row['password_hash'], 'legacy_password'=>'', 'source'=>'users', 'id'=>(int) $row['id']] : null;
}

function admin_password_ok(string $password, ?string $username = null): bool
{
    if ($password === '' || strlen($password) > 1024) return false;
    $account = admin_account($username ?? admin_username());
    if (!$account) return false;
    return $account['hash'] !== '' ? password_verify($password, $account['hash']) : $account['legacy_password'] !== '' && hash_equals($account['legacy_password'], $password);
}

function admin_auth_fingerprint(array $account): string
{
    return hash('sha256', $account['username'] . "\0" . $account['source'] . "\0" . $account['hash'] . "\0" . $account['legacy_password']);
}

function admin_login(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $username = trim((string) ($_POST['username'] ?? ''));
        if (!rate_limit_allowed('login', 10, 900)) {
            http_response_code(429); header('Retry-After: 900'); flash('Demasiados intentos. Espera 15 minutos.', 'danger');
        } elseif (verify_csrf() && mb_strlen($username) <= 191 && admin_password_ok((string) ($_POST['password'] ?? ''), $username)) {
            $account = admin_account($username);
            if ($account['legacy_password'] !== '') {
                save_setting('cms_username', $username);
                save_setting('cms_password_hash', password_hash((string) $_POST['password'], PASSWORD_DEFAULT));
                $account = admin_account($username);
            }
            session_regenerate_id(true);
            $_SESSION = [];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_auth_fingerprint'] = admin_auth_fingerprint($account);
            $_SESSION['admin_started_at'] = time();
            csrf_token();
            flash('Bienvenido al CMS.', 'success');
            redirect_to(url_path('/admin'), 303);
        } else {
            http_response_code(401);
            flash('Usuario o contraseña incorrectos.', 'danger');
        }
    }
    view('admin/login', ['metaTitle' => 'Login CMS', 'setupReady'=>admin_account() !== null]);
}

function require_admin(): void
{
    $account = isset($_SESSION['admin_username']) ? admin_account((string) $_SESSION['admin_username']) : null;
    if (empty($_SESSION['admin_logged_in']) || !$account || !hash_equals(admin_auth_fingerprint($account), (string) ($_SESSION['admin_auth_fingerprint'] ?? ''))
        || time() - (int) ($_SESSION['admin_started_at'] ?? 0) > (int) app_config('session_lifetime', 43200)) {
        unset($_SESSION['admin_logged_in'], $_SESSION['admin_username'], $_SESSION['admin_auth_fingerprint'], $_SESSION['admin_started_at']);
        redirect_to(url_path('/admin/login'));
    }
}

function admin_cv_is_owner(): bool
{
    $owner = trim((string) app_config('cv_owner_username', ''));
    $username = (string) ($_SESSION['admin_username'] ?? '');
    return $owner !== '' && $username !== '' && hash_equals($owner, $username);
}

function admin_cv(string $method): void
{
    header('Cache-Control: no-store, private');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    if (!admin_cv_is_owner()) {
        http_response_code(403);
        if ($method !== 'HEAD') echo 'El acceso al CV privado no está autorizado para esta cuenta.';
        return;
    }
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        http_response_code(405);
        header('Allow: GET, HEAD');
        return;
    }

    require __DIR__ . '/cv-owner.php';
    render_owner_cv($method);
}

function admin_dashboard(): void
{
    $stats = db_one("SELECT SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published_count, SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_count FROM posts") ?: [];
    $commentStats = db_one("SELECT COUNT(*) AS total_comments, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_comments FROM comments") ?: [];
    $contactStats = db_one("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count FROM contact_messages") ?: [];
    $newsletterStats = newsletter_stats();
    $recentPosts = db_all("SELECT posts.*, categories.name AS category_name FROM posts LEFT JOIN categories ON categories.id = posts.category_id ORDER BY posts.updated_at DESC LIMIT 6");
    view('admin/dashboard', ['metaTitle' => 'Dashboard CMS', 'stats' => $stats, 'commentStats' => $commentStats, 'newsletterStats' => $newsletterStats, 'contactStats' => $contactStats, 'recentPosts' => $recentPosts]);
}

function admin_posts(string $category = ''): void
{
    $where = [];
    $params = [];
    $query = admin_filter_value('q');
    $status = in_array($_GET['status'] ?? '', ['draft','published'], true) ? $_GET['status'] : '';
    $selectedCategory = $category !== '' ? $category : admin_filter_value('category');
    if ($selectedCategory !== '') {
        $selectedCategory = canonical_category_slug($selectedCategory);
        $where[] = 'categories.slug = ?'; $params[] = $selectedCategory;
    }
    if ($status !== '') { $where[] = 'posts.status = ?'; $params[] = $status; }
    append_post_search($where, $params, $query);
    $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = (int) (db_one("SELECT COUNT(*) AS total FROM posts LEFT JOIN categories ON categories.id = posts.category_id $clause", $params)['total'] ?? 0);
    $pageSize = 30; $totalPages = max(1, (int) ceil($total / $pageSize)); $page = min($totalPages, max(1, (int) admin_filter_value('page')));
    $posts = db_all("SELECT posts.*, categories.name AS category_name, categories.slug AS category_slug, (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND status = 'approved') AS approved_comments FROM posts LEFT JOIN categories ON categories.id = posts.category_id $clause ORDER BY posts.updated_at DESC, posts.id DESC LIMIT ? OFFSET ?", [...$params, $pageSize, ($page - 1) * $pageSize]);
    view('admin/posts', ['metaTitle'=>'Posts CMS', 'posts'=>$posts, 'caseMode'=>$category === 'casos-de-estudio', 'filters'=>['q'=>$query,'status'=>$status,'category'=>$selectedCategory], 'categories'=>admin_category_options(), 'page'=>$page, 'total'=>$total, 'totalPages'=>$totalPages, 'pageSize'=>$pageSize]);
}

function admin_contacts(): void
{
    $query = admin_filter_value('q');
    $status = in_array($_GET['status'] ?? '', ['new','read','replied'], true) ? $_GET['status'] : '';
    $where = []; $params = [];
    if ($status !== '') { $where[] = 'status = ?'; $params[] = $status; }
    if ($query !== '') { $where[] = "(name LIKE ? ESCAPE '!' OR email LIKE ? ESCAPE '!' OR topic LIKE ? ESCAPE '!' OR message LIKE ? ESCAPE '!')"; $like = search_like_pattern($query); array_push($params, $like, $like, $like, $like); }
    $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = (int) (db_one("SELECT COUNT(*) AS total FROM contact_messages $clause", $params)['total'] ?? 0);
    $pageSize = 30; $totalPages = max(1, (int) ceil($total / $pageSize)); $page = min($totalPages, max(1, (int) admin_filter_value('page')));
    $contacts = db_all("SELECT * FROM contact_messages $clause ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?", [...$params, $pageSize, ($page - 1) * $pageSize]);
    view('admin/contacts', ['metaTitle'=>'Contactos CMS', 'contacts'=>$contacts, 'filters'=>['q'=>$query,'status'=>$status], 'page'=>$page, 'total'=>$total, 'totalPages'=>$totalPages, 'pageSize'=>$pageSize]);
}

function admin_update_contact(int $id): void
{
    if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
    $status = admin_filter_value('status', $_POST);
    if (!in_array($status, ['new','read','replied'], true)) { http_response_code(422); echo 'Estado no válido.'; return; }
    if (!db_one('SELECT id FROM contact_messages WHERE id = ?', [$id])) { not_found_page(); return; }
    db_run('UPDATE contact_messages SET status = ? WHERE id = ?', [$status, $id]);
    flash('Estado del mensaje actualizado.', 'success');
    redirect_to(admin_list_return('/admin/contacts'), 303);
}

function admin_post_form(?int $postId = null): void
{
    require_once __DIR__ . '/professional.php';
    $post = $postId ? db_one("SELECT * FROM posts WHERE id = ?", [$postId]) : null;
    if ($postId && !$post) { not_found_page(); return; }
    $categories = admin_category_options();
    $authors = authors(true);
    $translationInput = is_array($_POST['translations'] ?? null) ? $_POST['translations'] : [];
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!verify_csrf()) {
            flash('Formulario expirado.', 'danger');
            redirect_to(url_path('/admin/posts'));
        }
        $title = trim((string) ($_POST['title'] ?? ''));
        $content = trim((string) ($_POST['content_markdown'] ?? ''));
        $contentHtml = sanitize_article_html(trim((string) ($_POST['content_html'] ?? '')));
        $slug = slugify(trim((string) ($_POST['slug'] ?? '')) ?: ($post['slug'] ?? $title));
        $uploadError = '';
        try {
            $uploadedCover = uploaded_file_url('cover_image_file', 'posts');
        } catch (RuntimeException $error) {
            $uploadError = $error->getMessage();
            $uploadedCover = '';
        }
        $authorIdInput = trim((string) ($_POST['author_id'] ?? ''));
        $authorId = $authorIdInput !== '' ? (int) $authorIdInput : null;
        $selectedAuthor = $authorId ? author_by_id($authorId) : null;
        $authorName = $selectedAuthor ? $selectedAuthor['name'] : (($post['author_name'] ?? '') ?: ($GLOBALS['site']['author_name'] ?? 'Jovani'));
        $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
        $publishedAt = $status === 'published' ? ($post['published_at'] ?? date('c')) : null;
        $payload = [
            'title' => $title,
            'slug' => unique_post_slug($slug, $postId),
            'excerpt' => trim((string) ($_POST['excerpt'] ?? '')) ?: seo_text($contentHtml !== '' ? $contentHtml : markdown_to_html($content), 240),
            'content_markdown' => $content,
            'content_html' => $contentHtml !== '' ? $contentHtml : sanitize_article_html(markdown_to_html($content)),
            'cover_image' => $uploadedCover ?: safe_content_url(trim((string) ($_POST['cover_image'] ?? ''))),
            'seo_title' => seo_text(trim((string) ($_POST['seo_title'] ?? '')) ?: $title, 255),
            'seo_description' => seo_description(['seo_description' => $_POST['seo_description'] ?? '', 'excerpt' => $_POST['excerpt'] ?? '', 'content_html' => $contentHtml, 'content_markdown' => $content]),
            'status' => $status,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'author_name' => $authorName,
            'author_id' => $selectedAuthor ? $authorId : null,
            'category_id' => !empty($_POST['category_id']) && db_one('SELECT id FROM categories WHERE id = ?', [(int) $_POST['category_id']]) ? (int) $_POST['category_id'] : null,
            'read_time' => estimate_read_time($content !== '' ? $content : strip_tags($contentHtml)),
            'updated_at' => date('c'),
            'published_at' => $publishedAt,
        ];
        if ($uploadError !== '') {
            flash($uploadError, 'danger');
        } elseif ($title === '' || $payload['excerpt'] === '' || ($content === '' && trim(strip_tags($contentHtml)) === '')) {
            flash('Titulo, extracto y contenido son obligatorios.', 'danger');
        } elseif (mb_strlen($title) > 255 || mb_strlen($payload['excerpt']) > 10000 || strlen($content) > 2097152 || strlen($contentHtml) > 2097152 || strlen($payload['slug']) > 191) {
            flash('El artículo supera el tamaño permitido. Revisa el título, extracto o contenido.', 'danger');
        } else {
            $oldStatus = $post['status'] ?? 'draft';
            try {
                db()->beginTransaction();
                if ($postId) {
                    db_run("UPDATE posts SET title=?, slug=?, excerpt=?, content_markdown=?, content_html=?, cover_image=?, seo_title=?, seo_description=?, status=?, is_featured=?, author_name=?, author_id=?, category_id=?, read_time=?, updated_at=?, published_at=? WHERE id=?", array_merge(array_values($payload), [$postId]));
                    $savedPostId = $postId;
                } else {
                    db_run(
                "INSERT INTO posts (title, slug, excerpt, content_markdown, content_html, cover_image, seo_title, seo_description, status, is_featured, author_name, author_id, category_id, read_time, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $payload['title'], $payload['slug'], $payload['excerpt'], $payload['content_markdown'],
                    $payload['content_html'], $payload['cover_image'], $payload['seo_title'], $payload['seo_description'],
                    $payload['status'], $payload['is_featured'], $payload['author_name'], $payload['author_id'], $payload['category_id'],
                    $payload['read_time'], date('c'), $payload['updated_at'], $payload['published_at'],
                ]
                    );
                    $savedPostId = (int) db()->lastInsertId();
                }
                foreach (SUPPORTED_LOCALES as $itemLocale) save_post_translation($savedPostId, $itemLocale, is_array($translationInput[$itemLocale] ?? null) ? $translationInput[$itemLocale] : []);
                db()->commit();
            } catch (Throwable $error) {
                if (db()->inTransaction()) db()->rollBack();
                $savedPostId = null;
                flash('No se guardó el artículo. Revisa el tamaño y los campos de sus traducciones; tu texto permanece en el editor.', 'danger');
            }
            if ($savedPostId) {
                if ($status === 'published' && $oldStatus !== 'published') {
                    $newsletterResult = send_post_newsletter($savedPostId);
                    flash('Post guardado. Newsletter: ' . $newsletterResult['message'], 'success');
                } else flash('Post guardado.', 'success');
                redirect_to(url_path(admin_post_list_path($payload['category_id'])), 303);
            }
        }
        $post = array_merge($post ?? [], $payload);
    }
    if (!$post) {
        $preselectedCategoryId = '';
        $requestedCategory = canonical_category_slug(admin_filter_value('category'));
        foreach ($categories as $category) {
            if (($category['slug'] ?? '') === $requestedCategory) {
                $preselectedCategoryId = $category['id'];
                break;
            }
        }
        $defaultAuthor = $authors[0] ?? null;
        $post = ['title' => '', 'slug' => '', 'excerpt' => '', 'content_markdown' => '', 'content_html' => '', 'cover_image' => '', 'seo_title' => '', 'seo_description' => '', 'status' => 'draft', 'is_featured' => 0, 'author_name' => $defaultAuthor['name'] ?? ($GLOBALS['site']['author_name'] ?? 'Jovani'), 'author_id' => $defaultAuthor['id'] ?? null, 'category_id' => $preselectedCategoryId];
    }
    if (!$postId && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && is_string($_GET['template'] ?? null)) {
        $template = professional_template($_GET['template']);
        if ($template) {
            $post = array_merge($post, array_intersect_key($template, array_flip(['title','excerpt','content_markdown'])));
            $post['content_html'] = sanitize_article_html(markdown_to_html($post['content_markdown']));
            foreach ($categories as $category) if (($category['slug'] ?? '') === $template['category']) $post['category_id'] = $category['id'];
            $post['author_name'] = 'Jovani Treviño';
            $post['author_id'] = null;
            foreach ($authors as $author) if (($author['slug'] ?? '') === 'jovani-trevino') $post['author_id'] = $author['id'];
        }
    }
    $emptyTranslation = ['title' => '', 'slug' => '', 'excerpt' => '', 'content_markdown' => '', 'content_html' => '', 'seo_title' => '', 'seo_description' => ''];
    $translations = [];
    foreach (SUPPORTED_LOCALES as $itemLocale) {
        $savedTranslation = $postId ? (get_post_translation($postId, $itemLocale) ?: []) : [];
        $postedTranslation = is_array($translationInput[$itemLocale] ?? null) ? $translationInput[$itemLocale] : [];
        $translations[$itemLocale] = array_merge($emptyTranslation, $savedTranslation, $postedTranslation);
        $translations[$itemLocale]['content_html'] = sanitize_article_html((string) $translations[$itemLocale]['content_html']);
    }
    $post['content_html'] = sanitize_article_html((string) ($post['content_html'] ?? ''));
    $editorListPath = admin_post_list_path(!empty($post['category_id']) ? (int) $post['category_id'] : null);
    view('admin/post_form', ['editorListPath' => $editorListPath, 'caseMode' => $editorListPath === '/admin/case-studies', 'metaTitle' => $postId ? 'Editar post' : 'Nuevo post', 'post' => $post, 'categories' => $categories, 'authors' => $authors, 'postId' => $postId, 'translations' => $translations]);
}

function unique_post_slug(string $slug, ?int $ignoreId = null): string
{
    $base = $slug;
    $suffix = 2;
    while (true) {
        $sql = "SELECT id FROM posts WHERE slug = ?";
        $params = [$slug];
        if ($ignoreId) {
            $sql .= " AND id != ?";
            $params[] = $ignoreId;
        }
        $translationSql = "SELECT post_id FROM post_translations WHERE slug = ?";
        $translationParams = [$slug];
        if ($ignoreId) {
            $translationSql .= " AND post_id != ?";
            $translationParams[] = $ignoreId;
        }
        if (!db_one($sql, $params) && !db_one($translationSql, $translationParams)) {
            return $slug;
        }
        $slug = $base . '-' . $suffix++;
    }
}

function admin_delete_post(int $id): void
{
    if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
    $post = db_one('SELECT category_id FROM posts WHERE id = ?', [$id]);
    if (!$post) { not_found_page(); return; }
    $path = admin_post_list_path($post['category_id'] ? (int) $post['category_id'] : null);
    db_run('DELETE FROM posts WHERE id = ?', [$id]);
    flash('Publicación eliminada.', 'success');
    redirect_to(admin_list_return($path), 303);
}

function admin_categories(): void
{
    $data = admin_filter_collection(admin_category_options(), ['name','name_es','name_en','slug','description']);
    view('admin/categories', $data + ['metaTitle'=>'Categorías CMS', 'categories'=>$data['rows']]);
}

function admin_category_form(?int $id = null): void
{
    $category = $id ? db_one("SELECT * FROM categories WHERE id = ?", [$id]) : null;
    $systemCategory = $category ? system_category_definition((string) ($category['slug'] ?? '')) : null;
    $blockedCategoryIds = $id ? category_descendant_ids($id) : [];
    $categoryOptions = array_filter(admin_category_options(), static function (array $item) use ($blockedCategoryIds): bool {
        return !in_array((int) $item['id'], $blockedCategoryIds, true);
    });
    $emptyCategory = ['name' => '', 'slug' => '', 'name_en' => '', 'name_es' => '', 'slug_en' => '', 'slug_es' => '', 'description' => '', 'description_en' => '', 'description_es' => '', 'accent_color' => '#c9853b', 'icon_class' => 'bi bi-megaphone-fill', 'spotlight' => '', 'spotlight_en' => '', 'spotlight_es' => '', 'parent_id' => null, 'show_in_nav' => 1, 'nav_order' => 0];
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!verify_csrf()) {
            redirect_to(url_path('/admin/categories'));
        }
        $nameEn = trim((string) ($_POST['name_en'] ?? ''));
        $nameEs = trim((string) ($_POST['name_es'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? '')) ?: $nameEn ?: $nameEs;
        $slug = $systemCategory
            ? (string) ($systemCategory['slug'] ?? '')
            : unique_category_slug(trim((string) ($_POST['slug'] ?? '')) ?: trim((string) ($_POST['slug_en'] ?? '')) ?: trim((string) ($_POST['slug_es'] ?? '')) ?: $name, $id);
        $parentId = max(0, (int) ($_POST['parent_id'] ?? 0));
        if ($id && in_array($parentId, category_descendant_ids($id), true)) {
            $parentId = 0;
        }
        $slugEnInput = trim((string) ($_POST['slug_en'] ?? ''));
        $slugEsInput = trim((string) ($_POST['slug_es'] ?? ''));
        $accentColor = normalize_hex_color((string) ($_POST['accent_color'] ?? '#c9853b'));
        $iconClass = normalize_bootstrap_icon_class((string) ($_POST['icon_class'] ?? 'bi bi-megaphone-fill'));
        $payload = [
            $name,
            $slug,
            $nameEn,
            $nameEs,
            $slugEnInput !== '' || $nameEn !== '' ? unique_category_slug($slugEnInput !== '' ? $slugEnInput : $nameEn, $id) : null,
            $slugEsInput !== '' || $nameEs !== '' ? unique_category_slug($slugEsInput !== '' ? $slugEsInput : $nameEs, $id) : null,
            trim((string) ($_POST['description'] ?? '')),
            trim((string) ($_POST['description_en'] ?? '')),
            trim((string) ($_POST['description_es'] ?? '')),
            $accentColor,
            $iconClass,
            trim((string) ($_POST['spotlight'] ?? '')),
            trim((string) ($_POST['spotlight_en'] ?? '')),
            trim((string) ($_POST['spotlight_es'] ?? '')),
            $parentId ?: null,
            isset($_POST['show_in_nav']) ? 1 : 0,
            (int) ($_POST['nav_order'] ?? 0),
        ];
        if ($name === '') {
            flash('Agrega al menos un nombre para la categoria.', 'danger');
            $category = array_merge($emptyCategory, $category ?: [], [
                'name' => $name,
                'slug' => $systemCategory ? (string) ($systemCategory['slug'] ?? '') : trim((string) ($_POST['slug'] ?? '')),
                'name_en' => $nameEn,
                'name_es' => $nameEs,
                'slug_en' => $slugEnInput,
                'slug_es' => $slugEsInput,
                'description' => trim((string) ($_POST['description'] ?? '')),
                'description_en' => trim((string) ($_POST['description_en'] ?? '')),
                'description_es' => trim((string) ($_POST['description_es'] ?? '')),
                'accent_color' => $accentColor,
                'icon_class' => $iconClass,
                'spotlight' => trim((string) ($_POST['spotlight'] ?? '')),
                'spotlight_en' => trim((string) ($_POST['spotlight_en'] ?? '')),
                'spotlight_es' => trim((string) ($_POST['spotlight_es'] ?? '')),
                'parent_id' => $parentId ?: null,
                'show_in_nav' => isset($_POST['show_in_nav']) ? 1 : 0,
                'nav_order' => (int) ($_POST['nav_order'] ?? 0),
            ]);
        } elseif ($id) {
            db_run("UPDATE categories SET name=?, slug=?, name_en=?, name_es=?, slug_en=?, slug_es=?, description=?, description_en=?, description_es=?, accent_color=?, icon_class=?, spotlight=?, spotlight_en=?, spotlight_es=?, parent_id=?, show_in_nav=?, nav_order=? WHERE id=?", array_merge($payload, [$id]));
        } elseif ($name !== '') {
            db_run("INSERT INTO categories (name, slug, name_en, name_es, slug_en, slug_es, description, description_en, description_es, accent_color, icon_class, spotlight, spotlight_en, spotlight_es, parent_id, show_in_nav, nav_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $payload);
        }
        if ($name !== '') {
            flash('Categoria guardada.', 'success');
            redirect_to(url_path('/admin/categories'));
        }
    }
    $category = array_merge($emptyCategory, $category ?: []);
    $categoryCanonical = (string) ($category['slug'] ?? '');
    foreach (SUPPORTED_LOCALES as $itemLocale) {
        $copy = CATEGORY_COPY[$categoryCanonical][$itemLocale] ?? null;
        $category['name_' . $itemLocale] = trim((string) ($category['name_' . $itemLocale] ?? '')) !== ''
            ? (string) $category['name_' . $itemLocale]
            : ($copy['name'] ?? ($itemLocale === DEFAULT_LOCALE ? (string) ($category['name'] ?? '') : ''));
        $category['slug_' . $itemLocale] = trim((string) ($category['slug_' . $itemLocale] ?? '')) !== ''
            ? (string) $category['slug_' . $itemLocale]
            : ($copy['slug'] ?? ($itemLocale === DEFAULT_LOCALE ? (string) ($category['slug'] ?? '') : ''));
        $category['description_' . $itemLocale] = trim((string) ($category['description_' . $itemLocale] ?? '')) !== ''
            ? (string) $category['description_' . $itemLocale]
            : ($copy['description'] ?? ($itemLocale === DEFAULT_LOCALE ? (string) ($category['description'] ?? '') : ''));
        $category['spotlight_' . $itemLocale] = trim((string) ($category['spotlight_' . $itemLocale] ?? '')) !== ''
            ? (string) $category['spotlight_' . $itemLocale]
            : ($copy['spotlight'] ?? ($itemLocale === DEFAULT_LOCALE ? (string) ($category['spotlight'] ?? '') : ''));
    }
    view('admin/category_form', ['metaTitle' => $id ? 'Editar categoria' : 'Nueva categoria', 'category' => $category, 'categoryId' => $id, 'categoryOptions' => $categoryOptions, 'systemCategory' => $systemCategory]);
}

function admin_delete_category(int $id): void
{
    $category = db_one("SELECT * FROM categories WHERE id = ? LIMIT 1", [$id]);
    $systemCategory = $category ? system_category_definition((string) ($category['slug'] ?? '')) : null;
    if ($systemCategory) {
        flash(($systemCategory['name'] ?? 'Esta categoria') . ' es una categoria del sistema. ' . ($systemCategory['publish_copy'] ?? 'Administra su contenido desde su modulo del CMS.'), 'warning');
        redirect_to(url_path('/admin/categories'));
    }

    if (verify_csrf()) {
        db_run("UPDATE posts SET category_id = NULL WHERE category_id = ?", [$id]);
        db_run("UPDATE categories SET parent_id = NULL WHERE parent_id = ?", [$id]);
        db_run("DELETE FROM categories WHERE id = ?", [$id]);
        flash('Categoria eliminada.', 'success');
    }
    redirect_to(url_path('/admin/categories'));
}

function admin_authors(): void
{
    $data = admin_filter_collection(authors(false), ['name','role','bio','slug'], ['active','inactive']);
    view('admin/authors', $data + ['metaTitle'=>'Autores CMS', 'authors'=>$data['rows']]);
}

function admin_author_form(?int $id = null): void
{
    $emptyAuthor = [
        'name' => '',
        'slug' => '',
        'role' => '',
        'role_en' => '',
        'role_es' => '',
        'headline' => '',
        'headline_en' => '',
        'headline_es' => '',
        'bio' => '',
        'bio_en' => '',
        'bio_es' => '',
        'expertise_areas' => '',
        'expertise_areas_en' => '',
        'expertise_areas_es' => '',
        'profile_labels' => '',
        'profile_labels_en' => '',
        'profile_labels_es' => '',
        'highlights' => '',
        'highlights_en' => '',
        'highlights_es' => '',
        'avatar_url' => '',
        'professional_links' => '',
        'linkedin_url' => '',
        'github_url' => '',
        'website_url' => '',
        'status' => 'active',
    ];
    $author = $id ? author_by_id($id) : null;
    if ($id && !$author && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET')) {
        flash('Ese autor ya no existe o el enlace ya no es valido.', 'warning');
        redirect_to(url_path('/admin/authors'));
    }
    $isFreshNewAuthor = !$id && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!verify_csrf()) {
            redirect_to(url_path('/admin/authors'));
        }
        $name = trim((string) ($_POST['name'] ?? ''));
        $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';
        $professionalLinkInput = is_array($_POST['professional_links'] ?? null) ? $_POST['professional_links'] : [];
        $professionalLinks = [];
        foreach (author_professional_link_catalog() as $key => $definition) {
            $item = is_array($professionalLinkInput[$key] ?? null) ? $professionalLinkInput[$key] : [];
            $professionalLinks[$key] = [
                'url' => trim((string) ($item['url'] ?? '')),
                'enabled' => isset($item['enabled']),
            ];
        }
        $professionalLinksJson = json_encode($professionalLinks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $author = array_merge($emptyAuthor, $author ?: [], [
            'name' => $name,
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'role' => trim((string) ($_POST['role'] ?? '')),
            'role_en' => trim((string) ($_POST['role_en'] ?? '')),
            'role_es' => trim((string) ($_POST['role_es'] ?? '')),
            'headline' => trim((string) ($_POST['headline'] ?? '')),
            'headline_en' => trim((string) ($_POST['headline_en'] ?? '')),
            'headline_es' => trim((string) ($_POST['headline_es'] ?? '')),
            'bio' => trim((string) ($_POST['bio'] ?? '')),
            'bio_en' => trim((string) ($_POST['bio_en'] ?? '')),
            'bio_es' => trim((string) ($_POST['bio_es'] ?? '')),
            'expertise_areas' => trim((string) ($_POST['expertise_areas'] ?? '')),
            'expertise_areas_en' => trim((string) ($_POST['expertise_areas_en'] ?? '')),
            'expertise_areas_es' => trim((string) ($_POST['expertise_areas_es'] ?? '')),
            'profile_labels' => trim((string) ($_POST['profile_labels'] ?? '')),
            'profile_labels_en' => trim((string) ($_POST['profile_labels_en'] ?? '')),
            'profile_labels_es' => trim((string) ($_POST['profile_labels_es'] ?? '')),
            'highlights' => trim((string) ($_POST['highlights'] ?? '')),
            'highlights_en' => trim((string) ($_POST['highlights_en'] ?? '')),
            'highlights_es' => trim((string) ($_POST['highlights_es'] ?? '')),
            'avatar_url' => trim((string) ($_POST['avatar_url'] ?? '')),
            'professional_links' => is_string($professionalLinksJson) ? $professionalLinksJson : '{}',
            'linkedin_url' => (string) ($professionalLinks['linkedin']['url'] ?? ''),
            'github_url' => (string) ($professionalLinks['github']['url'] ?? ''),
            'website_url' => (string) ($professionalLinks['website']['url'] ?? ''),
            'status' => $status,
        ]);

        if ($name === '') {
            flash('El nombre del autor es obligatorio.', 'danger');
        } else {
            try {
                $uploadedAvatar = uploaded_file_url('avatar_file', 'authors');
            } catch (RuntimeException $error) {
                flash($error->getMessage(), 'danger');
                redirect_to((string) ($_SERVER['REQUEST_URI'] ?? url_path('/admin/authors')));
            }
            $slug = unique_author_slug(trim((string) ($_POST['slug'] ?? '')) ?: $name, $id);
            $author['slug'] = $slug;
            if ($uploadedAvatar !== '') {
                $author['avatar_url'] = $uploadedAvatar;
            }
        }

        $payload = [
            $name,
            $author['slug'],
            $author['role'],
            $author['role_en'],
            $author['role_es'],
            $author['headline'],
            $author['headline_en'],
            $author['headline_es'],
            $author['bio'],
            $author['bio_en'],
            $author['bio_es'],
            $author['expertise_areas'],
            $author['expertise_areas_en'],
            $author['expertise_areas_es'],
            $author['profile_labels'],
            $author['profile_labels_en'],
            $author['profile_labels_es'],
            $author['highlights'],
            $author['highlights_en'],
            $author['highlights_es'],
            $author['avatar_url'],
            $author['professional_links'],
            $author['linkedin_url'],
            $author['github_url'],
            $author['website_url'],
            $author['status'],
            date('c'),
        ];
        if ($name === '') {
            // Let the form render with the current typed values instead of reusing stale data.
        } elseif ($id) {
            db_run("UPDATE authors SET name=?, slug=?, role=?, role_en=?, role_es=?, headline=?, headline_en=?, headline_es=?, bio=?, bio_en=?, bio_es=?, expertise_areas=?, expertise_areas_en=?, expertise_areas_es=?, profile_labels=?, profile_labels_en=?, profile_labels_es=?, highlights=?, highlights_en=?, highlights_es=?, avatar_url=?, professional_links=?, linkedin_url=?, github_url=?, website_url=?, status=?, updated_at=? WHERE id=?", array_merge($payload, [$id]));
            db_run("UPDATE posts SET author_name = ? WHERE author_id = ?", [$name, $id]);
            flash('Autor actualizado.', 'success');
            redirect_to(url_path('/admin/authors'));
        } else {
            db_run("INSERT INTO authors (name, slug, role, role_en, role_es, headline, headline_en, headline_es, bio, bio_en, bio_es, expertise_areas, expertise_areas_en, expertise_areas_es, profile_labels, profile_labels_en, profile_labels_es, highlights, highlights_en, highlights_es, avatar_url, professional_links, linkedin_url, github_url, website_url, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array_merge($payload, [date('c')]));
            flash('Autor creado.', 'success');
            redirect_to(url_path('/admin/authors'));
        }
    }
    $author = array_merge($emptyAuthor, $author ?: []);
    view('admin/author_form', ['metaTitle' => $id ? 'Editar autor' : 'Nuevo autor', 'author' => $author, 'authorId' => $id, 'authorFormIsFresh' => $isFreshNewAuthor, 'authorProfessionalLinks' => author_professional_links($author)]);
}

function admin_delete_author(int $id): void
{
    if (verify_csrf()) {
        $author = author_by_id($id);
        if ($author) {
            $replacementAuthor = db_one("SELECT id, name FROM authors WHERE id != ? ORDER BY CASE WHEN status = 'active' THEN 0 ELSE 1 END, name ASC LIMIT 1", [$id]);
            $fallbackAuthorName = trim((string) ($author['name'] ?? '')) ?: trim((string) ($GLOBALS['site']['author_name'] ?? 'Jovani'));
            db_run("UPDATE posts SET author_id = NULL, author_name = CASE WHEN author_name IS NULL OR author_name = '' THEN ? ELSE author_name END WHERE author_id = ?", [$fallbackAuthorName, $id]);
            if (($GLOBALS['site']['author_name'] ?? '') === ($author['name'] ?? '') && $replacementAuthor) {
                save_setting('author_name', (string) $replacementAuthor['name']);
                $GLOBALS['site']['author_name'] = (string) $replacementAuthor['name'];
            }
            db_run("DELETE FROM authors WHERE id = ?", [$id]);
            flash('Autor eliminado.', 'success');
        }
    }
    redirect_to(url_path('/admin/authors'));
}

function admin_orcid_connect(int $authorId): void
{
    $author = author_by_id($authorId);
    if (!$author) {
        flash('Ese autor ya no existe.', 'warning');
        redirect_to(url_path('/admin/authors'));
    }
    if (!orcid_is_configured()) {
        flash('Configura las credenciales ORCID en config.local.php o variables MS_ORCID_* antes de conectar autores.', 'warning');
        redirect_to(url_path('/admin/authors/' . $authorId . '/edit'));
    }

    $state = bin2hex(random_bytes(24));
    if (!isset($_SESSION['orcid_oauth']) || !is_array($_SESSION['orcid_oauth'])) {
        $_SESSION['orcid_oauth'] = [];
    }
    $_SESSION['orcid_oauth'][$state] = [
        'author_id' => $authorId,
        'created_at' => time(),
        'redirect_uri' => orcid_config()['redirect_uri'],
    ];

    redirect_to(orcid_authorization_url($state));
}

function admin_orcid_callback(): void
{
    $state = trim((string) ($_GET['state'] ?? ''));
    $pending = is_array($_SESSION['orcid_oauth'][$state] ?? null) ? $_SESSION['orcid_oauth'][$state] : null;
    if ($state !== '') {
        unset($_SESSION['orcid_oauth'][$state]);
    }

    $fallbackUrl = url_path('/admin/authors');
    $authorId = (int) ($pending['author_id'] ?? 0);
    if ($authorId > 0) {
        $fallbackUrl = url_path('/admin/authors/' . $authorId . '/edit');
    }

    if (!$pending || (time() - (int) ($pending['created_at'] ?? 0)) > 900) {
        flash('La conexion con ORCID expiro. Intentalo de nuevo.', 'warning');
        redirect_to($fallbackUrl);
    }
    if (!empty($_GET['error'])) {
        $message = trim((string) ($_GET['error_description'] ?? $_GET['error']));
        flash('ORCID no autorizo la conexion: ' . ($message ?: 'solicitud cancelada.'), 'warning');
        redirect_to($fallbackUrl);
    }

    $code = trim((string) ($_GET['code'] ?? ''));
    if ($code === '') {
        flash('ORCID no devolvio un codigo de autorizacion.', 'danger');
        redirect_to($fallbackUrl);
    }

    try {
        $payload = orcid_exchange_authorization_code($code, (string) ($pending['redirect_uri'] ?? ''));
        $orcidId = orcid_normalize_id((string) ($payload['orcid'] ?? ''));
        if ($orcidId === '') {
            throw new RuntimeException('ORCID no devolvio un identificador valido.');
        }

        db_run(
            'UPDATE authors SET orcid_id = ?, orcid_name = ?, orcid_verified_at = ?, updated_at = ? WHERE id = ?',
            [
                $orcidId,
                trim((string) ($payload['name'] ?? '')),
                date('c'),
                date('c'),
                $authorId,
            ]
        );
        flash('ORCID conectado: ' . $orcidId, 'success');
    } catch (Throwable $error) {
        flash('No se pudo conectar ORCID: ' . $error->getMessage(), 'danger');
    }

    redirect_to($fallbackUrl);
}

function admin_orcid_disconnect(int $authorId): void
{
    if (verify_csrf()) {
        db_run('UPDATE authors SET orcid_id = NULL, orcid_name = NULL, orcid_verified_at = NULL, updated_at = ? WHERE id = ?', [date('c'), $authorId]);
        flash('ORCID desconectado del autor.', 'success');
    }
    redirect_to(url_path('/admin/authors/' . $authorId . '/edit'));
}

function admin_comments(): void
{
    $data = admin_list_data('comments');
    view('admin/comments', $data + ['metaTitle'=>'Comentarios CMS', 'comments'=>$data['rows']]);
}

function admin_update_comment(int $id): void
{
    if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
    $status = admin_filter_value('status', $_POST);
    if (!in_array($status, ['pending','approved','rejected','spam'], true)) { http_response_code(422); echo 'Estado no válido.'; return; }
    if (!db_one('SELECT id FROM comments WHERE id = ?', [$id])) { not_found_page(); return; }
    db_run('UPDATE comments SET status = ?, updated_at = ? WHERE id = ?', [$status, date('c'), $id]);
    flash('Estado del comentario actualizado.', 'success');
    redirect_to(admin_list_return('/admin/comments'), 303);
}

function admin_delete_comment(int $id): void
{
    if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
    if (!db_one('SELECT id FROM comments WHERE id = ?', [$id])) { not_found_page(); return; }
    db_run('DELETE FROM comments WHERE id = ?', [$id]);
    flash('Comentario eliminado.', 'success');
    redirect_to(admin_list_return('/admin/comments'), 303);
}

function admin_newsletter(): void
{
    $availablePosts = db_all("
        SELECT posts.*, categories.name AS category_name, categories.slug AS category_slug
        FROM posts
        LEFT JOIN categories ON categories.id = posts.category_id
        WHERE posts.status = 'published'
        ORDER BY posts.published_at DESC, posts.updated_at DESC
        LIMIT 12
    ");
    $data = admin_list_data('newsletter');
    view('admin/newsletter', $data + [
        'metaTitle' => 'Newsletter CMS',
        'stats' => newsletter_stats(),
        'subscribers' => $data['rows'],
        'campaigns' => newsletter_campaigns(60),
        'availablePosts' => $availablePosts,
        'mailerStatus' => newsletter_mailer_status($GLOBALS['site']),
    ]);
}

function admin_add_subscriber(): void
{
    if (!verify_csrf()) {
        flash('Formulario expirado.', 'danger');
        redirect_to(url_path('/admin/newsletter'));
    }

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $locale = strtolower(trim((string) ($_POST['locale'] ?? DEFAULT_LOCALE)));
    if (!in_array($locale, SUPPORTED_LOCALES, true)) {
        $locale = DEFAULT_LOCALE;
    }
    $source = trim((string) ($_POST['source'] ?? 'cms'));
    $source = $source !== '' ? $source : 'cms';
    $status = in_array($_POST['status'] ?? 'active', ['active', 'unsubscribed'], true) ? (string) $_POST['status'] : 'active';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Agrega un email valido.', 'danger');
        redirect_to(url_path('/admin/newsletter'));
    }

    $created = save_admin_newsletter_subscriber($email, $locale, $source, $status);
    flash($created ? 'Suscriptor agregado al newsletter.' : 'Suscriptor existente actualizado.', 'success');
    redirect_to(url_path('/admin/newsletter'));
}
function admin_update_subscriber(int $id): void
{
    if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
    $status = admin_filter_value('status', $_POST);
    if (!in_array($status, ['active','unsubscribed'], true)) { http_response_code(422); echo 'Estado no válido.'; return; }
    if (!db_one('SELECT id FROM newsletter_subscribers WHERE id = ?', [$id])) { not_found_page(); return; }
    db_run('UPDATE newsletter_subscribers SET status = ?, unsubscribed_at = ? WHERE id = ?', [$status, $status === 'unsubscribed' ? date('c') : null, $id]);
    flash('Suscriptor actualizado.', 'success');
    redirect_to(admin_list_return('/admin/newsletter'), 303);
}

function admin_send_newsletter_post(int $postId): void
{
    if (!verify_csrf()) {
        redirect_to(url_path('/admin/newsletter'));
    }
    $force = isset($_POST['force']);
    $result = send_post_newsletter($postId, $force);
    flash('Newsletter: ' . $result['message'], $result['failed'] > 0 ? 'warning' : 'success');
    redirect_to(url_path('/admin/newsletter'));
}

function admin_upload_editor_image(): never
{
    header('Content-Type: application/json; charset=utf-8');

    if (!verify_csrf()) {
        http_response_code(419);
        echo json_encode(['ok' => false, 'message' => 'La sesion del editor expiro. Recarga la pagina e intenta otra vez.']);
        exit;
    }

    try {
        $uploaded = uploaded_file_url('editor_image_file', 'editor');
        if ($uploaded === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Selecciona una imagen valida para subir.']);
            exit;
        }
        echo json_encode([
            'ok' => true,
            'url' => $uploaded,
            'message' => 'Imagen subida correctamente.',
        ]);
        exit;
    } catch (RuntimeException $error) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => $error->getMessage()]);
        exit;
    }
}

function admin_lab_resources(): void
{
    $data = admin_list_data('lab-resources');
    view('admin/lab_resources', $data + ['metaTitle'=>'Lab CMS', 'resources'=>$data['rows']]);
}

function admin_lab_form(?int $id = null): void
{
    $resource = $id ? db_one("SELECT * FROM lab_resources WHERE id = ?", [$id]) : null;
    if ($id && !$resource) { not_found_page(); return; }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!verify_csrf()) {
            redirect_to(url_path('/admin/lab-resources'));
        }
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = $resource['slug'] ?? slugify($title);
        $baseSlug = $slug; $suffix = 2;
        while (db_one('SELECT id FROM lab_resources WHERE slug = ? AND id != ?', [$slug, $id ?? 0])) $slug = $baseSlug . '-' . $suffix++;
        $uploadError = ''; $uploadedCover = ''; $uploadedDownload = '';
        try {
            $uploadedCover = uploaded_file_url('cover_image_file', 'lab');
            $uploadedDownload = uploaded_file_url('download_file', 'downloads', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip'], 15728640);
        } catch (RuntimeException $error) {
            $uploadError = $error->getMessage();
        }
        $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
        $download = $uploadedDownload ?: safe_content_url(trim((string) ($_POST['download_url'] ?? '')));
        if ($uploadError !== '' || $title === '' || mb_strlen($title) > 255 || strlen($slug) > 191 || ($status === 'published' && $download === '')) {
            http_response_code(422);
            flash($uploadError ?: 'Agrega un título válido y un archivo o URL segura antes de publicar el recurso.', 'danger');
            $resource = array_merge(['title'=>'','slug'=>'','description'=>'','resource_type'=>'Template','download_url'=>'','cover_image'=>'','author_name'=>'','source_name'=>'','source_url'=>'','status'=>'draft','is_featured'=>0], $resource ?? [], array_filter($_POST, 'is_string'), ['slug'=>$slug, 'status'=>$status, 'download_url'=>$download, 'cover_image'=>$uploadedCover ?: admin_filter_value('cover_image', $_POST), 'is_featured'=>isset($_POST['is_featured']) ? 1 : 0]);
            view('admin/lab_resource_form', ['metaTitle'=>$id ? 'Editar material' : 'Nuevo material', 'resource'=>$resource, 'resourceId'=>$id]);
            return;
        }
        $payload = [$title, $slug, trim((string) ($_POST['description'] ?? '')), mb_substr(trim((string) ($_POST['resource_type'] ?? 'Template')), 0, 80), $download, $uploadedCover ?: safe_content_url(trim((string) ($_POST['cover_image'] ?? ''))), mb_substr(trim((string) ($_POST['author_name'] ?? '')) ?: ($GLOBALS['site']['author_name'] ?? 'Jovani'), 0, 191), mb_substr(trim((string) ($_POST['source_name'] ?? '')), 0, 191), safe_content_url(trim((string) ($_POST['source_url'] ?? ''))), $status, isset($_POST['is_featured']) ? 1 : 0, date('c'), $status === 'published' ? ($resource['published_at'] ?? date('c')) : null];
        if ($id) {
            db_run("UPDATE lab_resources SET title=?, slug=?, description=?, resource_type=?, download_url=?, cover_image=?, author_name=?, source_name=?, source_url=?, status=?, is_featured=?, updated_at=?, published_at=? WHERE id=?", array_merge($payload, [$id]));
        } else {
            db_run(
                "INSERT INTO lab_resources (title, slug, description, resource_type, download_url, cover_image, author_name, source_name, source_url, status, is_featured, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $payload[0], $payload[1], $payload[2], $payload[3], $payload[4], $payload[5],
                    $payload[6], $payload[7], $payload[8], $payload[9], $payload[10],
                    date('c'), $payload[11], $payload[12],
                ]
            );
        }
        flash('Material guardado.', 'success');
        redirect_to(url_path('/admin/lab-resources'));
    }
    $resource = $resource ?: ['title' => '', 'slug' => '', 'description' => '', 'resource_type' => 'Template', 'download_url' => '', 'cover_image' => '', 'author_name' => $GLOBALS['site']['author_name'] ?? 'Jovani', 'source_name' => '', 'source_url' => '', 'status' => 'draft', 'is_featured' => 0];
    view('admin/lab_resource_form', ['metaTitle' => $id ? 'Editar material' : 'Nuevo material', 'resource' => $resource, 'resourceId' => $id]);
}

function admin_delete_lab(int $id): void
{
    if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
    if (!db_one('SELECT id FROM lab_resources WHERE id = ?', [$id])) { not_found_page(); return; }
    db_run('DELETE FROM lab_resources WHERE id = ?', [$id]);
    flash('Material eliminado.', 'success');
    redirect_to(admin_list_return('/admin/lab-resources'), 303);
}

function admin_settings(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!verify_csrf()) {
            flash('Formulario expirado.', 'danger');
            redirect_to(url_path('/admin/settings'));
        }
        if (($_POST['form_name'] ?? '') === 'password') {
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $newUsername = trim((string) ($_POST['cms_username'] ?? admin_username()));
            $account = admin_account(admin_username());
            if (!admin_password_ok((string) ($_POST['current_password'] ?? ''))) {
                flash('La contraseña actual no es correcta.', 'danger');
            } elseif ($newPassword !== (string) ($_POST['confirm_password'] ?? '')) {
                flash('Las passwords no coinciden.', 'danger');
            } elseif (strlen($newPassword) < 12 || strlen($newPassword) > 72 || $newUsername === '' || mb_strlen($newUsername) > 191) {
                flash('Utiliza un usuario válido y una contraseña de 12 a 72 caracteres.', 'danger');
            } elseif (db_one('SELECT id FROM users WHERE username = ? AND id != ?', [$newUsername, $account['id'] ?? 0])) {
                flash('Ese nombre de usuario ya pertenece a otra cuenta.', 'danger');
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($account['source'] === 'users') db_run('UPDATE users SET username = ?, password_hash = ? WHERE id = ?', [$newUsername, $hash, $account['id']]);
                else { save_setting('cms_username', $newUsername); save_setting('cms_password_hash', $hash); }
                session_regenerate_id(true);
                $_SESSION['admin_username'] = $newUsername;
                $_SESSION['admin_auth_fingerprint'] = admin_auth_fingerprint(admin_account($newUsername));
                $_SESSION['admin_started_at'] = time();
                unset($_SESSION['csrf_token']); csrf_token();
                flash('Acceso del CMS actualizado.', 'success');
            }
            redirect_to(url_path('/admin/settings'));
        }
        try {
            $uploadMap = [
                'logo_file' => ['key' => 'logo_url', 'dir' => 'brand'],
                'hero_background_file' => ['key' => 'hero_background_image', 'dir' => 'brand'],
                'site_share_file' => ['key' => 'site_share_image', 'dir' => 'brand'],
            ];
            foreach ($uploadMap as $field => $upload) {
                $uploaded = uploaded_file_url($field, $upload['dir']);
                if ($uploaded !== '') {
                    $_POST[$upload['key']] = $uploaded;
                }
            }
        } catch (RuntimeException $error) {
            flash($error->getMessage(), 'danger');
            redirect_to(url_path('/admin/settings'));
        }
        $keys = ['site_title', 'site_tagline', 'logo_url', 'hero_title', 'hero_description', 'hero_background_image', 'about_blurb', 'author_name', 'site_share_image', 'comments_intro', 'newsletter_from_name', 'newsletter_from_email', 'newsletter_subject_prefix', 'newsletter_smtp_host', 'newsletter_smtp_port', 'newsletter_smtp_encryption', 'newsletter_smtp_username', 'newsletter_smtp_password', 'recaptcha_site_key', 'recaptcha_secret_key', 'adsense_script', 'ad_home_top', 'ad_sidebar', 'ad_article_inline', 'ad_footer', 'social_linkedin_url', 'social_x_url', 'social_instagram_url', 'social_youtube_url', 'social_tiktok_url', 'contact_email'];
        foreach ($keys as $key) {
            $value = (string) ($_POST[$key] ?? '');
            if ($key === 'newsletter_smtp_password' && trim($value) === '') {
                $value = (string) (settings()['newsletter_smtp_password'] ?? '');
            }
            save_setting($key, $value);
        }
        foreach (['cookie_consent_enabled', 'comments_moderation_enabled', 'recaptcha_enabled', 'newsletter_enabled'] as $key) {
            save_setting($key, isset($_POST[$key]) ? '1' : '0');
        }
        flash('Configuracion guardada.', 'success');
        redirect_to(url_path('/admin/settings'));
    }
    view('admin/settings', ['metaTitle' => 'Configuracion CMS', 'settings' => settings()]);
}
