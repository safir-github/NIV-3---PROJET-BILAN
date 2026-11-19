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

    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return trim($this->firstName . ' ' . $this->lastName); }
    public function getRole(): string { return $this->role; }
    public function isAdmin(): bool { return $this->role === 'ROLE_ADMIN'; }
    public function isActive(): bool { return $this->isActive; }
    public function getActivationToken(): ?string { return $this->activationToken; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }
    public function setActivationToken(?string $token): self { $this->activationToken = $token; return $this; }
}
