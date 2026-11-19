<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Router;

/**
 * Class RouterTest
 * 
 * Validates the core HTTP router mechanism with static and dynamic parametrized routes.
 * 
 * @package App\Tests
 */
class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    /**
     * Test that Router class can be instantiated properly.
     */
    public function testRouterCanBeInstantiated(): void
    {
        $this->assertInstanceOf(Router::class, $this->router);
    }

    /**
     * Test route dispatching with dynamic regex parameters.
     */
    public function testDynamicParamRouteDispatch(): void
    {
        $testController = new class {
            public static ?string $capturedSlug = null;
            public function showCourse(string $slug): void
            {
                self::$capturedSlug = $slug;
            }
        };

        // Register dynamic parameter route
        $this->router->get('/courses/{slug}', get_class($testController), 'showCourse');

        // Dispatch matching URI
        $this->router->dispatch('/courses/guitare-debutant', 'GET');

        $this->assertSame('guitare-debutant', $testController::$capturedSlug);
    }

    /**
     * Test fallback 404 response on unknown routes.
     */
    public function testUnknownRouteReturns404(): void
    {
        ob_start();
        $this->router->dispatch('/unknown-path-xyz', 'GET');
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertStringContainsString('404 - Page non trouvée', (string) $output);
    }
}
