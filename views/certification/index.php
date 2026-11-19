<div class="certifications-page">
    <div class="page-header">
        <h1>Mes Certifications</h1>
        <p class="subtitle">Vos attestations officielles de réussite obtenues sur la plateforme Knowledge Learning.</p>
    </div>

    <?php if (empty($certifications)): ?>
        <div class="empty-state-card">
            <div class="empty-state-icon">🎓</div>
            <h2>Aucune certification pour l'instant</h2>
            <p>Validez toutes les leçons d'un thème pour débloquer automatiquement votre attestation de réussite officielle.</p>
            <a href="/courses" class="btn btn-primary">Explorer les cours</a>
        </div>
    <?php else: ?>
        <div class="certifications-grid">
            <?php foreach ($certifications as $cert): ?>
                <div class="certificate-badge-card">
                    <div class="certificate-seal">🏅</div>
                    <span class="cert-theme-tag"><?= htmlspecialchars($cert['theme_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <h3 class="cert-course-title">Certification « <?= htmlspecialchars($cert['theme_name'], ENT_QUOTES, 'UTF-8') ?> »</h3>
                    <p class="cert-issuer">Délivré par <strong>Knowledge Learning</strong></p>

                    <div class="cert-code-box">
                        <span class="code-label">Code d'authenticité :</span>
                        <code class="cert-code"><?= htmlspecialchars($cert['certificate_code'], ENT_QUOTES, 'UTF-8') ?></code>
                    </div>

                    <div class="cert-date">
                        Date d'obtention : <?= date('d/m/Y', strtotime($cert['issued_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
