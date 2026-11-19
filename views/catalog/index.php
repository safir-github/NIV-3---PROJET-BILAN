<div class="catalog-page">
    <div class="page-header">
        <h1>Catalogue des Formations</h1>
        <p class="subtitle">Découvrez l'ensemble de nos thèmes d'apprentissage et cursus spécialisés.</p>
    </div>

    <!-- Filtre par Thèmes -->
    <section class="section-themes">
        <h2 class="section-title">Explorez par Thème</h2>
        <div class="themes-grid">
            <?php foreach ($themes as $theme): ?>
                <a href="/themes/<?= htmlspecialchars($theme->getSlug(), ENT_QUOTES, 'UTF-8') ?>" class="theme-card">
                    <div class="theme-image-wrapper">
                        <img src="/assets/images/course-banner.jpg" alt="<?= htmlspecialchars($theme->getName(), ENT_QUOTES, 'UTF-8') ?>" class="theme-banner-img">
                    </div>
                    <div class="theme-card-body">
                        <h3 class="theme-name"><?= htmlspecialchars($theme->getName(), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="theme-desc"><?= htmlspecialchars($theme->getDescription() ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <span class="theme-link">Voir les cursus &rarr;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Liste de tous les Cursus -->
    <section class="section-courses">
        <h2 class="section-title">Tous les Cursus Disponibles</h2>
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <div class="course-image-wrapper">
                        <img src="/assets/images/course-banner.jpg" alt="<?= htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') ?>" class="course-banner-img">
                    </div>
                    <div class="course-card-content">
                        <div class="course-badge"><?= htmlspecialchars($course->getThemeName() ?? 'Formation', ENT_QUOTES, 'UTF-8') ?></div>
                        <h3 class="course-title"><?= htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="course-desc"><?= htmlspecialchars($course->getDescription() ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="course-footer">
                            <span class="course-price"><?= number_format($course->getPrice(), 2, ',', ' ') ?> €</span>
                            <a href="/courses/<?= htmlspecialchars($course->getSlug(), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline btn-sm">Découvrir le cursus</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
