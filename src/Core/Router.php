<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class Router
 * 
 * Simple, robust HTTP router mapping URLs to Controller actions.
 * 
 * @package App\Core
 */
class Router
{
    /**
     * @var array<string, array<string, array{controller: string, action: string}>> Registered routes.
     */
    private array $routes = [];

    /**
     * Register a GET route.
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function get(string $path, string $controller, string $action): void
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'action' => $action];
    }

    /**
     * Register a POST route.
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function post(string $path, string $controller, string $action): void
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'action' => $action];
    }

    /**
     * Dispatch the current HTTP request to matching controller action.
     *
     * @param string $uri
     * @param string $method
     * @return void
     */
    public function dispatch(string $uri, string $method): void
    {
        // Strip query string if present
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
            $controllerClass = $route['controller'];
            $action = $route['action'];

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    return;
                }
            }
        }

        // 404 Not Found fallback
        http_response_code(404);
        echo '<h1>404 - Page non trouvée</h1><p>La page demandée n\'existe pas.</p>';
    }
}
