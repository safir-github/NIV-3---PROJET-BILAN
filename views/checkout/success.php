<div class="checkout-card">
    <div class="card-icon-success">🎉</div>
    <h1 class="checkout-title">Merci pour votre commande !</h1>
    <p class="checkout-subtitle">Votre paiement a été validé avec succès par notre passerelle de test.</p>

    <?php if ($order): ?>
        <div class="order-summary-box">
            <p><strong>Référence de commande :</strong> <?= htmlspecialchars($order['order_reference'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Montant réglé :</strong> <?= number_format((float)$order['total_amount'], 2, ',', ' ') ?> €</p>
            <p><strong>Statut :</strong> <span class="badge-success">Payée</span></p>
        </div>
    <?php endif; ?>

    <p>Vos cours et leçons sont dès à présent débloqués dans votre espace d'apprentissage.</p>
    <div class="auth-footer">
        <a href="/courses" class="btn btn-primary">Accéder à mes formations</a>
    </div>
</div>
