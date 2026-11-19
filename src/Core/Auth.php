<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Class Auth
 * 
 * Manages authenticated user state in session.
 * 
 * @package App\Core
 */
class Auth
{
    private const SESSION_USER_ID = 'auth_user_id';

    /**
     * Check if a user is currently logged in.
     *
     * @return bool
     */
    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return !empty($_SESSION[self::SESSION_USER_ID]);
    }

    /**
     * Get the currently authenticated User instance or null.
     *
     * @return User|null
     */
    public static function user(): ?User
    {
        if (!self::check()) {
            return null;
        }

        $userId = (int) $_SESSION[self::SESSION_USER_ID];
        $repository = new UserRepository();

        return $repository->findById($userId);
    }

    /**
     * Log in a user and regenerate session ID to prevent session fixation.
     *
     * @param User $user
     * @return void
     */
    public static function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_USER_ID] = $user->getId();
    }

    /**
     * Log out current user and destroy active session.
     *
     * @return void
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION[self::SESSION_USER_ID]);
        session_destroy();
    }
}
