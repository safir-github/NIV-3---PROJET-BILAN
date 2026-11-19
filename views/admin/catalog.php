<?php
/**
 * Backoffice catalogue page: create, edit and delete themes, courses and lessons.
 *
 * Variables: $themes (Theme[]), $courses (Course[]), $lessonsByCourse (array<courseId, Lesson[]>), $flash (array|null)
 */
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="admin-dashboard catalog-admin">
    <div class="page-header">
        <h1>📚 Gestion du catalogue</h1>
        <p class="subtitle">Thèmes, cursus et leçons. <a href="/admin">Retour au tableau de bord</a></p>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-danger' ?>">
            <p><?= $e($flash['message']) ?></p>
        </div>
    <?php endif; ?>

    <!-- 1. Thèmes -->
    <section class="admin-section">
        <h2 class="section-title">Thèmes (<?= count($themes) ?>)</h2>

        <?php foreach ($themes as $theme): ?>
            <form action="/admin/themes/<?= $theme->getId() ?>/update" method="POST" class="auth-form inline-form">
                <input type="hidden" name="_csrf_token" value="<?= $e(\App\Core\Csrf::generateToken()) ?>">
                <label>Nom
                    <input type="text" name="name" value="<?= $e($theme->getName()) ?>" maxlength="100" required>
                </label>
                <label>Description
                    <input type="text" name="description" value="<?= $e($theme->getDescription()) ?>">
                </label>
                <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
            </form>
            <form action="/admin/themes/<?= $theme->getId() ?>/delete" method="POST" class="inline-form"
                  onsubmit="return confirm('Supprimer ce thème, ainsi que ses cursus, leçons et certifications ?');">
                <input type="hidden" name="_csrf_token" value="<?= $e(\App\Core\Csrf::generateToken()) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Supprimer le thème</button>
            </form>
        <?php endforeach; ?>

        <h3>Ajouter un thème</h3>
        <form action="/admin/themes/create" method="POST" class="auth-form inline-form">
            <input type="hidden" name="_csrf_token" value="<?= $e(\App\Core\Csrf::generateToken()) ?>">
            <label>Nom <input type="text" name="name" maxlength="100" required></label>
            <label>Description <input type="text" name="description"></label>
            <button type="submit" class="btn btn-primary btn-sm">Créer le thème</button>
        </form>
    </section>

    <!-- 2. Cursus et leçons -->
    <section class="admin-section">
        <h2 class="section-title">Cursus et leçons (<?= count($courses) ?>)</h2>

        <?php foreach ($courses as $course): ?>
            <div class="course-admin-block">
                <h3><?= $e($course->getTitle()) ?> <small>(<?= $e($course->getThemeName()) ?>)</small></h3>

                <form action="/admin/courses/<?= $course->getId() ?>/update" method="POST" class="auth-form inline-form">
                    <input type="hidden" name="_csrf_token" value="<?= $e(\App\Core\Csrf::generateToken()) ?>">
                    <label>Titre <input type="text" name="title" value="<?= $e($course->getTitle()) ?>" maxlength="150" required></label>
                    <label>Thème
                        <select name="theme_id">
                            <?php foreach ($themes as $theme): ?>
                                <option value="<?= $theme->getId() ?>" <?= $theme->getId() === $course->getThemeId() ? 'selected' : '' ?>>
                                    <?= $e($theme->getName()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Prix (€) <input type="number" name="price" step="0.01" min="0.01" value="<?= $e(number_format($course->getPrice(), 2, '.', '')) ?>" required></label>
                    <label>Description <input type="text" name="description" value="<?= $e($course->getDescription()) ?>"></label>
                    <button type="submit" class="btn btn-primary btn-sm">Enregistrer le cursus</button>
                </form>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Titre</th>
                            <th>Prix</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($lessonsByCourse[$course->getId()] ?? []) as $lesson): ?>
                            <tr>
                                <td colspan="4">
                                    <form action="/admin/lessons/<?= $lesson->getId() ?>/update" method="POST" class="auth-form inline-form">
                                        <input type="hidden" name="_csrf_token" value="<?= $e(\App\Core\Csrf::generateToken()) ?>">
                                        <input type="hidden" name="course_id" value="<?= $course->getId() ?>">
                                        <label>Pos. <input type="number" name="position" min="1" value="<?= $lesson->getPosition() ?>" required></label>
                                        <label>Titre <input type="text" name="title" value="<?= $e($lesson->getTitle()) ?>" maxlength="150" required></label>
                                        <label>Prix (€) <input type="number" name="price" step="0.01" min="0.01" value="<?= $e(number_format($lesson->getPrice(), 2, '.', '')) ?>" required></label>
                                        <label>Vidéo (URL) <input type="text" name="video_url" value="<?= $e($lesson->getVideoUrl()) ?>"></label>
                                        <label>Description <input type="text" name="description" value="<?= $e($lesson->getDescription()) ?>"></label>
                                        <label>Contenu <textarea name="content_text" rows="2"><?= $e($lesson->getContentText()) ?></textarea></label>
                                        <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                                    </form>
                                    <form action="/admin/lessons/<?= $lesson->getId() ?>/delete" method="POST" class="inline-form"
                                          onsubmit="return confirm('Supprimer cette leçon ?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $e(\App\Core\Csrf::generateToken()) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer la leçon</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h4>Ajouter une leçon à ce cursus</h4>
                <form action="/admin/lessons/create" method="POST" class="auth-form inline-form">
                    <input type="hidden" name="_csrf_token" value="<?= $e(\App\Core\Csrf::generateToken()) ?>">
                    <input type="hidden" name="course_id" value="<?= $course->getId() ?>">
                    <label>Pos. <input type="number" name="position" min="1" value="1" required></label>
                    <label>Titre <input type="text" name="title" maxlength="150" required></label>
                    <label>Prix (€) <input type="number" name="price" step="0.01" min="0.01" required></label>
                    <label>Vidéo (URL) <input type="text" name="video_url"></label>
                    <label>Description <input type="text" name="description"></label>
                    <label>Contenu <textarea name="content_text" rows="2"></textarea></label>
                    <button type="submit" class="btn btn-primary btn-sm">Créer la leçon</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>
</div>
