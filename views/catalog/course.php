<div class="catalog-page">
    <div class="breadcrumb">
        <a href="/courses">Catalogue</a> &rsaquo;
        <a href="/themes/<?= htmlspecialchars($course->getSlug(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($course->getThemeName() ?? 'Thème', ENT_QUOTES, 'UTF-8') ?></a> &rsaquo;
        <span><?= htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="course-header-card">
        <div class="course-header-info">
            <span class="badge-tag"><?= htmlspecialchars($course->getThemeName() ?? 'Formation', ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="course-full-desc"><?= htmlspecialchars($course->getDescription() ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="course-header-action">
            <?php if ($hasCourseAccess): ?>
                <div class="status-box status-owned">
                    <span class="icon">✅</span>
                    <div>
                        <strong>Cursus débloqué</strong>
                        <p>Vous avez un accès complet à toutes les leçons.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="price-box">
                    <span class="price-label">Prix complet du cursus :</span>
                    <span class="price-amount"><?= number_format($course->getPrice(), 2, ',', ' ') ?> €</span>
                </div>
                <form action="/checkout/course/<?= $course->getId() ?>" method="POST">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="btn btn-primary btn-block">Acheter le cursus complet</button>
                </form>
                <p class="secure-note">🔒 Paiement sécurisé via Stripe Sandbox</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Programme du Cursus -->
    <section class="curriculum-section">
        <h2 class="section-title">Programme du cursus (<?= count($lessons) ?> leçons)</h2>
        <div class="lessons-list">
            <?php foreach ($lessons as $index => $lesson): ?>
                <?php $isUnlocked = !empty($lessonsAccess[$lesson->getId()]); ?>
                <div class="lesson-row <?= $isUnlocked ? 'lesson-unlocked' : 'lesson-locked' ?>">
                    <div class="lesson-order"><?= $index + 1 ?></div>
                    <div class="lesson-info">
                        <h4 class="lesson-title"><?= htmlspecialchars($lesson->getTitle(), ENT_QUOTES, 'UTF-8') ?></h4>
                        <span class="lesson-pricing">Prix individuel : <?= number_format($lesson->getPrice(), 2, ',', ' ') ?> €</span>
                    </div>
                    <div class="lesson-action">
                        <?php if ($isUnlocked): ?>
                            <a href="/lessons/<?= htmlspecialchars($lesson->getSlug(), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm">Suivre la leçon</a>
                        <?php else: ?>
                            <a href="/lessons/<?= htmlspecialchars($lesson->getSlug(), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline btn-sm">Détails / Acheter (<?= number_format($lesson->getPrice(), 2, ',', ' ') ?> €)</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
