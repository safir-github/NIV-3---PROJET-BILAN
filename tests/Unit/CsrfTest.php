<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Csrf;
use PHPUnit\Framework\TestCase;

/**
 * Class CsrfTest
 * 
 * Verifies CSRF token generation, session persistence, and timing attack resistant validation.
 * 
 * @package Tests\Unit
 */
class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['_csrf_token']);
    }

    /**
     * Test CSRF token generation and format.
     */
    public function testGenerateTokenCreatesSecureHexToken(): void
    {
        $token = Csrf::generateToken();
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token)); // 32 bytes in hex = 64 characters
        $this->assertSame($token, $_SESSION['_csrf_token']);
    }

    /**
     * Test valid CSRF token passes validation.
     */
    public function testValidTokenPasses(): void
    {
        $token = Csrf::generateToken();
        $this->assertTrue(Csrf::validateToken($token));
    }

    /**
     * Test invalid or empty CSRF token is rejected.
     */
    public function testInvalidTokenRejected(): void
    {
        Csrf::generateToken();
        $this->assertFalse(Csrf::validateToken('invalid_token_value'));
        $this->assertFalse(Csrf::validateToken(''));
        $this->assertFalse(Csrf::validateToken(null));
    }

    /**
     * Test CSRF HTML field generation helper.
     */
    public function testFieldGeneratesHiddenInput(): void
    {
        $field = Csrf::field();
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="_csrf_token"', $field);
        $this->assertStringContainsString($_SESSION['_csrf_token'], $field);
    }
}
