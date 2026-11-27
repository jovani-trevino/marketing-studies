<section class="section-space">
    <div class="container">
        <?php require __DIR__ . '/nav.php'; ?>
        <div class="admin-header"><div><span class="eyebrow">Marca, SEO y comunidad</span><h1>Configuración</h1><p>Identidad, redes, AdSense, reCAPTCHA y seguridad.</p></div></div>
        <div class="row g-4">
            <div class="col-lg-8">
                <form data-admin-unsaved method="post" class="admin-card" enctype="multipart/form-data">
                    <?= csrf_input() ?><input type="hidden" name="form_name" value="site">
                    <div class="section-head mb-3"><div><span class="eyebrow">Branding</span><h2 class="h4 mb-0">Identidad principal</h2></div></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label for="field-settings-site_title" class="form-label">Nombre del sitio</label><input id="field-settings-site_title" type="text" name="site_title" class="form-control" value="<?= e($settings['site_title'] ?? '') ?>"></div>
                        <div class="col-md-6"><label for="field-settings-author_name" class="form-label">Autor principal</label><input id="field-settings-author_name" type="text" name="author_name" class="form-control" value="<?= e($settings['author_name'] ?? '') ?>"></div>
                        <div class="col-md-6"><label for="field-settings-site_tagline" class="form-label">Tagline</label><input id="field-settings-site_tagline" type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline'] ?? '') ?>"></div>
                        <div class="col-md-6"><label for="field-settings-hero_title" class="form-label">Hero title</label><input id="field-settings-hero_title" type="text" name="hero_title" class="form-control" value="<?= e($settings['hero_title'] ?? '') ?>"></div>
                        <div class="col-lg-4">
                            <label class="form-label">Logotipo</label>
                            <?php admin_image_field('logo_url', 'logo_file', 'Logotipo del sitio', $settings['logo_url'] ?? '', [
                                'id' => 'site-logo',
                                'button_label' => 'Subir logotipo',
                                'manual_label' => 'Ruta del logotipo',
                                'placeholder' => '/uploads/brand/logo.png',
                                'hint' => 'Si eliges un archivo desde tu PC, ese sera el que se guarde.',
                                'empty_title' => 'Aún no hay logotipo',
                                'empty_copy' => 'Sube el logotipo oficial desde tu PC.',
                                'drop_hint' => 'Tambien puedes arrastrar aqui el archivo del logo.',
                            ]); ?>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Imagen de portada</label>
                            <?php admin_image_field('hero_background_image', 'hero_background_file', 'Imagen principal del hero', $settings['hero_background_image'] ?? '', [
                                'id' => 'site-hero-image',
                                'button_label' => 'Subir portada',
                                'manual_label' => 'Ruta de la portada',
                                'placeholder' => '/uploads/brand/hero.jpg',
                                'hint' => 'Ideal para la cabecera principal del home.',
                                'empty_title' => 'Portada del hero',
                                'empty_copy' => 'Elige una imagen grande para la portada principal.',
                                'drop_hint' => 'Acepta JPG, PNG, WEBP o GIF.',
                            ]); ?>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Imagen para compartir</label>
                            <?php admin_image_field('site_share_image', 'site_share_file', 'Imagen social del sitio', $settings['site_share_image'] ?? '', [
                                'id' => 'site-share-image',
                                'button_label' => 'Subir imagen social',
                                'manual_label' => 'Ruta de imagen social',
                                'placeholder' => '/uploads/brand/share-image.jpg',
                                'hint' => 'Se usa al compartir el sitio en WhatsApp, Facebook, LinkedIn y X.',
                                'empty_title' => 'Imagen social',
                                'empty_copy' => 'Sube la imagen que quieres mostrar al compartir el sitio.',
                                'drop_hint' => 'Puedes cambiarla cuando quieras desde el CMS.',
                            ]); ?>
                        </div>
                        <div class="col-12"><label for="field-settings-hero_description" class="form-label">Hero description</label><textarea id="field-settings-hero_description" name="hero_description" rows="3" class="form-control"><?= e($settings['hero_description'] ?? '') ?></textarea></div>
                        <div class="col-12"><label for="field-settings-about_blurb" class="form-label">About blurb</label><textarea id="field-settings-about_blurb" name="about_blurb" rows="3" class="form-control"><?= e($settings['about_blurb'] ?? '') ?></textarea></div>
                    </div>
                    <hr class="my-4">
                    <div class="section-head mb-3"><div><span class="eyebrow">Redes</span><h2 class="h4 mb-0">Social links</h2></div></div>
                    <div class="row g-3">
                        <?php foreach (['social_linkedin_url' => 'LinkedIn', 'social_x_url' => 'X', 'social_instagram_url' => 'Instagram', 'social_youtube_url' => 'YouTube', 'social_tiktok_url' => 'TikTok', 'contact_email' => 'Email contacto'] as $key => $label): ?>
                            <div class="col-md-6"><label class="form-label"><?= e($label) ?></label><input type="text" name="<?= e($key) ?>" class="form-control" value="<?= e($settings[$key] ?? '') ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <hr class="my-4">
                    <div class="section-head mb-3"><div><span class="eyebrow">Newsletter</span><h2 class="h4 mb-0">Correo a suscriptores</h2></div></div>
                    <div class="row g-3">
                        <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="newsletter_enabled" <?= setting_is_enabled($settings['newsletter_enabled'] ?? '1') ? 'checked' : '' ?>><label class="form-check-label">Enviar email automaticamente al publicar un post nuevo</label></div></div>
                        <div class="col-md-6"><label for="field-settings-newsletter_from_name" class="form-label">Nombre del remitente</label><input id="field-settings-newsletter_from_name" type="text" name="newsletter_from_name" class="form-control" value="<?= e($settings['newsletter_from_name'] ?? '') ?>" placeholder="Marketing Studies"></div>
                        <div class="col-md-6"><label for="field-settings-newsletter_from_email" class="form-label">Email remitente</label><input id="field-settings-newsletter_from_email" type="email" name="newsletter_from_email" class="form-control" value="<?= e($settings['newsletter_from_email'] ?? '') ?>" placeholder="newsletter@tudominio.com"></div>
                        <div class="col-12"><label for="field-settings-newsletter_subject_prefix" class="form-label">Prefijo del asunto</label><input id="field-settings-newsletter_subject_prefix" type="text" name="newsletter_subject_prefix" class="form-control" value="<?= e($settings['newsletter_subject_prefix'] ?? '') ?>" placeholder="Nuevo en Marketing Studies"></div>
                        <div class="col-12">
                            <div class="alert alert-light border rounded-4 mb-0">
                                <strong>Entrega a Gmail y Outlook:</strong> usa el buzón real <code>newsletter@marketing-studies.com</code>, configura SMTP autenticado y activa SPF, DKIM y DMARC en tu hosting.
                            </div>
                        </div>
                        <div class="col-md-6"><label for="field-settings-newsletter_smtp_host" class="form-label">SMTP host</label><input id="field-settings-newsletter_smtp_host" type="text" name="newsletter_smtp_host" class="form-control" value="<?= e($settings['newsletter_smtp_host'] ?? '') ?>" placeholder="mail.marketing-studies.com"></div>
                        <div class="col-md-3"><label for="field-settings-newsletter_smtp_port" class="form-label">Puerto</label><input id="field-settings-newsletter_smtp_port" type="number" min="1" name="newsletter_smtp_port" class="form-control" value="<?= e($settings['newsletter_smtp_port'] ?? '465') ?>" placeholder="465"></div>
                        <div class="col-md-3">
                            <label for="field-settings-newsletter_smtp_encryption" class="form-label">Seguridad</label>
                            <select id="field-settings-newsletter_smtp_encryption" name="newsletter_smtp_encryption" class="form-select">
                                <?php $smtpEncryption = strtolower((string) ($settings['newsletter_smtp_encryption'] ?? 'ssl')); ?>
                                <option value="ssl" <?= $smtpEncryption === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="tls" <?= $smtpEncryption === 'tls' ? 'selected' : '' ?>>TLS / STARTTLS</option>
                                <option value="none" <?= $smtpEncryption === 'none' ? 'selected' : '' ?>>Sin cifrado</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label for="field-settings-newsletter_smtp_username" class="form-label">Usuario SMTP</label><input id="field-settings-newsletter_smtp_username" type="text" name="newsletter_smtp_username" class="form-control" value="<?= e($settings['newsletter_smtp_username'] ?? '') ?>" placeholder="newsletter@marketing-studies.com"></div>
                        <div class="col-md-6"><label for="field-settings-newsletter_smtp_password" class="form-label">Password SMTP</label><input id="field-settings-newsletter_smtp_password" type="password" name="newsletter_smtp_password" class="form-control" value="" placeholder="Deja en blanco para conservar la actual"><small class="text-body-secondary">Si no cambias la password, se mantiene la guardada.</small></div>
                    </div>
                    <hr class="my-4">
                    <div class="section-head mb-3"><div><span class="eyebrow">Comentarios</span><h2 class="h4 mb-0">Privacidad y proteccion</h2></div></div>
                    <div class="row g-3">
                        <div class="col-12"><label for="field-settings-comments_intro" class="form-label">Intro comentarios</label><textarea id="field-settings-comments_intro" name="comments_intro" rows="3" class="form-control"><?= e($settings['comments_intro'] ?? '') ?></textarea></div>
                        <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="comments_moderation_enabled" <?= setting_is_enabled($settings['comments_moderation_enabled'] ?? '1') ? 'checked' : '' ?>><label class="form-check-label">Moderacion previa</label></div></div>
                        <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="recaptcha_enabled" <?= setting_is_enabled($settings['recaptcha_enabled'] ?? '0') ? 'checked' : '' ?>><label class="form-check-label">Activar reCAPTCHA</label></div></div>
                        <div class="col-md-6"><label for="field-settings-recaptcha_site_key" class="form-label">reCAPTCHA Site Key</label><input id="field-settings-recaptcha_site_key" name="recaptcha_site_key" class="form-control" value="<?= e($settings['recaptcha_site_key'] ?? '') ?>"></div>
                        <div class="col-md-6"><label for="field-settings-recaptcha_secret_key" class="form-label">reCAPTCHA Secret Key</label><input id="field-settings-recaptcha_secret_key" name="recaptcha_secret_key" class="form-control" value="<?= e($settings['recaptcha_secret_key'] ?? '') ?>"></div>
                        <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="cookie_consent_enabled" <?= setting_is_enabled($settings['cookie_consent_enabled'] ?? '1') ? 'checked' : '' ?>><label class="form-check-label">Mostrar modal de cookies</label></div></div>
                    </div>
                    <hr class="my-4">
                    <div class="section-head mb-3"><div><span class="eyebrow">Ads</span><h2 class="h4 mb-0">Google AdSense</h2></div></div>
                    <?php foreach (['adsense_script' => 'Script global', 'ad_home_top' => 'Slot Home', 'ad_sidebar' => 'Slot Sidebar', 'ad_article_inline' => 'Slot Articulo', 'ad_footer' => 'Slot Footer'] as $key => $label): ?>
                        <div class="mb-3"><label class="form-label"><?= e($label) ?></label><textarea name="<?= e($key) ?>" class="form-control code-area" rows="4"><?= e($settings[$key] ?? '') ?></textarea></div>
                    <?php endforeach; ?>
                    <button class="btn btn-dark rounded-pill px-4">Guardar configuración</button>
                </form>
            </div>
            <div class="col-lg-4">
                <form data-admin-unsaved method="post" class="admin-card">
                    <?= csrf_input() ?><input type="hidden" name="form_name" value="password">
                    <span class="eyebrow">Seguridad</span>
                    <h2 class="h4 mb-3">Cambiar acceso CMS</h2>
                    <div class="mb-3"><label for="field-settings-cms_username" class="form-label">Usuario CMS</label><input id="field-settings-cms_username" name="cms_username" class="form-control" value="<?= e(admin_username()) ?>"></div>
                    <div class="mb-3"><label class="form-label" for="cms-current-password">Contraseña actual</label><input id="cms-current-password" type="password" name="current_password" class="form-control" autocomplete="current-password" required></div><div class="mb-3"><label class="form-label" for="cms-new-password">Nueva contraseña</label><input id="cms-new-password" type="password" name="new_password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required><small class="text-body-secondary">Usa de 12 a 72 caracteres; los caracteres especiales pueden ocupar más de un byte.</small></div>
                    <div class="mb-4"><label class="form-label" for="cms-confirm-password">Confirmar nueva contraseña</label><input id="cms-confirm-password" type="password" name="confirm_password" class="form-control" minlength="12" maxlength="72" autocomplete="new-password" required></div>
                    <button class="btn btn-outline-dark rounded-pill w-100">Actualizar acceso</button>
                </form>
            </div>
        </div>
    </div>
</section>
