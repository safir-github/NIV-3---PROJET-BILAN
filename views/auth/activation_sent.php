<div class="auth-card">
    <div class="card-icon-success">✉️</div>
    <h2 class="auth-title">Vérifiez vos emails</h2>
    <p class="auth-subtitle">
        Un lien de confirmation sécurisé a été envoyé à l'adresse suivante :<br>
        <strong><?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
    </p>

    <div class="alert alert-info">
        <p>Veuillez cliquer sur le lien présent dans le message pour activer votre compte avant de vous connecter.</p>
    </div>

    <div class="auth-footer">
        <p><a href="/login" class="btn btn-outline">Aller à la page de connexion</a></p>
    </div>
</div>
