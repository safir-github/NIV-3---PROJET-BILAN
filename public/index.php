<?php

declare(strict_types=1);

// Start PHP session for authentication and CSRF security
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require Composer Autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\CatalogController;
use App\Controllers\CheckoutController;
use App\Controllers\StripeWebhookController;
use App\Controllers\LessonController;
use App\Controllers\CertificationController;
use App\Controllers\AdminController;

// Load environment variables (.env)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Initialize Router and define core routes
$router = new Router();

// Public & Home routes
$router->get('/', HomeController::class, 'index');
$router->get('/courses', CatalogController::class, 'index');
$router->get('/themes/{slug}', CatalogController::class, 'theme');
$router->get('/courses/{slug}', CatalogController::class, 'course');
$router->get('/lessons/{slug}', CatalogController::class, 'lesson');

// Lesson validation & Certifications
$router->post('/lessons/{id}/validate', LessonController::class, 'validate');
$router->get('/certifications', CertificationController::class, 'index');

// Backoffice routes (Admin only)
$router->get('/admin', AdminController::class, 'index');
$router->post('/admin/users/{id}/role', AdminController::class, 'updateUserRole');
$router->post('/admin/courses/create', AdminController::class, 'createCourse');
$router->post('/admin/courses/{id}/delete', AdminController::class, 'deleteCourse');
$router->get('/admin/catalog', AdminController::class, 'catalog');
$router->post('/admin/themes/create', AdminController::class, 'createTheme');
$router->post('/admin/themes/{id}/update', AdminController::class, 'updateTheme');
$router->post('/admin/themes/{id}/delete', AdminController::class, 'deleteTheme');
$router->post('/admin/courses/{id}/update', AdminController::class, 'updateCourse');
$router->post('/admin/lessons/create', AdminController::class, 'createLesson');
$router->post('/admin/lessons/{id}/update', AdminController::class, 'updateLesson');
$router->post('/admin/lessons/{id}/delete', AdminController::class, 'deleteLesson');

// Checkout & Stripe routes (POST secured with CSRF)
$router->post('/checkout/course/{id}', CheckoutController::class, 'checkoutCourse');
$router->post('/checkout/lesson/{id}', CheckoutController::class, 'checkoutLesson');
$router->get('/checkout/success', CheckoutController::class, 'success');
$router->get('/checkout/cancel', CheckoutController::class, 'cancel');
$router->post('/stripe/webhook', StripeWebhookController::class, 'handle');

// Authentication routes
$router->get('/register', AuthController::class, 'showRegister');
$router->post('/register', AuthController::class, 'register');
$router->get('/activate', AuthController::class, 'activate');
$router->get('/login', AuthController::class, 'showLogin');
$router->post('/login', AuthController::class, 'login');
$router->post('/logout', AuthController::class, 'logout');

// Dispatch incoming request
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($requestUri, $requestMethod);
