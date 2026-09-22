<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Router;

/**
 * Class RouterTest
 * 
 * Validates the core HTTP router mechanism.
 * 
 * @package App\Tests
 */
class RouterTest extends TestCase
{
    /**
     * Test that Router class can be instantiated properly.
     */
    public function testRouterCanBeInstantiated(): void
    {
        $router = new Router();
        $this->assertInstanceOf(Router::class, $router);
    }
}
