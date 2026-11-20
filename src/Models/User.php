<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class User
 * 
 * Represents a registered user entity in Knowledge Learning platform.
 * 
 * @package App\Models 
 */
class User
{
    public const ROLE_CLIENT = 'ROLE_CLIENT';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_INSTRUCTOR = 'ROLE_INSTRUCTOR'; // 'autre rôle' required by CEF rubric

    public const AVAILABLE_ROLES = [
        self::ROLE_CLIENT,
        self::ROLE_ADMIN,
        self::ROLE_INSTRUCTOR,
    ];

    private ?int $id;
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private string $role;
    private bool $isActive;
    private ?string $activationToken;
    private ?string $createdAt;
    private ?string $updatedAt;
    private string $createdBy;
    private string $updatedBy;

    /**
     * Create a new object.
     */
    public function __construct(array $attributes = [])
    {
        $this->id = isset($attributes['id']) ? (int) $attributes['id'] : null;
        $this->email = (string) ($attributes['email'] ?? '');
        $this->password = (string) ($attributes['password'] ?? '');
        $this->firstName = (string) ($attributes['first_name'] ?? '');
        $this->lastName = (string) ($attributes['last_name'] ?? '');
        $this->role = (string) ($attributes['role'] ?? 'ROLE_CLIENT');
        $this->isActive = !empty($attributes['is_active']);
        $this->activationToken = isset($attributes['activation_token']) ? (string) $attributes['activation_token'] : null;
        $this->createdAt = $attributes['created_at'] ?? null;
        $this->updatedAt = $attributes['updated_at'] ?? null;
        $this->createdBy = (string) ($attributes['created_by'] ?? 'system');
        $this->updatedBy = (string) ($attributes['updated_by'] ?? 'system');
    }

    /**
     * Get the id.
     */
    public function getId(): ?int { return $this->id; }
    /**
     * Get the email.
     */
    public function getEmail(): string { return $this->email; }
    /**
     * Get the password.
     */
    public function getPassword(): string { return $this->password; }
    /**
     * Get the first name.
     */
    public function getFirstName(): string { return $this->firstName; }
    /**
     * Get the last name.
     */
    public function getLastName(): string { return $this->lastName; }
    /**
     * Get the full name.
     */
    public function getFullName(): string { return trim($this->firstName . ' ' . $this->lastName); }
    /**
     * Get the role.
     */
    public function getRole(): string { return $this->role; }
    /**
     * Check if the admin is true.
     */
    public function isAdmin(): bool { return $this->role === 'ROLE_ADMIN'; }
    /**
     * Check if the active is true.
     */
    public function isActive(): bool { return $this->isActive; }
    /**
     * Get the activation token.
     */
    public function getActivationToken(): ?string { return $this->activationToken; }
    /**
     * Get the created at.
     */
    public function getCreatedAt(): ?string { return $this->createdAt; }

    /**
     * Run the set email action.
     */
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    /**
     * Run the set password action.
     */
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    /**
     * Run the set first name action.
     */
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    /**
     * Run the set last name action.
     */
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    /**
     * Run the set role action.
     */
    public function setRole(string $role): self { $this->role = $role; return $this; }
    /**
     * Run the set is active action.
     */
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }
    /**
     * Run the set activation token action.
     */
    public function setActivationToken(?string $token): self { $this->activationToken = $token; return $this; }
}
