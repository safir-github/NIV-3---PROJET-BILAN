<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class Router
 * 
 * Simple, robust HTTP router mapping URLs to Controller actions.
 * Supports static routes and dynamic parameter placeholders (e.g. /courses/{slug}).
 * 
 * @package App\Core
 */
class Router
{
    /**
     * @var array<string, array<string, array{controller: string, action: string, regex: string, paramNames: array<string>}>>
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
        $this->addRoute('GET', $path, $controller, $action);
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
        $this->addRoute('POST', $path, $controller, $action);
    }

    /**
     * Compile and register route with optional parameter placeholders {param}.
     *
     * @param string $method
     * @param string $path
     * @param string $controller
     * @param string $action
     * @return void
     */
    private function addRoute(string $method, string $path, string $controller, string $action): void
    {
        $paramNames = [];
        // Convert route pattern: /courses/{slug} => regex ^/courses/([a-zA-Z0-9_\-]+)$
        $regex = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function (array $matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([a-zA-Z0-9_\-]+)';
        }, $path);

        $regexPattern = '#^' . $regex . '$#';

        $this->routes[$method][$path] = [
            'controller' => $controller,
            'action' => $action,
            'regex' => $regexPattern,
            'paramNames' => $paramNames
        ];
    }

    /**
     * Dispatch incoming HTTP request to matching controller action.
     *
     * @param string $uri
     * @param string $method
     * @return void
     */
    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $route) {
                if (preg_match($route['regex'], $path, $matches)) {
                    array_shift($matches); // Remove full match

                    $controllerClass = $route['controller'];
                    $action = $route['action'];

                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $action)) {
                            // Call action with extracted URL parameters
                            $controller->$action(...$matches);
                            return;
                        }
                    }
                }
            }
        }

        // 404 Not Found fallback
        http_response_code(404);
        echo '<h1>404 - Page non trouvée</h1><p>La page demandée n\'existe pas.</p>';
    }
}
