<section class="section-space admin-login-section">
    <div class="container">
        <div class="admin-login-grid">
            <div class="admin-login-story">
                <span class="eyebrow">Marketing Studies · Estudio editorial</span>
                <h1>Investigar.<br>Escribir.<br><em>Compartir.</em></h1>
                <p>Un espacio para cuidar tus ideas, darles forma y convertirlas en conocimiento que otros puedan usar.</p>
                <a href="<?= e(url_path('/')) ?>">Volver a la publicación <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </div>
            <div class="admin-login-form-wrap">
                <div class="admin-login-form">
                    <span class="admin-login-icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
                    <span class="eyebrow">Acceso al CMS</span>
                    <h2>Tu próximo artículo<br>comienza aquí.</h2>
                    <p class="text-body-secondary">Entra para administrar tu publicación, tus recursos y tu comunidad.</p>
                    <?php if (isset($setupReady) && !$setupReady): ?>
                        <div class="alert alert-info" role="status">El acceso editorial todavía no está configurado. El administrador del servidor debe completar la configuración inicial de la cuenta.</div>
                    <?php endif; ?>
                    <form method="post" class="mt-4">
                        <?= csrf_input() ?>
                        <div class="mb-3">
                            <label class="form-label" for="cms-username">Usuario</label>
                            <input type="text" id="cms-username" name="username" class="form-control" required autocomplete="username" autocapitalize="none" spellcheck="false" autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="cms-password">Contraseña</label>
                            <input type="password" id="cms-password" name="password" class="form-control" required autocomplete="current-password">
                        </div>
                        <button class="btn btn-dark w-100" type="submit"<?= isset($setupReady) && !$setupReady ? ' disabled' : '' ?>>Entrar al estudio <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
