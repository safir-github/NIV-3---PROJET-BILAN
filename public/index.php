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
