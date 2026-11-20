<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Slugger;
use App\Repositories\CatalogRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

/**
 * Class AdminController
 *
 * Backoffice administration controller.
 * Enforces strict ROLE_ADMIN authorization on all administrative actions.
 * Every write action checks the CSRF token and validates its input before touching the database.
 *
 * @package App\Controllers
 */
class AdminController extends BaseController
{
    private UserRepository $userRepository;
    private CatalogRepository $catalogRepository;
    private OrderRepository $orderRepository;

    /**
     * Create a new object.
     */
    public function __construct(
        ?UserRepository $userRepository = null,
        ?CatalogRepository $catalogRepository = null,
        ?OrderRepository $orderRepository = null
    ) {
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->catalogRepository = $catalogRepository ?? new CatalogRepository();
        $this->orderRepository = $orderRepository ?? new OrderRepository();
    }

    /**
     * Enforce administrator access control.
     *
     * @return void
     */
    private function requireAdmin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        $user = Auth::user();
        if ($user === null || !$user->isAdmin()) {
            http_response_code(403);
            $this->render('errors/error', [
                'errorCode' => 403,
                'pageTitle' => 'Accès Interdit (403)',
                'message' => 'Cette section est strictement réservée aux administrateurs de la plateforme.'
            ]);
            exit;
        }
    }

    /**
     * Check the CSRF token of a write action. Redirects back to $back on failure.
     *
     * @param string $back Where to go if the token is missing or invalid
     * @return bool
     */
    private function csrfOk(string $back): bool
    {
        if (!Csrf::validateToken($_POST['_csrf_token'] ?? null)) {
            $this->flash('error', 'Formulaire expiré. Veuillez réessayer.');
            $this->redirect($back);
            return false;
        }

        return true;
    }

    /**
     * Store a one-time message shown on the next backoffice page.
     *
     * @param string $type    'success' or 'error'
     * @param string $message
     * @return void
     */
    private function flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Build a unique slug for a table from a title.
     *
     * @param string   $table     'themes', 'courses' or 'lessons'
     * @param string   $title
     * @param int|null $exceptId  Row being updated, so it can keep its own slug
     * @return string|null Null if the title has no usable character
     */
    private function uniqueSlug(string $table, string $title, ?int $exceptId = null): ?string
    {
        $base = Slugger::make($title);
        if ($base === '') {
            return null;
        }

        $slug = $base;
        if ($this->catalogRepository->slugExists($table, $slug, $exceptId)) {
            // Append a short random suffix until the slug is free
            $slug = $base . '-' . bin2hex(random_bytes(3));
        }

        return $slug;
    }

    /**
     * Admin dashboard summary.
     *
     * @return void
     */
    public function index(): void
    {
        $this->requireAdmin();

        $users = $this->userRepository->findAll();
        $courses = $this->catalogRepository->findCourses();
        $orders = $this->orderRepository->findAllWithUsers();

        $this->render('admin/index', [
            'pageTitle' => 'Backoffice Administrateur - Knowledge Learning',
            'users' => $users,
            'courses' => $courses,
            'orders' => $orders
        ]);
    }

    /**
     * Catalogue management page: themes, courses and lessons.
     *
     * @return void
     */
    public function catalog(): void
    {
        $this->requireAdmin();

        $themes = $this->catalogRepository->findAllThemes();
        $courses = $this->catalogRepository->findCourses();

        // Lessons are grouped by course ID for display
        $lessonsByCourse = [];
        foreach ($courses as $course) {
            $lessonsByCourse[$course->getId()] = $this->catalogRepository->findLessonsByCourseId($course->getId());
        }

        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);

        $this->render('admin/catalog', [
            'pageTitle' => 'Gestion du catalogue - Knowledge Learning',
            'themes' => $themes,
            'courses' => $courses,
            'lessonsByCourse' => $lessonsByCourse,
            'flash' => $flash
        ]);
    }

    /**
     * Update a user's role.
     *
     * @param string $id
     * @return void
     */
    public function updateUserRole(string $id): void
    {
        $this->requireAdmin();

        $token = $_POST['_csrf_token'] ?? null;
        if (Csrf::validateToken($token)) {
            $role = $_POST['role'] ?? 'ROLE_CLIENT';
            if (in_array($role, \App\Models\User::AVAILABLE_ROLES, true)) {
                $this->userRepository->updateRole((int) $id, $role);
            }
        }

        $this->redirect('/admin');
    }

    /**
     * Create a new course via admin form.
     *
     * @return void
     */
    public function createCourse(): void
    {
        $this->requireAdmin();

        $token = $_POST['_csrf_token'] ?? null;
        if (Csrf::validateToken($token)) {
            $title = trim($_POST['title'] ?? '');
            $themeId = (int) ($_POST['theme_id'] ?? 1);
            $price = (float) ($_POST['price'] ?? 0.0);
            $description = trim($_POST['description'] ?? '');

            $slug = $this->uniqueSlug('courses', $title);

            if (!empty($title) && $price > 0 && $slug !== null) {
                $this->catalogRepository->createCourse([
                    'theme_id' => $themeId,
                    'title' => $title,
                    'slug' => $slug,
                    'description' => $description,
                    'price' => $price
                ]);
            }
        }

        $this->redirect('/admin');
    }

    /**
     * Delete a course.
     *
     * @param string $id
     * @return void
     */
    public function deleteCourse(string $id): void
    {
        $this->requireAdmin();

        $token = $_POST['_csrf_token'] ?? null;
        if (Csrf::validateToken($token)) {
            $this->catalogRepository->deleteCourse((int) $id);
        }

        $this->redirect('/admin');
    }

    /**
     * Create a theme.
     *
     * @return void
     */
    public function createTheme(): void
    {
        $this->requireAdmin();
        if (!$this->csrfOk('/admin/catalog')) {
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = $name === '' ? null : $this->uniqueSlug('themes', $name);

        if ($name === '' || mb_strlen($name) > 100 || $slug === null) {
            $this->flash('error', 'Le nom du thème est obligatoire (100 caractères maximum).');
        } else {
            $this->catalogRepository->createTheme(['name' => $name, 'slug' => $slug, 'description' => $description]);
            $this->flash('success', 'Thème créé.');
        }

        $this->redirect('/admin/catalog');
    }

    /**
     * Update a theme.
     *
     * @param string $id
     * @return void
     */
    public function updateTheme(string $id): void
    {
        $this->requireAdmin();
        if (!$this->csrfOk('/admin/catalog')) {
            return;
        }

        $themeId = (int) $id;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = $name === '' ? null : $this->uniqueSlug('themes', $name, $themeId);

        if ($this->catalogRepository->findThemeById($themeId) === null) {
            $this->flash('error', 'Thème introuvable.');
        } elseif ($name === '' || mb_strlen($name) > 100 || $slug === null) {
            $this->flash('error', 'Le nom du thème est obligatoire (100 caractères maximum).');
        } else {
            $this->catalogRepository->updateTheme($themeId, ['name' => $name, 'slug' => $slug, 'description' => $description]);
            $this->flash('success', 'Thème mis à jour.');
        }

        $this->redirect('/admin/catalog');
    }

    /**
     * Delete a theme (and, by cascade, its courses, lessons and certifications).
     *
     * @param string $id
     * @return void
     */
    public function deleteTheme(string $id): void
    {
        $this->requireAdmin();
        if (!$this->csrfOk('/admin/catalog')) {
            return;
        }

        $this->catalogRepository->deleteTheme((int) $id);
        $this->flash('success', 'Thème supprimé, ainsi que ses cursus et leçons.');
        $this->redirect('/admin/catalog');
    }

    /**
     * Update a course (title, theme, price, description).
     *
     * @param string $id
     * @return void
     */
    public function updateCourse(string $id): void
    {
        $this->requireAdmin();
        if (!$this->csrfOk('/admin/catalog')) {
            return;
        }

        $courseId = (int) $id;
        $course = $this->catalogRepository->findCourseById($courseId);
        $title = trim($_POST['title'] ?? '');
        $themeId = (int) ($_POST['theme_id'] ?? 0);
        $price = (float) ($_POST['price'] ?? 0.0);
        $description = trim($_POST['description'] ?? '');
        $slug = $title === '' ? null : $this->uniqueSlug('courses', $title, $courseId);

        if ($course === null) {
            $this->flash('error', 'Cursus introuvable.');
        } elseif ($title === '' || mb_strlen($title) > 150 || $slug === null || $price <= 0) {
            $this->flash('error', 'Titre obligatoire (150 caractères maximum) et prix supérieur à 0.');
        } elseif ($this->catalogRepository->findThemeById($themeId) === null) {
            $this->flash('error', 'Thème invalide.');
        } else {
            $this->catalogRepository->updateCourse($courseId, [
                'theme_id' => $themeId,
                'title' => $title,
                'slug' => $slug,
                'description' => $description,
                'price' => $price
            ]);
            $this->flash('success', 'Cursus mis à jour.');
        }

        $this->redirect('/admin/catalog');
    }

    /**
     * Create a lesson in a course.
     *
     * @return void
     */
    public function createLesson(): void
    {
        $this->requireAdmin();
        if (!$this->csrfOk('/admin/catalog')) {
            return;
        }

        $data = $this->lessonInputFromPost();
        if ($data === null) {
            $this->flash('error', 'Leçon invalide : titre, cursus et prix (supérieur à 0) sont obligatoires.');
        } else {
            $data['slug'] = $this->uniqueSlug('lessons', $data['title']);
            $this->catalogRepository->createLesson($data);
            $this->flash('success', 'Leçon créée.');
        }

        $this->redirect('/admin/catalog');
    }

    /**
     * Update a lesson.
     *
     * @param string $id
     * @return void
     */
    public function updateLesson(string $id): void
    {
        $this->requireAdmin();
        if (!$this->csrfOk('/admin/catalog')) {
            return;
        }

        $lessonId = (int) $id;
        $data = $this->lessonInputFromPost();

        if ($this->catalogRepository->findLessonById($lessonId) === null) {
            $this->flash('error', 'Leçon introuvable.');
        } elseif ($data === null) {
            $this->flash('error', 'Leçon invalide : titre, cursus et prix (supérieur à 0) sont obligatoires.');
        } else {
            $data['slug'] = $this->uniqueSlug('lessons', $data['title'], $lessonId);
            $this->catalogRepository->updateLesson($lessonId, $data);
            $this->flash('success', 'Leçon mise à jour.');
        }

        $this->redirect('/admin/catalog');
    }

    /**
     * Delete a lesson.
     *
     * @param string $id
     * @return void
     */
    public function deleteLesson(string $id): void
    {
        $this->requireAdmin();
        if (!$this->csrfOk('/admin/catalog')) {
            return;
        }

        $this->catalogRepository->deleteLesson((int) $id);
        $this->flash('success', 'Leçon supprimée.');
        $this->redirect('/admin/catalog');
    }

    /**
     * Read and validate the lesson form fields.
     *
     * @return array<string, mixed>|null Null if the input is invalid
     */
    private function lessonInputFromPost(): ?array
    {
        $title = trim($_POST['title'] ?? '');
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $price = (float) ($_POST['price'] ?? 0.0);
        $position = (int) ($_POST['position'] ?? 1);

        if ($title === '' || mb_strlen($title) > 150 || $price <= 0 || $position < 1) {
            return null;
        }

        if ($this->catalogRepository->findCourseById($courseId) === null) {
            return null;
        }

        return [
            'course_id' => $courseId,
            'title' => $title,
            'description' => trim($_POST['description'] ?? ''),
            'content_text' => trim($_POST['content_text'] ?? ''),
            'video_url' => trim($_POST['video_url'] ?? ''),
            'price' => $price,
            'position' => $position
        ];
    }
}
