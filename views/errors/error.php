<div class="error-container">
    <div class="error-code"><?= (int) ($errorCode ?? 404) ?></div>
    <h2 class="error-title"><?= htmlspecialchars($pageTitle ?? 'Erreur', ENT_QUOTES, 'UTF-8') ?></h2>
    <p class="error-text"><?= htmlspecialchars($message ?? 'Une anomalie est survenue.', ENT_QUOTES, 'UTF-8') ?></p>
    <a href="/courses" class="btn btn-primary">Retour au catalogue</a>
</div>
