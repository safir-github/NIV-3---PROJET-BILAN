<div class="lesson-page">
    <div class="breadcrumb">
        <a href="/courses">Catalogue</a> &rsaquo;
        <?php if ($course): ?>
            <a href="/courses/<?= htmlspecialchars($course->getSlug(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') ?></a> &rsaquo;
        <?php endif; ?>
        <span><?= htmlspecialchars($lesson->getTitle(), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <?php if ($hasAccess): ?>
        <!-- Contenu de la leçon débloquée -->
        <article class="lesson-content-card">
            <header class="lesson-header">
                <span class="badge-tag">Leçon débloquée</span>
                <h1><?= htmlspecialchars($lesson->getTitle(), ENT_QUOTES, 'UTF-8') ?></h1>
            </header>

            <?php if (!empty($lesson->getVideoUrl())): ?>
                <div class="video-container">
                    <iframe 
                        src="<?= htmlspecialchars($lesson->getVideoUrl(), ENT_QUOTES, 'UTF-8') ?>" 
                        title="<?= htmlspecialchars($lesson->getTitle(), ENT_QUOTES, 'UTF-8') ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            <?php endif; ?>

            <div class="lesson-text-body">
                <h3>Support & Fiche de cours</h3>
                <p><?= nl2br(htmlspecialchars($lesson->getContentText() ?? 'Contenu pédagogique en cours de mise à jour.', ENT_QUOTES, 'UTF-8')) ?></p>
            </div>

            <footer class="lesson-validation-footer">
                <?php if ($isValidated): ?>
                    <div class="alert alert-success">
                        ✅ <strong>Leçon validée !</strong> Votre progression a bien été enregistrée pour ce cursus.
                    </div>
                <?php else: ?>
                    <form action="/lessons/<?= $lesson->getId() ?>/validate" method="POST">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="btn btn-primary btn-lg">Valider cette leçon</button>
                    </form>
                <?php endif; ?>
            </footer>
        </article>

    <?php else: ?>
        <!-- Paywall / Accès restreint -->
        <div class="paywall-card">
            <div class="paywall-icon">🔒</div>
            <h2>Contenu réservé aux inscrits</h2>
            <p>Pour visionner la vidéo et consulter les fiches de cours de <strong>« <?= htmlspecialchars($lesson->getTitle(), ENT_QUOTES, 'UTF-8') ?> »</strong>, vous devez acquérir cette leçon ou le cursus complet.</p>

            <div class="purchase-options">
                <div class="option-card">
                    <h3>Cette leçon seule</h3>
                    <p class="option-price"><?= number_format($lesson->getPrice(), 2, ',', ' ') ?> €</p>
                    <a href="/checkout/lesson/<?= $lesson->getId() ?>" class="btn btn-outline btn-block">Acheter la leçon</a>
                </div>

                <?php if ($course): ?>
                    <div class="option-card option-featured">
                        <span class="popular-tag">Le plus avantageux</span>
                        <h3>Cursus complet</h3>
                        <p class="option-price"><?= number_format($course->getPrice(), 2, ',', ' ') ?> €</p>
                        <a href="/checkout/course/<?= $course->getId() ?>" class="btn btn-primary btn-block">Acheter le cursus (<?= count($course->getLessons() ?? []) ?> leçons)</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
