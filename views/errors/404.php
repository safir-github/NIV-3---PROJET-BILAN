<div class="error-container">
    <div class="error-code">404</div>
    <h2 class="error-title"><?= htmlspecialchars($pageTitle ?? 'Page non trouvée', ENT_QUOTES, 'UTF-8') ?></h2>
    <p class="error-text"><?= htmlspecialchars($message ?? 'La ressource que vous recherchez est introuvable.', ENT_QUOTES, 'UTF-8') ?></p>
    <a href="/courses" class="btn btn-primary">Retour au catalogue</a>
</div>
