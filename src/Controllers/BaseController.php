<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Class BaseController
 * 
 * Abstract base controller providing standard view rendering and CSRF helpers.
 * 
 * @package App\Controllers
 */
abstract class BaseController
{
    /**
     * Render a view template wrapped in main layout.
     *
     * @param string $view Relative view path without .php (e.g. 'home/index')
     * @param array<string, mixed> $data Variables passed to the view template
     * @return void
     */
    protected function render(string $view, array $data = []): void
    {
        // Extract array keys as local variables for view template
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = dirname(__DIR__, 2) . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<p>View not found: {$view}</p>";
        }
        $content = ob_get_clean();

        // Render main layout with injected content
        $layoutFile = dirname(__DIR__, 2) . '/views/layouts/main.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Redirect to a specific URL and terminate script.
     *
     * @param string $url Target URL
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
