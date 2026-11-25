<?php
$isEs = $locale === 'es';
$profile = is_array($authorRecord ?? null) ? $authorRecord : [];
$authorName = trim((string) ($authorName ?? '')) ?: 'Jovani Treviño';
$authorSlug = trim((string) ($profile['slug'] ?? '')) ?: slugify($authorName);
$authorRole = trim((string) ($profile['role'] ?? '')) ?: 'Marketing Intelligence & Performance';
$authorHeadline = trim((string) ($profile['headline'] ?? ''));
if ($authorHeadline === '' || mb_strtolower($authorHeadline) === mb_strtolower($authorRole)) {
    $authorHeadline = $isEs
    ? 'Conecto estrategia, datos y creatividad para entender mejor el marketing y llevar las ideas a la práctica.'
    : 'Connecting strategy, data and creativity to understand marketing and put ideas into practice.';
}
$authorBio = trim((string) ($profile['bio'] ?? '')) ?: ($isEs
    ? "Mi trabajo conecta investigación, contenido, analítica y experiencia digital. Me interesa entender qué necesita una marca, encontrar las preguntas correctas y convertir ese contexto en decisiones.\n\nMarketing Studies es mi espacio para documentar ese proceso: compartir ideas, explorar herramientas y pensar con más profundidad sobre el marketing."
    : "My work connects research, content, analytics and digital experience. I focus on understanding what a brand needs, asking useful questions and turning that context into decisions.\n\nMarketing Studies is where I document that process: sharing ideas, exploring tools and thinking more deeply about marketing.");
$bioParagraphs = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', $authorBio) ?: [])));
$authorAreas = parse_tag_list((string) ($profile['expertise_areas'] ?? ''));
if (!$authorAreas) {
    $authorAreas = $isEs
        ? ['Estrategia de marketing', 'SEO y contenido', 'Performance', 'Analítica', 'Experiencia digital']
        : ['Marketing strategy', 'SEO & content', 'Performance', 'Analytics', 'Digital experience'];
}
$authorLabels = parse_tag_list((string) ($profile['profile_labels'] ?? ''));
$authorHighlights = parse_line_list((string) ($profile['highlights'] ?? ''));
$professionalLinks = author_visible_professional_links($profile);
$orcidId = orcid_normalize_id((string) ($profile['orcid_id'] ?? ''));
$contactEmail = trim((string) ($site['contact_email'] ?? '')) ?: 'jovani@marketing-studies.com';
$blogUrl = url_path('/blog', $locale);
$authorArchiveUrl = url_path('/author/' . $authorSlug, $locale);
$portfolioUrl = portfolio_url();
$cvUrl = portfolio_url('cv');
$extraHead = '<link rel="stylesheet" href="' . e(versioned_asset_url('static/author-profile.css')) . '">';
?>

<div class="ms-profile">
    <section class="ms-profile-hero ms-profile-wrap" aria-labelledby="profile-name">
        <div class="ms-profile-intro">
            <p class="ms-profile-kicker"><?= $isEs ? 'El autor de Marketing Studies' : 'The author of Marketing Studies' ?></p>
            <h1 id="profile-name"><?= e($authorName) ?><span class="ms-profile-period" aria-hidden="true">.</span></h1>
            <p class="ms-profile-role"><?= e($authorRole) ?></p>
            <p class="ms-profile-lead"><?= e($authorHeadline) ?></p>
            <div class="ms-profile-actions">
                <a class="ms-profile-button" href="#publications"><i class="bi bi-journal-text" aria-hidden="true"></i><?= $isEs ? 'Mis publicaciones' : 'My writing' ?></a>
                <a class="ms-profile-button ms-profile-button-outline" href="<?= e($cvUrl) ?>"><i class="bi bi-file-earmark-person" aria-hidden="true"></i><?= $isEs ? 'Ver mi CV' : 'View my CV' ?></a>
            </div>
            <div class="ms-profile-connections" aria-label="<?= $isEs ? 'Enlaces profesionales' : 'Professional links' ?>">
                <a href="<?= e($portfolioUrl) ?>"><?= $isEs ? 'Portafolio' : 'Portfolio' ?></a>
                <?php foreach ($professionalLinks as $link): ?>
                    <a href="<?= e($link['url']) ?>" target="_blank" rel="me noopener noreferrer"><i class="<?= e($link['icon']) ?>" aria-hidden="true"></i><?= e($link['label']) ?></a>
                <?php endforeach; ?>
                <?php if ($orcidId !== ''): ?>
                    <a href="<?= e(orcid_profile_url($orcidId)) ?>" target="_blank" rel="me noopener noreferrer" aria-label="ORCID <?= e($orcidId) ?>">ORCID</a>
                <?php endif; ?>
                <a href="#contact"><?= $isEs ? 'Contacto' : 'Contact' ?></a>
            </div>
        </div>
        <figure class="ms-profile-portrait">
            <div class="ms-profile-photo">
                <img src="<?= e($profilePortrait) ?>" alt="<?= e($authorName) ?>" width="720" height="900" fetchpriority="high" loading="eager" decoding="async">
            </div>
            <figcaption><span><?= $isEs ? 'Estrategia, datos y una mirada curiosa.' : 'Strategy, data and a curious perspective.' ?></span><span class="ms-profile-monogram" aria-hidden="true">MS.</span></figcaption>
        </figure>
    </section>

    <nav class="ms-profile-jump-nav" aria-label="<?= $isEs ? 'En esta página' : 'On this page' ?>">
        <div class="ms-profile-wrap">
            <span><?= $isEs ? 'Conóceme' : 'Get to know me' ?></span>
            <a href="#about"><?= $isEs ? 'Sobre mí' : 'About' ?></a>
            <a href="#expertise"><?= $isEs ? 'Mi enfoque' : 'My focus' ?></a>
            <a href="#publications"><?= $isEs ? 'Publicaciones' : 'Writing' ?></a>
            <a href="#contact"><?= $isEs ? 'Hablemos' : 'Let’s talk' ?></a>
        </div>
    </nav>

    <section class="ms-profile-about ms-profile-wrap" id="about" aria-labelledby="profile-about-title">
        <div class="ms-profile-section-heading">
            <p class="ms-profile-kicker"><?= $isEs ? 'Detrás del blog' : 'Behind the journal' ?></p>
            <h2 id="profile-about-title"><?= $isEs ? 'Criterio antes<br> que ruido.' : 'Clarity before<br> the noise.' ?></h2>
            <a class="ms-profile-text-link" href="<?= e($portfolioUrl) ?>"><?= $isEs ? 'Conoce mi trabajo' : 'Explore my work' ?></a>
        </div>
        <div class="ms-profile-bio">
            <?php foreach ($bioParagraphs as $paragraph): ?>
                <p><?= nl2br(e($paragraph)) ?></p>
            <?php endforeach; ?>
            <?php if ($authorLabels): ?>
                <ul class="ms-profile-labels" aria-label="<?= $isEs ? 'Perfil profesional' : 'Professional profile' ?>">
                    <?php foreach ($authorLabels as $label): ?><li><?= e($label) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div class="ms-profile-expertise" id="expertise">
                <h3><?= $isEs ? 'Las disciplinas que conecto' : 'The disciplines I connect' ?></h3>
                <ul class="ms-profile-topics">
                    <?php foreach ($authorAreas as $area): ?><li><?= e($area) ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php if ($authorHighlights): ?>
                <div class="ms-profile-highlights">
                    <h3><?= $isEs ? 'En la práctica' : 'In practice' ?></h3>
                    <ul><?php foreach ($authorHighlights as $highlight): ?><li><?= e($highlight) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="ms-profile-writing" id="publications" aria-labelledby="profile-writing-title">
        <div class="ms-profile-wrap">
            <div class="ms-profile-section-top">
                <div>
                    <p class="ms-profile-kicker">Marketing Studies<?php if ($totalPosts > 0): ?> <span class="ms-profile-publication-count"><?= (int) $totalPosts ?> <?= $isEs ? ($totalPosts === 1 ? 'publicación' : 'publicaciones') : ($totalPosts === 1 ? 'published article' : 'published articles') ?></span><?php endif; ?></p>
                    <h2 id="profile-writing-title"><?= $isEs ? 'Ideas que comparto.' : 'Ideas worth sharing.' ?></h2>
                </div>
                <a class="ms-profile-text-link" href="<?= e($posts ? $authorArchiveUrl : $blogUrl) ?>"><?= $isEs ? 'Explorar las publicaciones' : 'Explore the writing' ?></a>
            </div>
            <?php if ($posts): ?>
                <div class="ms-profile-posts">
                    <?php foreach ($posts as $post): ?>
                        <?php $cover = media_url($post['cover_image'] ?? ''); $postHref = post_url($post, $locale); ?>
                        <article class="ms-profile-post">
                            <a class="ms-profile-post-cover" href="<?= e($postHref) ?>" tabindex="-1" aria-hidden="true">
                                <?php if ($cover !== ''): ?>
                                    <img src="<?= e($cover) ?>" alt="" width="720" height="450" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span class="ms-profile-cover-placeholder"><i class="bi bi-book" aria-hidden="true"></i><span>Marketing Studies.</span></span>
                                <?php endif; ?>
                            </a>
                            <div class="ms-profile-post-meta"><span><?= e($post['category_name'] ?? ($isEs ? 'Artículo' : 'Article')) ?></span><span><?= max(1, (int) ($post['read_time'] ?? 1)) ?> min</span></div>
                            <h3><a href="<?= e($postHref) ?>"><?= e($post['title']) ?></a></h3>
                            <p><?= e(post_display_excerpt($post, 145)) ?></p>
                            <?php if (!empty($post['published_at'])): ?><time datetime="<?= e(date('Y-m-d', strtotime($post['published_at']))) ?>"><?= e(date_label($post['published_at'])) ?></time><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="ms-profile-empty">
                    <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
                    <div><h3><?= $isEs ? 'La conversación sigue en el blog.' : 'The conversation continues in the journal.' ?></h3><p><?= $isEs ? 'Explora las lecturas, las preguntas y los recursos de Marketing Studies.' : 'Explore the reading, questions and resources at Marketing Studies.' ?></p></div>
                    <a class="ms-profile-button ms-profile-button-outline" href="<?= e($blogUrl) ?>"><?= $isEs ? 'Ir al blog' : 'Visit the journal' ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="ms-profile-contact ms-profile-wrap" id="contact" aria-labelledby="profile-contact-title">
        <div class="ms-profile-contact-copy">
            <p class="ms-profile-kicker"><?= $isEs ? 'Sigamos la conversación' : 'Keep the conversation going' ?></p>
            <h2 id="profile-contact-title"><?= $isEs ? '¿Lo conversamos?' : 'Shall we talk?' ?></h2>
            <p><?= $isEs ? 'Una idea, un proyecto o una pregunta que valga la pena explorar. Cuéntame qué tienes en mente.' : 'An idea, a project or a question worth exploring. Tell me what you have in mind.' ?></p>
            <a class="ms-profile-email" href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>
            <div class="ms-profile-newsletter" id="newsletter">
                <h3><?= $isEs ? 'Las próximas ideas, en tu correo.' : 'The next ideas, in your inbox.' ?></h3>
                <form method="post" action="<?= e(url_path('/newsletter', $locale)) ?>">
                    <?= csrf_input() ?>
                    <input type="hidden" name="source" value="author-profile">
                    <label for="profile-newsletter-email"><?= $isEs ? 'Correo electrónico' : 'Email address' ?></label>
                    <div class="ms-profile-subscribe-row"><input id="profile-newsletter-email" type="email" name="email" placeholder="<?= $isEs ? 'tu@correo.com' : 'you@example.com' ?>" required maxlength="191" autocomplete="email"><button class="ms-profile-button" type="submit"><?= $isEs ? 'Suscribirme' : 'Subscribe' ?></button></div>
                    <p class="ms-profile-form-note"><?= $isEs ? 'Novedades cuando hay algo útil que compartir. Puedes darte de baja cuando quieras.' : 'Updates when there is something useful to share. Unsubscribe whenever you wish.' ?></p>
                </form>
            </div>
        </div>
        <form class="ms-profile-contact-form" method="post" action="<?= e(url_path('/contact', $locale)) ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="source" value="author-profile">
            <h3><?= $isEs ? 'Escríbeme un mensaje' : 'Send me a message' ?></h3>
            <div class="ms-profile-form-row">
                <div><label for="profile-contact-name"><?= $isEs ? 'Nombre' : 'Name' ?></label><input id="profile-contact-name" name="name" type="text" required maxlength="150" autocomplete="name"></div>
                <div><label for="profile-contact-email"><?= $isEs ? 'Correo electrónico' : 'Email address' ?></label><input id="profile-contact-email" name="email" type="email" required maxlength="191" autocomplete="email"></div>
            </div>
            <div><label for="profile-contact-topic"><?= $isEs ? 'Tema' : 'Subject' ?> <span><?= $isEs ? '(opcional)' : '(optional)' ?></span></label><input id="profile-contact-topic" name="topic" type="text" maxlength="200"></div>
            <div><label for="profile-contact-message"><?= $isEs ? '¿Qué tienes en mente?' : 'What do you have in mind?' ?></label><textarea id="profile-contact-message" name="message" rows="4" required maxlength="10000"></textarea></div>
            <div class="ms-profile-form-bottom"><p class="ms-profile-form-note"><a href="<?= e(url_path('/privacy', $locale)) ?>"><?= $isEs ? 'Cómo se tratan tus datos' : 'How your data is handled' ?></a></p><button class="ms-profile-button" type="submit"><?= $isEs ? 'Enviar mensaje' : 'Send message' ?></button></div>
        </form>
    </section>
</div>
