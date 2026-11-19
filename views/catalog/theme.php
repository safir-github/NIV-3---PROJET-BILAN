<div class="catalog-page">
    <div class="breadcrumb">
        <a href="/courses">Catalogue</a> &rsaquo; <span><?= htmlspecialchars($theme->getName(), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="page-header">
        <h1>Thème : <?= htmlspecialchars($theme->getName(), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="subtitle"><?= htmlspecialchars($theme->getDescription() ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <section class="section-courses">
        <h2 class="section-title">Cursus dans ce thème (<?= count($courses) ?>)</h2>
        <?php if (empty($courses)): ?>
            <p>Aucun cursus disponible pour ce thème pour le moment.</p>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <h3 class="course-title"><?= htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="course-desc"><?= htmlspecialchars($course->getDescription() ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="course-footer">
                            <span class="course-price"><?= number_format($course->getPrice(), 2, ',', ' ') ?> €</span>
                            <a href="/courses/<?= htmlspecialchars($course->getSlug(), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm">Accéder au cursus</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
