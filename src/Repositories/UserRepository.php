<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;

/**
 * Class UserRepository
 * 
 * Data Access Object (DAO) for User entity.
 * Handles database operations with secure prepared statements.
 * 
 * @package App\Repositories
 */
class UserRepository extends BaseRepository
{
    /**
     * Find a user by their unique email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        return $data ? new User($data) : null;
    }

    /**
     * Find a user by their primary key ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data ? new User($data) : null;
    }

    /**
     * Find a user by their email activation token.
     *
     * @param string $token
     * @return User|null
     */
    public function findByActivationToken(string $token): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE activation_token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        $data = $stmt->fetch();

        return $data ? new User($data) : null;
    }

    /**
     * Create a new user with hashed password and default ROLE_CLIENT.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        $sql = 'INSERT INTO users (email, password, first_name, last_name, role, is_active, activation_token, created_by, updated_by)
                VALUES (:email, :password, :first_name, :last_name, :role, :is_active, :activation_token, :created_by, :updated_by)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email'            => $data['email'],
            'password'         => $data['password'],
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'role'             => $data['role'] ?? 'ROLE_CLIENT',
            'is_active'        => !empty($data['is_active']) ? 1 : 0,
            'activation_token' => $data['activation_token'] ?? null,
            'created_by'       => $data['created_by'] ?? 'user_registration',
            'updated_by'       => $data['updated_by'] ?? 'user_registration',
        ]);

        $id = (int) $this->db->lastInsertId();
        return $this->findById($id);
    }

    /**
     * Activate a user account and clear the activation token.
     *
     * @param int $id
     * @return bool
     */
    public function activateAccount(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = 1, activation_token = NULL, updated_by = :updated_by WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'updated_by' => 'email_activation'
        ]);
    }
}
