<div class="checkout-card">
    <div class="card-icon-warning">⚙️</div>
    <h1 class="checkout-title">Mode Sandbox (Simulation Locale)</h1>
    <p class="checkout-subtitle">La clé d'API Stripe n'est pas encore configurée dans le fichier <code>.env</code>.</p>

    <div class="order-summary-box">
        <p><strong>Commande créée :</strong> <?= htmlspecialchars($order['reference'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Article :</strong> <?= htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Montant :</strong> <?= number_format((float)($price ?? 0), 2, ',', ' ') ?> €</p>
    </div>

    <div class="alert alert-info">
        <p>Pour brancher Stripe en réel lors de la soutenance, renseignez vos clés de test Stripe (<code>STRIPE_SECRET_KEY</code>) dans le fichier <code>.env</code>.</p>
    </div>

    <div class="auth-footer">
        <a href="/courses" class="btn btn-outline">Retour au catalogue</a>
    </div>
</div>
