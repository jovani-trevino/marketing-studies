<section class="section-space">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="admin-card">
                    <span class="eyebrow">Acceso al CMS</span>
                    <h1 class="mb-3">Entra para escribir y publicar</h1>
                    <p class="text-body-secondary">Usuario inicial <strong>admin</strong>, password inicial <strong>marketing123</strong>. Cambialo al entrar.</p>
                    <form method="post" class="mt-4">
                        <?= csrf_input() ?>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="username" class="form-control" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-dark w-100 rounded-pill" type="submit">Entrar al panel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
