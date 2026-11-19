<div class="auth-card">
    <h2 class="auth-title">Connexion</h2>
    <p class="auth-subtitle">Accédez à votre espace d'apprentissage Knowledge Learning.</p>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/login" method="POST" class="auth-form">
        <?= \App\Core\Csrf::field() ?>

        <div class="form-group">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
    </form>

    <div class="auth-footer">
        <p>Pas encore inscrit ? <a href="/register">Créer un compte</a>.</p>
    </div>
</div>
