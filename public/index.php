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

// Load environment variables (.env)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Initialize Router and define core routes
$router = new Router();

// Public routes
$router->get('/', HomeController::class, 'index');

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
