<div class="auth-card">
    <h2 class="auth-title">Créer un compte</h2>
    <p class="auth-subtitle">Rejoignez Knowledge Learning pour accéder à nos cours et certifications.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/register" method="POST" class="auth-form">
        <?= \App\Core\Csrf::field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">Prénom *</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nom *</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Adresse email *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe *</label>
            <input type="password" id="password" name="password" required>
            <small class="form-help">Minimum 8 caractères, avec majuscule, minuscule, chiffre et caractère spécial (@$!%*?&#^_-).</small>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmer le mot de passe *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
    </form>

    <div class="auth-footer">
        <p>Vous avez déjà un compte ? <a href="/login">Connectez-vous ici</a>.</p>
    </div>
</div>
