<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\CatalogRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

/**
 * Class AdminController
 * 
 * Backoffice administration controller.
 * Enforces strict ROLE_ADMIN authorization on all administrative actions.
 * 
 * @package App\Controllers
 */
class AdminController extends BaseController
{
    private UserRepository $userRepository;
    private CatalogRepository $catalogRepository;
    private OrderRepository $orderRepository;

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
            $this->render('errors/404', [
                'pageTitle' => 'Accès Interdit',
                'message' => 'Cette section est strictement réservée aux administrateurs de la plateforme.'
            ]);
            exit;
        }
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
            if (in_array($role, ['ROLE_CLIENT', 'ROLE_ADMIN'], true)) {
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

            // Basic slug generator
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

            if (!empty($title) && $price > 0) {
                $this->catalogRepository->createCourse([
                    'theme_id' => $themeId,
                    'title' => $title,
                    'slug' => $slug . '-' . uniqid(),
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
}
