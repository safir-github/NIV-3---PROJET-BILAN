<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\PasswordValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class PasswordValidatorTest
 * 
 * Verifies strong password rules according to security guidelines.
 * 
 * @package Tests\Unit
 */
class PasswordValidatorTest extends TestCase
{
    private PasswordValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new PasswordValidator();
    }

    /**
     * Test that a valid strong password passes validation with zero errors.
     */
    public function testValidPasswordPasses(): void
    {
        $this->assertTrue($this->validator->isValid('Password123!'));
        $this->assertEmpty($this->validator->validate('Password123!'));
    }

    /**
     * Test that passwords shorter than minimum length fail validation.
     */
    public function testTooShortPasswordFails(): void
    {
        $errors = $this->validator->validate('Pass1!');
        $this->assertFalse($this->validator->isValid('Pass1!'));
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('at least 8 characters', $errors[0]);
    }

    /**
     * Test missing uppercase requirement.
     */
    public function testMissingUppercaseFails(): void
    {
        $errors = $this->validator->validate('password123!');
        $this->assertFalse($this->validator->isValid('password123!'));
        $this->assertContains('Password must contain at least one uppercase letter.', $errors);
    }

    /**
     * Test missing lowercase requirement.
     */
    public function testMissingLowercaseFails(): void
    {
        $errors = $this->validator->validate('PASSWORD123!');
        $this->assertFalse($this->validator->isValid('PASSWORD123!'));
        $this->assertContains('Password must contain at least one lowercase letter.', $errors);
    }

    /**
     * Test missing number requirement.
     */
    public function testMissingNumberFails(): void
    {
        $errors = $this->validator->validate('PasswordDef!');
        $this->assertFalse($this->validator->isValid('PasswordDef!'));
        $this->assertContains('Password must contain at least one number.', $errors);
    }

    /**
     * Test missing special character requirement.
     */
    public function testMissingSpecialCharFails(): void
    {
        $errors = $this->validator->validate('Password1234');
        $this->assertFalse($this->validator->isValid('Password1234'));
        $this->assertContains('Password must contain at least one special character (@$!%*?&#^_-).', $errors);
    }
}
