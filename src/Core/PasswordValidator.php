<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Class PasswordValidator
 * 
 * Validates password strength according to project security specifications.
 * Rules:
 * - Minimum 8 characters
 * - At least 1 uppercase letter
 * - At least 1 lowercase letter
 * - At least 1 number
 * - At least 1 special character (@$!%*?&#^_-)
 * 
 * @package App\Core
 */
class PasswordValidator
{
    public const MIN_LENGTH = 8;

    /**
     * Validate a plain text password and return any error messages.
     *
     * @param string $password
     * @return array<string> Array of error messages (empty if valid)
     */
    public function validate(string $password): array
    {
        $errors = [];

        if (mb_strlen($password) < self::MIN_LENGTH) {
            $errors[] = sprintf('Password must be at least %d characters long.', self::MIN_LENGTH);
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if (!preg_match('/[@$!%*?&#^_\-]/', $password)) {
            $errors[] = 'Password must contain at least one special character (@$!%*?&#^_-).';
        }

        return $errors;
    }

    /**
     * Check if a password satisfies all security criteria.
     *
     * @param string $password
     * @return bool
     */
    public function isValid(string $password): bool
    {
        return empty($this->validate($password));
    }
}
